<!DOCTYPE html>
<html>
<head>
    <title>Carnet Médical - UTS Santé</title>
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CARNET DE SANTÉ DIGITALE</h1>
        <p>Université Thomas SANKARA (UTS)</p>
    </div>
    <h2>Informations du Patient</h2>
    <p>Nom : {{ $patient->last_name }} {{ $patient->first_name }}</p>
    <p>Téléphone : {{ $patient->phone }}</p>

    <h3>Historique des Consultations</h3>
    @if($consultations->count() > 0)
        @foreach($consultations as $consultation)
            <div style="margin-bottom: 15px; border: 1px solid #ccc; padding: 10px;">
                <strong>Date :</strong> {{ $consultation->created_at->format('d/m/Y') }}<br>
                <strong>Diagnostic :</strong> {{ $consultation->diagnosis }}
            </div>
        @endforeach
    @else
        <p>Aucune consultation enregistrée.</p>
    @endif
</body>
</html>