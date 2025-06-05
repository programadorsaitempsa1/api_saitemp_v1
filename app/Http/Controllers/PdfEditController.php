<?php

namespace App\Http\Controllers;

use setasign\Fpdi\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;


class PdfEditController extends Controller
{
    /**
     * Recibe varias imágenes y las junta en un PDF A4.
     */
    public function imagesToA4Pdf(Request $request)
    {
        // 1. Validar
        $request->validate([
            'images'   => 'required|array|min:1',
            'images.*' => 'required|image'
        ]);

        // 2. Guardar rutas temporales
        $paths = [];
        foreach ($request->file('images') as $img) {
            $paths[] = $img->getRealPath();
        }

        // 3. Generar PDF A4
        $pdfPath = $this->convertImagesToA4($paths);

        // 4. Devolver al cliente y borrar temp
        return response()
            ->download($pdfPath, 'imagenes_' . Carbon::now()->timestamp . '.pdf')
            ->deleteFileAfterSend(true);
    }

    /**
     * Convierte un array de rutas de imagen a un PDF A4.
     *
     * @param  string[]  $imagePaths
     * @return string     Ruta del PDF generado
     */
    protected function convertImagesToA4(array $imagePaths): string
    {
        // medidas A4 en mm
        $a4Width  = 210;
        $a4Height = 297;
        // márgenes internos en mm
        $margin = 10;
        $maxWidth  = $a4Width  - 2 * $margin;
        $maxHeight = $a4Height - 2 * $margin;
        // factor conversión px → mm (asumiendo 96 DPI)
        $pxToMm = 25.4 / 96;
        $type = 'JPG';
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        foreach ($imagePaths as $path) {
            // 1. Nueva página A4
            $pdf->AddPage('P', [$a4Width, $a4Height]);

            // 2. Dimensiones de la imagen en px
            list($pxW, $pxH) = getimagesize($path);

            // 3. Convertir a mm
            $imgW = $pxW * $pxToMm;
            $imgH = $pxH * $pxToMm;

            // 4. Calcular escala para que quepa dentro de los márgenes
            $scale = min($maxWidth / $imgW, $maxHeight / $imgH, 1);

            $drawW = $imgW * $scale;
            $drawH = $imgH * $scale;

            // 5. Centrar
            $x = ($a4Width  - $drawW) / 2;
            $y = ($a4Height - $drawH) / 2;

            // 6. Insertar imagen
            $pdf->Image($path, $x, $y, $drawW, $drawH, $type);
        }

        // 7. Guardar en temp
        $output = tempnam(sys_get_temp_dir(), 'multiimg_') . '.pdf';
        $pdf->Output($output, 'F');

        return $output;
    }

    /**
     * Divide un PDF según rangos o páginas específicas.
     *
     * @param  string    $sourcePath       Ruta al PDF de entrada.
     * @param  string[]  $pagesOrRanges    Array de strings, cada uno:
     *                                     - "m-n" para rango de páginas m a n
     *                                     - "k" para página suelta k
     * @return string[]                    Array de rutas a los PDFs generados,
     *                                     en el mismo orden de $pagesOrRanges.
     * @throws \Exception                 Si el rango es inválido.
     */
    protected function splitPdfByRanges(string $sourcePath, array $pagesOrRanges): array
    {

        $outputPaths = [];
        $fpdiCount = new Fpdi();
        $totalPages = $fpdiCount->setSourceFile($sourcePath);

        foreach ($pagesOrRanges as $spec) {

            $pdf = new Fpdi();
            $pdf->SetAutoPageBreak(false);
            if (strpos($spec, '-') !== false) {

                list($start, $end) = array_map('intval', explode('-', $spec, 2));
                if ($start < 1 || $end > $totalPages || $start > $end) {
                    throw new \Exception("Rango inválido: {$spec}");
                }
                $pages = range($start, $end);
            } else {
                $k = intval($spec);
                if ($k < 1 || $k > $totalPages) {
                    throw new \Exception("Página inválida: {$spec}");
                }
                $pages = [$k];
            }
            foreach ($pages as $pageNo) {
                $tplId = $pdf->setSourceFile($sourcePath);
                $tpl = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }
            $outPath = tempnam(sys_get_temp_dir(), 'split_') . '.pdf';
            $pdf->Output($outPath, 'F');
            $outputPaths[] = $outPath;
        }

        return $outputPaths;
    }

    public function splitPdf(Request $request)
    {
        $request->validate([
            'pdf'    => 'required|mimes:pdf',
            'parts'  => 'required|array|min:1',
            'parts.*' => ['required', 'regex:/^\d+(-\d+)?$/']
        ]);
        $pdfPath = $request->file('pdf')->getRealPath();
        $ranges  = $request->input('parts');

        try {
            $outputs = $this->splitPdfByRanges($pdfPath, $ranges);
            return response()->json([
                'status' => 'success',
                'files'  => $outputs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 
     *
     * @param  string[]  $pdfPaths  Array de rutas a los PDFs de entrada.
     * @return string               Ruta al PDF combinado.
     * @throws \Exception
     */
    protected function mergePdfsToA4(array $pdfPaths): string
    {
        if (empty($pdfPaths)) {
            throw new \InvalidArgumentException('Debes pasar al menos un PDF.');
        }

        $a4Width  = 210;
        $a4Height = 297;

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        foreach ($pdfPaths as $file) {
            if (!file_exists($file)) {
                throw new \RuntimeException("Archivo no existe: {$file}");
            }

            $pageCount = $pdf->setSourceFile($file);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

                $tplId = $pdf->importPage($pageNo);
                $size  = $pdf->getTemplateSize($tplId);
                $origW = $size['width'];
                $origH = $size['height'];
                $scale = min($a4Width / $origW, $a4Height / $origH);
                $w = $origW * $scale;
                $h = $origH * $scale;
                $x = ($a4Width  - $w) / 2;
                $y = ($a4Height - $h) / 2;
                $pdf->AddPage('P', 'A4');
                $pdf->useTemplate($tplId, $x, $y, $w, $h);
            }
        }
        $output = tempnam(sys_get_temp_dir(), 'mergedA4_') . '.pdf';
        $pdf->Output($output, 'F');

        return $output;
    }

    public function mergeToA4(Request $request)
    {

        /* 
        $request->validate([
            'pdfs'   => 'required|array|min:2',
            'pdfs.*' => 'required|mimes:pdf'
        ]); */

        $paths = array_map(fn($file) => $file->getRealPath(), $request->file('pdfs'));
        try {
            $merged = $this->mergePdfsToA4($paths);
            return response()
                ->download($merged, 'merged_A4_' . now()->timestamp . '.pdf')
                ->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * “Comprime” un PDF activando la compresión de streams de FPDF/FPDI.
     * No modifica las imágenes (solo comprime los objetos internos).
     *
     * @param string $sourcePath Ruta al PDF original
     * @return string            Ruta al PDF comprimido (temporal)
     * @throws \Exception
     */
    protected function compressPdfFpdi(string $sourcePath): string
    {
        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException("No existe el PDF: {$sourcePath}");
        }
        $pdf = new Fpdi();
        $pdf->SetCompression(true);
        $pdf->SetTitle('');
        $pdf->SetSubject('');
        $pdf->SetAuthor('');
        $pdf->SetCreator('');
        $pdf->SetKeywords('');
        $pdf->SetAutoPageBreak(false);
        $pageCount = $pdf->setSourceFile($sourcePath);
        for ($p = 1; $p <= $pageCount; $p++) {
            $tplId = $pdf->importPage($p);
            $size  = $pdf->getTemplateSize($tplId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
        }
        $outPath = tempnam(sys_get_temp_dir(), 'compressedFpdi_') . '.pdf';
        $pdf->Output($outPath, 'F');
        return $outPath;
    }


    public function compress(Request $request)
    {
        $request->validate(['pdf' => 'required|mimes:pdf']);
        $src = $request->file('pdf')->getRealPath();

        try {
            $compressed = $this->compressPdfFpdi($src);
            return response()
                ->download($compressed, 'compressed_' . now()->timestamp . '.pdf')
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Reordena las páginas de un PDF según el array `order` recibido.
     *
     * @param  Request  $request
     *     - pdf: archivo PDF (multipart/form-data)
     *     - order: array de números de página en el orden deseado, ej. [3,1,2,5]
     * @return \Illuminate\Http\Response
     */
    public function reorderPdf(Request $request)
    {
        $request->validate([
            'pdf'   => 'required|file|mimes:pdf',
            'order' => 'required|array|min:1',
            'order.*' => 'integer|min:1',
        ]);

        $pdfPath = $request->file('pdf')->getRealPath();
        $newOrder = $request->input('order');

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        $pageCount = $pdf->setSourceFile($pdfPath);

        foreach ($newOrder as $p) {
            if ($p < 1 || $p > $pageCount) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Número de página inválido en 'order': {$p}"
                ], 422);
            }
        }

        foreach ($newOrder as $pageNo) {
            $tplId = $pdf->importPage($pageNo);
            $size  = $pdf->getTemplateSize($tplId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
        }

        $outputPath = tempnam(sys_get_temp_dir(), 'reordered_') . '.pdf';
        $pdf->Output($outputPath, 'F');

        return response()->download($outputPath, 'reordered_' . now()->timestamp . '.pdf')
            ->deleteFileAfterSend(true);
    }
}