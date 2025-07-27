<?php

namespace App\Http\Controllers;

use App\Models\CalibrationRecord;
use App\Models\Gage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalibrationRecordController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CalibrationRecord::class);

        $query = CalibrationRecord::with(['gage.department', 'user'])
            ->whereHas('gage.department', function ($q) {
                $q->where('company_id', auth()->user()->company_id);
            });

        if ($request->has('gage_id')) {
            $query->where('gage_id', $request->gage_id);
        }

        $calibrationRecords = $query->latest()->get();
        
        // Add permission check for each record
        $calibrationRecords->each(function ($record) {
            $record->can_delete = auth()->user()->can('delete', $record);
        });
        
        return Inertia::render('CalibrationRecords/Index', [
            'calibrationRecords' => $calibrationRecords,
            'gage_id' => $request->gage_id,
            'user' => [
                'is_admin' => auth()->user()->isAdmin(),
                'id' => auth()->user()->id,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', CalibrationRecord::class);

        $gages = auth()->user()->company->gages()->with('department')->get();

        return Inertia::render('CalibrationRecords/Create', [
            'gages' => $gages,
            'gage_id' => $request->gage_id,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', CalibrationRecord::class);

        $request->validate([
            'gage_id' => 'required|exists:gages,id',
            'calibrated_at' => 'required|date',
            'cert_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
            'workflow_type' => 'required|in:detailed,simple',
            // Legacy fields (only required for simple workflow)
            'measured_value' => 'required_if:workflow_type,simple|nullable|numeric',
            'calibrated_value' => 'required_if:workflow_type,simple|nullable|numeric',
        ]);

        // Verify gage belongs to user's company
        $gage = auth()->user()->company->gages()->findOrFail($request->gage_id);

        $data = [
            'gage_id' => $request->gage_id,
            'calibrated_at' => $request->calibrated_at,
            'user_id' => auth()->id(),
        ];

        // For simple workflow, include the legacy measured/calibrated values
        if ($request->workflow_type === 'simple') {
            $data['measured_value'] = $request->measured_value;
            $data['calibrated_value'] = $request->calibrated_value;
        } else {
            // For detailed workflow, use placeholder values that will be replaced by measurement groups
            $data['measured_value'] = 0;
            $data['calibrated_value'] = 0;
        }

        // Handle file upload
        if ($request->hasFile('cert_file')) {
            $file = $request->file('cert_file');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $data['cert_file'] = $file->storeAs('calibration-certificates', $filename, 'public');
        }

        $calibrationRecord = CalibrationRecord::create($data);

        // Redirect based on workflow type
        if ($request->workflow_type === 'detailed') {
            // For detailed workflow, redirect to create measurement groups
            return redirect()->route('measurement-groups.create', ['calibration_record_id' => $calibrationRecord->id])
                ->with('success', 'Calibration record created successfully. Now add measurement groups for detailed tracking.');
        } else {
            // For simple workflow, go back to the index
            return redirect()->route('calibration-records.index', ['gage_id' => $request->gage_id])
                ->with('success', 'Calibration record created successfully.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CalibrationRecord $calibrationRecord)
    {
        $this->authorize('view', $calibrationRecord);
        
        $calibrationRecord->load([
            'gage.department', 
            'user',
            'measurementGroups.measurements' => function ($query) {
                $query->orderBy('sequence');
            }
        ]);

        return Inertia::render('CalibrationRecords/Show', [
            'calibrationRecord' => $calibrationRecord,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CalibrationRecord $calibrationRecord)
    {
        $this->authorize('update', $calibrationRecord);

        $gages = auth()->user()->company->gages()->with('department')->get();

        return Inertia::render('CalibrationRecords/Edit', [
            'calibrationRecord' => $calibrationRecord,
            'gages' => $gages,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CalibrationRecord $calibrationRecord)
    {
        $this->authorize('update', $calibrationRecord);

        $request->validate([
            'gage_id' => 'required|exists:gages,id',
            'measured_value' => 'required|numeric',
            'calibrated_value' => 'required|numeric',
            'calibrated_at' => 'required|date',
            'cert_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
        ]);

        // Verify gage belongs to user's company
        $gage = auth()->user()->company->gages()->findOrFail($request->gage_id);

        $data = [
            'gage_id' => $request->gage_id,
            'measured_value' => $request->measured_value,
            'calibrated_value' => $request->calibrated_value,
            'calibrated_at' => $request->calibrated_at,
        ];

        // Handle file upload
        if ($request->hasFile('cert_file')) {
            // Delete old file if it exists
            if ($calibrationRecord->cert_file) {
                \Storage::disk('public')->delete($calibrationRecord->cert_file);
            }
            
            $file = $request->file('cert_file');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $data['cert_file'] = $file->storeAs('calibration-certificates', $filename, 'public');
        }

        $calibrationRecord->update($data);

        return redirect()->route('calibration-records.index', ['gage_id' => $calibrationRecord->gage_id])
            ->with('success', 'Calibration record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CalibrationRecord $calibrationRecord)
    {
        $this->authorize('delete', $calibrationRecord);

        $gageId = $calibrationRecord->gage_id;
        
        // Delete associated file if it exists
        if ($calibrationRecord->cert_file) {
            \Storage::disk('public')->delete($calibrationRecord->cert_file);
        }
        
        $calibrationRecord->delete();

        return redirect()->route('calibration-records.index', ['gage_id' => $gageId])
            ->with('success', 'Calibration record deleted successfully.');
    }

    /**
     * Download the calibration certificate.
     */
    public function downloadCertificate(CalibrationRecord $calibrationRecord)
    {
        $this->authorize('view', $calibrationRecord);

        if (!$calibrationRecord->cert_file || !$calibrationRecord->hasCertificate()) {
            abort(404, 'Certificate not found.');
        }

        return \Storage::disk('public')->download($calibrationRecord->cert_file);
    }
}
