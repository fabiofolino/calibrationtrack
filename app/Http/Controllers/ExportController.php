<?php

namespace App\Http\Controllers;

use App\Models\Gage;
use App\Models\CalibrationRecord;
use App\Exports\GagesExport;
use App\Exports\CalibrationRecordsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class ExportController extends Controller
{
    /**
     * Export gages to CSV
     */
    public function exportGagesCSV()
    {
        $gages = auth()->user()->company->gages()->with(['department', 'calibrationRecords' => function ($query) {
            $query->latest()->limit(1);
        }])->get();

        $data = $gages->map(function ($gage) {
            return [
                'Name' => $gage->name,
                'Serial Number' => $gage->serial_number,
                'Department' => $gage->department->name,
                'Frequency (Days)' => $gage->frequency_days,
                'Next Due Date' => $gage->next_due_date ? $gage->next_due_date->format('Y-m-d') : 'N/A',
                'Status' => ucfirst(str_replace('_', ' ', $gage->getCalibrationStatus())),
                'Days Until Due' => $gage->getDaysUntilDue() ?? 'N/A',
                'Last Calibration' => $gage->calibrationRecords->first() ? 
                    $gage->calibrationRecords->first()->calibrated_at->format('Y-m-d H:i') : 'Never',
                'Created At' => $gage->created_at->format('Y-m-d H:i'),
            ];
        });

        $filename = 'gages_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return Excel::download(new GagesExport($data), $filename);
    }

    /**
     * Export gages to PDF
     */
    public function exportGagesPDF()
    {
        $company = auth()->user()->company;
        $gages = $company->gages()->with(['department', 'calibrationRecords' => function ($query) {
            $query->latest()->limit(1);
        }])->get();

        $data = [
            'company' => $company,
            'gages' => $gages,
            'export_date' => now(),
            'title' => 'Gages Report'
        ];

        $pdf = Pdf::loadView('exports.gages-pdf', $data);
        $filename = 'gages_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export calibration records to CSV
     */
    public function exportCalibrationRecordsCSV()
    {
        $records = CalibrationRecord::whereHas('gage.department', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        })->with(['gage.department', 'user'])->latest('calibrated_at')->get();

        $data = $records->map(function ($record) {
            return [
                'Gage Name' => $record->gage->name,
                'Gage Serial Number' => $record->gage->serial_number,
                'Department' => $record->gage->department->name,
                'Measured Value' => $record->measured_value,
                'Calibrated Value' => $record->calibrated_value,
                'Calibrated At' => $record->calibrated_at->format('Y-m-d H:i'),
                'Calibrated By' => $record->user->name,
                'Created At' => $record->created_at->format('Y-m-d H:i'),
            ];
        });

        $filename = 'calibration_records_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return Excel::download(new CalibrationRecordsExport($data), $filename);
    }

    /**
     * Export calibration records to PDF
     */
    public function exportCalibrationRecordsPDF()
    {
        $company = auth()->user()->company;
        $records = CalibrationRecord::whereHas('gage.department', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        })->with(['gage.department', 'user'])->latest('calibrated_at')->get();

        $data = [
            'company' => $company,
            'records' => $records,
            'export_date' => now(),
            'title' => 'Calibration Records Report'
        ];

        $pdf = Pdf::loadView('exports.calibration-records-pdf', $data);
        $filename = 'calibration_records_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }
}