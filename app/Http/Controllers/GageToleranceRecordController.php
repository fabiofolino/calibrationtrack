<?php

namespace App\Http\Controllers;

use App\Models\GageToleranceRecord;
use App\Models\Gage;
use App\Models\CalibrationRecord;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GageToleranceRecordController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $toleranceRecords = GageToleranceRecord::whereHas('gage.department', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        })->with(['gage', 'calibrationRecord', 'user'])->latest()->paginate(20);

        return Inertia::render('GageToleranceRecords/Index', [
            'toleranceRecords' => $toleranceRecords,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $calibrationRecord = CalibrationRecord::whereHas('gage.department', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        })->with('gage')->findOrFail($request->calibration_record_id);

        return Inertia::render('GageToleranceRecords/Create', [
            'calibrationRecord' => $calibrationRecord,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'calibration_record_id' => 'required|exists:calibration_records,id',
            'risk_assessment' => 'required|string|max:1000',
            'corrective_actions' => 'required|string|max:1000',
        ]);

        // Verify calibration record belongs to user's company
        $calibrationRecord = CalibrationRecord::whereHas('gage.department', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        })->findOrFail($request->calibration_record_id);

        GageToleranceRecord::create([
            'gage_id' => $calibrationRecord->gage_id,
            'calibration_record_id' => $request->calibration_record_id,
            'user_id' => auth()->id(),
            'risk_assessment' => $request->risk_assessment,
            'corrective_actions' => $request->corrective_actions,
            'identified_at' => now(),
        ]);

        return redirect()->route('gage-tolerance-records.index')
            ->with('success', 'Out-of-tolerance record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GageToleranceRecord $gageToleranceRecord)
    {
        // Authorize that the tolerance record belongs to user's company
        if ($gageToleranceRecord->gage->department->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $gageToleranceRecord->load(['gage', 'calibrationRecord', 'user']);

        return Inertia::render('GageToleranceRecords/Show', [
            'toleranceRecord' => $gageToleranceRecord,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GageToleranceRecord $gageToleranceRecord)
    {
        // Authorize that the tolerance record belongs to user's company
        if ($gageToleranceRecord->gage->department->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $gageToleranceRecord->load(['gage', 'calibrationRecord']);

        return Inertia::render('GageToleranceRecords/Edit', [
            'toleranceRecord' => $gageToleranceRecord,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GageToleranceRecord $gageToleranceRecord)
    {
        // Authorize that the tolerance record belongs to user's company
        if ($gageToleranceRecord->gage->department->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:out_of_tolerance,under_review,resolved',
            'risk_assessment' => 'required|string|max:1000',
            'corrective_actions' => 'required|string|max:1000',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $data = $request->only(['status', 'risk_assessment', 'corrective_actions', 'resolution_notes']);
        
        if ($request->status === 'resolved') {
            $data['resolved_at'] = now();
        }

        $gageToleranceRecord->update($data);

        return redirect()->route('gage-tolerance-records.index')
            ->with('success', 'Tolerance record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GageToleranceRecord $gageToleranceRecord)
    {
        // Authorize that the tolerance record belongs to user's company
        if ($gageToleranceRecord->gage->department->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $gageToleranceRecord->delete();

        return redirect()->route('gage-tolerance-records.index')
            ->with('success', 'Tolerance record deleted successfully.');
    }

    /**
     * Mark a tolerance record as resolved
     */
    public function resolve(Request $request, GageToleranceRecord $gageToleranceRecord)
    {
        // Authorize that the tolerance record belongs to user's company
        if ($gageToleranceRecord->gage->department->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $request->validate([
            'resolution_notes' => 'required|string|max:1000',
        ]);

        $gageToleranceRecord->markAsResolved($request->resolution_notes);

        return back()->with('success', 'Tolerance record marked as resolved.');
    }
}
