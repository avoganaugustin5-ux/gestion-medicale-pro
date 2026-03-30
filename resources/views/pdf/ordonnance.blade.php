<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; }
        .clinic-name { font-size: 22px; font-weight: bold; color: #4f46e5; text-transform: uppercase; }
        .info-section { margin-top: 30px; }
        .patient-box { float: right; border: 1px solid #ddd; padding: 15px; border-radius: 10px; width: 250px; }
        .content { margin-top: 200px; }
        .prescription-title { font-weight: bold; text-decoration: underline; margin-bottom: 15px; }
        .prescription-text { font-style: italic; line-height: 1.6; }
        .footer { position: fixed; bottom: 0; text-align: center; width: 100%; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <div class="clinic-name">{{ $clinic->name }}</div>
        <p>Service Médical Spécialisé - UTS</p>
    </div>

    <div class="info-section">
        <div style="float: left;">
            <strong>Docteur :</strong> {{ $consultation->doctor->user->name }}<br>
            <strong>Date :</strong> {{ $date }}
        </div>
        <div class="patient-box">
            <strong>Patient :</strong> {{ $patient->last_name }} {{ $patient->first_name }}<br>
            <strong>Contact :</strong> {{ $patient->phone }}
        </div>
    </div>

    <div class="content">
        <div class="prescription-title">ORDONNANCE :</div>
        <div class="prescription-text">
            {!! nl2br(e($consultation->prescription)) !!}
        </div>
    </div>

    <div class="footer">
        Document généré via Gestion-Médicale-Pro. L'authenticité peut être vérifiée auprès de la clinique.
    </div>
</body>
</html>