<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Radicado</th>
            <th>Responsable</th>
            <th>Nombre Estado</th>
            <th>Fecha de creación</th>
            <th>Fecha de finalización</th>
            <th>Tiempo(min)</th>
            <th>Estado oportuno</th>
            <th>Estado Inicial id</th>
            <th>Estado Final id</th>
        </tr>
    </thead>
    <tbody>
        @foreach($estados as $estado)
        <tr>
            <td>{{ $estado->id }}</td>
            <td>{{ $estado->radicado }}</td>
            <td>{{ $estado->responsable_inicial }}</td>
            <td>{{ $estado->nombre_estado }}</td>
            <td>{{ $estado->estado_created_at }}</td>
            <td>{{ $estado->estado_updated_at }}</td>
            <td>{{ $estado->tiempo }}</td>
            <td>{{ $estado->oportuno }}</td>
            <td>{{ $estado->estados_firma_inicial }}</td>
            <td>{{ $estado->estados_firma_final }}</td>
        </tr>
        @endforeach
    </tbody>
</table>