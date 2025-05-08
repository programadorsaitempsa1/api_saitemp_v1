<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;
use Illuminate\Support\Carbon;

class HorarioLaboralController extends Controller
{

    function cuentaFindes($fechaInicio, $fechaFin)
    {
        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);
        $diffDays = $inicio->diff($fin)->days + 1;
        $cuentaFinde = 0;
        for ($i = 0; $i < $diffDays; $i++) {

            $diaSemana = $inicio->format('w');

            if ($diaSemana == 0 || $diaSemana == 6) {
                $cuentaFinde++;
            }
            $inicio->modify('+1 day');
        }
        return $cuentaFinde;
    }

    private function applyTwoDigits($number): string
    {
        return $number < 10 ? '0' . $number : (string) $number;
    }

    /**
     * Aplica el formato DD/MM/YYYY a una fecha.
     */
    public function formatDate(Carbon $date): string
    {
        return $this->applyTwoDigits($date->day) . '/' . $this->applyTwoDigits($date->month) . '/' . $date->year;
    }

    /**
     * Calcula la fecha del Domingo de Resurrección/Pascua.
     */
    private static function getEasterSunday(int $year): Carbon
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::createFromDate($year, $month, $day);
    }

    /**
     * Calcula el próximo lunes de una fecha dada.
     */
    public function getNextMonday(Carbon $date): Carbon
    {
        while ($date->dayOfWeek !== Carbon::MONDAY) {
            $date->addDay();
        }
        return $date;
    }

    /**
     * Suma una cantidad de días a una fecha dada.
     */
    public function sumDay(Carbon $date, int $daysToSum): Carbon
    {
        return $date->addDays($daysToSum);
    }

    /**
     * Calcula y retorna el listado de festivos de un año dado.
     */
    public function getHolidaysByYear(int $year): array
    {
        $holidaysArray = [];
        $easterSunday = $this->getEasterSunday($year);

        foreach (self::HOLIDAYS as $holiday) {
            $date = isset($holiday['daysToSum'])
                ? $this->sumDay($easterSunday->copy(), $holiday['daysToSum'])
                : Carbon::create($year, explode('/', $holiday['date'])[1], explode('/', $holiday['date'])[0]);

            if ($holiday['nextMonday'] ?? false) {
                $date = $this->getNextMonday($date);
            }

            $holidaysArray[] = [
                'date' => $this->formatDate($date),
                'name' => $holiday['name'],
                'static' => $holiday['nextMonday'] ?? false,
            ];
        }

        return $holidaysArray;
    }

    /**
     * Calcula todos los días festivos de un rango de años.
     */
    public function getHolidaysByYearInterval(int $initialYear, int $finalYear): array
    {
        $holidaysArray = [];

        for ($year = $initialYear; $year <= $finalYear; $year++) {
            $holidaysArray[] = [
                'year' => $year,
                'holidays' => $this->getHolidaysByYear($year),
            ];
        }

        return $holidaysArray;
    }

    /**
     * Determina si una fecha específica es festivo.
     */
    public function isHoliday(Carbon $date): bool
    {
        $holidays = $this->getHolidaysByYear($date->year);

        foreach ($holidays as $holiday) {
            if ($holiday['date'] === $this->formatDate($date)) {
                return true;
            }
        }

        return false;
    }
    private const HOLIDAYS = [
        ['date' => '01/01', 'nextMonday' => false, 'name' => 'Año Nuevo'],
        ['date' => '06/01', 'nextMonday' => true, 'name' => 'Día de los Reyes Magos'],
        ['date' => '19/03', 'nextMonday' => true, 'name' => 'Día de San José'],
        ['daysToSum' => -3, 'nextMonday' => false, 'name' => 'Jueves Santo'],
        ['daysToSum' => -2, 'nextMonday' => false, 'name' => 'Viernes Santo'],
        ['date' => '01/05', 'nextMonday' => false, 'name' => 'Día del Trabajo'],
        ['daysToSum' => 40, 'nextMonday' => true, 'name' => 'Ascensión del Señor'],
        ['daysToSum' => 60, 'nextMonday' => true, 'name' => 'Corpus Christi'],
        ['daysToSum' => 71, 'nextMonday' => true, 'name' => 'Sagrado Corazón de Jesús'],
        ['date' => '29/06', 'nextMonday' => true, 'name' => 'San Pedro y San Pablo'],
        ['date' => '20/07', 'nextMonday' => false, 'name' => 'Día de la Independencia'],
        ['date' => '07/08', 'nextMonday' => false, 'name' => 'Batalla de Boyacá'],
        ['date' => '15/08', 'nextMonday' => true, 'name' => 'La Asunción de la Virgen'],
        ['date' => '12/10', 'nextMonday' => true, 'name' => 'Día de la Raza'],
        ['date' => '01/11', 'nextMonday' => true, 'name' => 'Todos los Santos'],
        ['date' => '11/11', 'nextMonday' => true, 'name' => 'Independencia de Cartagena'],
        ['date' => '08/12', 'nextMonday' => false, 'name' => 'Día de la Inmaculada Concepción'],
        ['date' => '25/12', 'nextMonday' => false, 'name' => 'Día de Navidad'],
    ];

    public static function countHolidaysBetweenDates(string $startDate, string $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->greaterThan($end)) {
            throw new \InvalidArgumentException("La fecha de inicio debe ser anterior a la fecha de fin.");
        }

        $holidays = self::getHolidaysForYearRange($start->year, $end->year);
        $count = 0;

        foreach ($holidays as $holiday) {
            $holidayDate = Carbon::createFromFormat('d/m/Y', $holiday['date']);
            if ($holidayDate->between($start, $end)) {
                $count++;
            }
        }

        return $count;
    }

    private static function getHolidaysForYearRange(int $startYear, int $endYear): array
    {
        $holidays = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            $holidays = array_merge($holidays, self::getHolidaysForYear($year));
        }
        return $holidays;
    }

    private static function getHolidaysForYear(int $year): array
    {
        $holidays = [];
        $easterSunday = self::getEasterSunday($year);

        foreach (self::HOLIDAYS as $holiday) {
            $date = null;
            if (isset($holiday['date'])) {
                $date = Carbon::createFromFormat('d/m/Y', "{$holiday['date']}/{$year}");
            } elseif (isset($holiday['daysToSum'])) {
                $date = $easterSunday->copy()->addDays($holiday['daysToSum']);
            }

            if ($holiday['nextMonday'] && $date->dayOfWeek !== Carbon::MONDAY) {
                $date = $date->copy()->next(Carbon::MONDAY);
            }

            $holidays[] = [
                'date' => $date->format('d/m/Y'),
                'name' => $holiday['name'],
            ];
        }

        return $holidays;
    }
}
