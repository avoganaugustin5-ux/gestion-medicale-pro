<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Planning Hebdomadaire - UTS</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1e293b; line-height: 1.5; }
        .header { border-bottom: 3px solid #4f46e5; padding-bottom: 20px; margin-bottom: 30px; text-align: center; }
        .university-name { font-size: 20px; font-weight: 900; color: #4f46e5; text-transform: uppercase; letter-spacing: 2px; }
        .doc-title { font-size: 16px; font-weight: bold; margin-top: 5px; color: #64748b; }
        .info-section { margin-bottom: 25px; font-size: 12px; }
        .info-box { background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }
        th { background-color: #4f46e5; color: white; padding: 12px; text-align: left; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .status-badge { padding: 4px 8px; border-radius: 5px; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .available { background: #dcfce7; color: #166534; }
        .booked { background: #dbeafe; color: #1e40af; }
        .cancelled { background: #fee2e2; color: #991b1b; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="university-name">Université Thomas SANKARA (UTS)</div>
        <div class="doc-title">PORTAIL SANTÉ - PLANNING HEBDOMADAIRE</div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <strong>PRATICIEN :</strong> Dr. {{ $doctor->name }} <br>
            <strong>ÉTABLISSEMENT :</strong> {{ $doctor->clinic->name ?? 'Thomas Sankara University Clinic' }} <br>
            <strong>GÉNÉRÉ LE :</strong> {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Créneau Horaire</th>
                <th>Durée</th>
                <th>État du créneau</th>
            </tr>
        </thead>
        <tbody>
            @foreach($availabilities as $a)
            <tr>
                <td>{{ \Carbon\Carbon::parse($a->date)->format('d/m/Y') }}</td>
                <td><strong>{{ $a->start_time }} - {{ $a->end_time }}</strong></td>
                <td>{{ \Carbon\Carbon::parse($a->start_time)->diff(\Carbon\Carbon::parse($a->end_time))->format('%Hh %I min') }}</td>
                <td>
                    @if($a->status === 'cancelled')
                        <span class="status-badge cancelled italic">Imprévu / Annulé</span>
                    @elseif($a->is_booked)
                        <span class="status-badge booked">RDV Occupé</span>
                    @else
                        <span class="status-badge available">Disponible</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Document généré via la plateforme AKASUTS - Coder pour améliorer. <br>
        Thomas Sankara University - Licence 3 Informatique
    </div>
</body>
</html>