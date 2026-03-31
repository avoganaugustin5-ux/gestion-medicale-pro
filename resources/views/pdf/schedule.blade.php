<!DOCTYPE html>
<html>
<head>
    <title>Planning Hebdomadaire</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Planning Hebdomadaire - UTS Santé</h1>
        <p><strong>Médecin :</strong> Dr. {{ $doctor->name }}</p>
        <p><strong>Clinique :</strong> {{ $doctor->clinic->name ?? 'N/A' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($availabilities as $a)
            <tr>
                <td>{{ \Carbon\Carbon::parse($a->date)->format('d/m/Y') }}</td>
                <td>{{ $a->start_time }}</td>
                <td>{{ $a->end_time }}</td>
                <td>{{ $a->status === 'cancelled' ? 'ANNULÉ' : ($a->is_booked ? 'OCCUPÉ' : 'DISPONIBLE') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>