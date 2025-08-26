<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Radicado</th>
            <th>Sede</th>
            <th>Proceso</th>
            <th>Solicitante</th>
            <th>Nombre/razon social</th>
            <th>Medio de atención</th>
            <th>Responsable</th>
            <th>Tipo de PQRSF</th>
            <th>Teléfono de contacto</th>
            <th>Correo de contacto</th>
            <th>Estado</th>
            <th>Observaciones</th>
            <th>Fecha de creación</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $estado)
        <tr>
            <td>{{ $estado->id }}</td>
            <td>{{ $estado->numero_radicado }}</td>
            <td>{{ $estado->sede }}</td>
            <td>{{ $estado->proceso }}</td>
            <td>{{ $estado->solicitante }}</td>
            <td>{{ $estado->nombre_contacto }}</td>
            <td>{{ $estado->iteraccion }}</td>
            <td>{{ $estado->responsable }}</td>
            <td>{{ $estado->pqrsf }}</td>
            <td>{{ $estado->telefono }}</td>
            <td>{{ $estado->correo }}</td>
            <td>{{ $estado->estado }}</td>
            <td>{{ $estado->observacion }}</td>
            <td>{{ $estado->created_at }}</td>
        </tr>
        @endforeach
    </tbody>
</table>