<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket de Rendez-vous</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #1f2937; line-height: 1.5; margin: 0; padding: 0; }
        .ticket-container { padding: 40px; border: 2px dashed #4f46e5; max-width: 600px; margin: 50px auto; border-radius: 20px; }
        .header { text-align: center; border-bottom: 2px solid #f3f4f6; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #4f46e5; margin: 0; text-transform: uppercase; font-size: 24px; }
        .header p { margin: 5px 0 0; font-size: 14px; color: #6b7280; }
        .content { margin-bottom: 30px; }
        .row { margin-bottom: 15px; }
        .label { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #9ca3af; letter-spacing: 0.1em; }
        .value { font-size: 16px; font-weight: bold; color: #111827; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; background: #dcfce7; color: #166534; font-size: 12px; font-weight: bold; }
        .footer { border-top: 1px solid #f3f4f6; padding-top: 20px; text-align: center; font-size: 11px; color: #6b7280; }
        .id-number { font-family: monospace; color: #4f46e5; font-weight: bold; }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="header">
            <h1>{{ $appointment->clinic->name }}</h1>
            <p>Ticket de Confirmation de Rendez-vous</p>
        </div>

        <div class="content">
            <div class="row">
                <div class="label">ID Rendez-vous</div>
                <div class="id-number">#{{ str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>

            <div class="row">
                <div class="label">Patient</div>
                <div class="value">{{ $appointment->patient->user->name ?? $appointment->patient->first_name . ' ' . $appointment->patient->last_name }}</div>
            </div>

            <div class="row">
                <div class="label">Médecin</div>
                <div class="value">Dr. {{ $appointment->doctor->user->name }}</div>
            </div>

            <div class="row">
                <div class="label">Date & Heure</div>
                <div class="value">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y à H:i') }}</div>
            </div>

            <div class="row">
                <div class="label">Service</div>
                <div class="value">{{ $appointment->service->nom }}</div>
            </div>

            <div class="row">
                <div class="label">Statut</div>
                <div class="status-badge">CONFIRMÉ</div>
            </div>
        </div>

        <div class="footer">
            <p>Présentez ce ticket à l'accueil de la clinique AKASUTS.</p>
            <p>&copy; {{ date('Y') }} Université Thomas SANKARA - Service Médical Universitaire</p>
        </div>
    </div>
</body>
</html>