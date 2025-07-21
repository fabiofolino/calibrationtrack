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
        
        return Inertia::render('CalibrationRecords/Index', [
            'calibrationRecords' => $calibrationRecords,
            'gage_id' => $request->gage_id,
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
            'measured_value' => 'required|numeric',
            'calibrated_value' => 'required|numeric',
            'calibrated_at' => 'required|date',
        ]);

        // Verify gage belongs to user's company
        $gage = auth()->user()->company->gages()->findOrFail($request->gage_id);

        CalibrationRecord::create([
            'gage_id' => $request->gage_id,
            'measured_value' => $request->measured_value,
            'calibrated_value' => $request->calibrated_value,
            'calibrated_at' => $request->calibrated_at,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('calibration-records.index', ['gage_id' => $request->gage_id])
            ->with('success', 'Calibration record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CalibrationRecord $calibrationRecord)
    {
        $this->authorize('view', $calibrationRecord);
        
        $calibrationRecord->load(['gage.department', 'user']);

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
        ]);

        // Verify gage belongs to user's company
        $gage = auth()->user()->company->gages()->findOrFail($request->gage_id);

        $calibrationRecord->update([
            'gage_id' => $request->gage_id,
            'measured_value' => $request->measured_value,
            'calibrated_value' => $request->calibrated_value,
            'calibrated_at' => $request->calibrated_at,
        ]);

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
        $calibrationRecord->delete();

        return redirect()->route('calibration-records.index', ['gage_id' => $gageId])
            ->with('success', 'Calibration record deleted successfully.');
    }
}
