<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf; // Import du moteur PDF

class ConsultationController extends Controller
{
    // ... (méthode store déjà créée)

    /**
     * Générer et télécharger l'ordonnance en PDF
     */
    public function downloadPDF(Clinic $clinic, Patient $patient, Consultation $consultation)
    {
        $data = [
            'clinic' => $clinic,
            'patient' => $patient,
            'consultation' => $consultation->load('doctor.user'),
            'date' => now()->format('d/m/Y'),
        ];

        $pdf = Pdf::loadView('pdf.ordonnance', $data);
        
        return $pdf->download("Ordonnance_{$patient->last_name}_{$consultation->id}.pdf");
    }

    /**
     * Mettre à jour une consultation
     */
    public function update(Request $request, Clinic $clinic, Patient $patient, Consultation $consultation)
    {
        $request->validate([
            'diagnostic' => 'required|string',
            'prescription' => 'nullable|string',
        ]);

        $consultation->update($request->only(['diagnostic', 'prescription']));

        return redirect()->back()->with('success', 'Ordonnance mise à jour.');
    }

    /**
     * Supprimer une consultation
     */
    public function destroy(Clinic $clinic, Patient $patient, Consultation $consultation)
    {
        $consultation->delete();
        return redirect()->back()->with('success', 'Ordonnance supprimée.');
    }
}