<?php

namespace App\Http\Controllers;

use App\Models\MeasurementGroup;
use App\Models\Measurement;
use App\Models\CalibrationRecord;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeasurementGroupController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $measurementGroups = MeasurementGroup::whereHas('calibrationRecord.gage.department', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        })->with(['calibrationRecord.gage', 'measurements'])->latest()->paginate(20);

        return Inertia::render('MeasurementGroups/Index', [
            'measurementGroups' => $measurementGroups,
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

        return Inertia::render('MeasurementGroups/Create', [
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
            'group_number' => 'required|string|max:255',
            'group_name' => 'required|string|max:255',
            'type' => 'required|in:tolerance_percent,tolerance_plus_minus,limits',
            'plus_value' => 'nullable|numeric',
            'minus_value' => 'nullable|numeric',
            'mask_min' => 'nullable|numeric',
            'mask_max' => 'nullable|numeric',
            'units' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'show_sequence' => 'boolean',
            'show_description' => 'boolean',
            'auto_fill_after' => 'boolean',
            'show_uncertainty' => 'boolean',
            'show_standards' => 'boolean',
            'measurements' => 'required|array|min:1',
            'measurements.*.nominal' => 'required|numeric',
            'measurements.*.description' => 'nullable|string|max:255',
        ]);

        // Verify calibration record belongs to user's company
        $calibrationRecord = CalibrationRecord::whereHas('gage.department', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        })->findOrFail($request->calibration_record_id);

        $measurementGroup = MeasurementGroup::create($request->only([
            'calibration_record_id', 'group_number', 'group_name', 'type',
            'plus_value', 'minus_value', 'mask_min', 'mask_max', 'units', 'notes',
            'show_sequence', 'show_description', 'auto_fill_after', 'show_uncertainty', 'show_standards'
        ]));

        // Create measurements
        foreach ($request->measurements as $index => $measurementData) {
            $nominal = (float) $measurementData['nominal'];
            $limits = $measurementGroup->calculateLimits($nominal);
            
            Measurement::create([
                'measurement_group_id' => $measurementGroup->id,
                'sequence' => $index + 1,
                'nominal' => $nominal,
                'upper_limit' => $limits['upper'],
                'lower_limit' => $limits['lower'],
                'description' => $measurementData['description'] ?? null,
            ]);
        }

        return redirect()->route('calibration-records.show', $calibrationRecord->id)
            ->with('success', 'Measurement group created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MeasurementGroup $measurementGroup)
    {
        // Authorize access
        if ($measurementGroup->calibrationRecord->gage->department->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $measurementGroup->load(['calibrationRecord.gage', 'measurements']);

        return Inertia::render('MeasurementGroups/Show', [
            'measurementGroup' => $measurementGroup,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MeasurementGroup $measurementGroup)
    {
        // Authorize access
        if ($measurementGroup->calibrationRecord->gage->department->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $measurementGroup->load(['calibrationRecord.gage', 'measurements']);

        return Inertia::render('MeasurementGroups/Edit', [
            'measurementGroup' => $measurementGroup,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MeasurementGroup $measurementGroup)
    {
        // Authorize access
        if ($measurementGroup->calibrationRecord->gage->department->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $request->validate([
            'group_number' => 'required|string|max:255',
            'group_name' => 'required|string|max:255',
            'type' => 'required|in:tolerance_percent,tolerance_plus_minus,limits',
            'plus_value' => 'nullable|numeric',
            'minus_value' => 'nullable|numeric',
            'mask_min' => 'nullable|numeric',
            'mask_max' => 'nullable|numeric',
            'units' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'show_sequence' => 'boolean',
            'show_description' => 'boolean',
            'auto_fill_after' => 'boolean',
            'show_uncertainty' => 'boolean',
            'show_standards' => 'boolean',
        ]);

        $measurementGroup->update($request->only([
            'group_number', 'group_name', 'type',
            'plus_value', 'minus_value', 'mask_min', 'mask_max', 'units', 'notes',
            'show_sequence', 'show_description', 'auto_fill_after', 'show_uncertainty', 'show_standards'
        ]));

        return redirect()->route('measurement-groups.show', $measurementGroup->id)
            ->with('success', 'Measurement group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MeasurementGroup $measurementGroup)
    {
        // Authorize access
        if ($measurementGroup->calibrationRecord->gage->department->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $calibrationRecordId = $measurementGroup->calibration_record_id;
        $measurementGroup->delete();

        return redirect()->route('calibration-records.show', $calibrationRecordId)
            ->with('success', 'Measurement group deleted successfully.');
    }

    /**
     * Update measurement values (as found/as left)
     */
    public function updateMeasurements(Request $request, MeasurementGroup $measurementGroup)
    {
        // Authorize access
        if ($measurementGroup->calibrationRecord->gage->department->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $request->validate([
            'measurements' => 'required|array',
            'measurements.*.id' => 'required|exists:measurements,id',
            'measurements.*.as_found_value' => 'nullable|numeric',
            'measurements.*.as_left_value' => 'nullable|numeric',
            'measurements.*.uncertainty' => 'nullable|numeric',
            'measurements.*.standards_used' => 'nullable|string',
        ]);

        $hasOutOfToleranceConditions = false;
        $failedMeasurements = [];

        foreach ($request->measurements as $measurementData) {
            $measurement = Measurement::find($measurementData['id']);
            
            // Update values
            $measurement->fill([
                'as_found_value' => $measurementData['as_found_value'] ?? null,
                'as_left_value' => $measurementData['as_left_value'] ?? null,
                'uncertainty' => $measurementData['uncertainty'] ?? null,
                'standards_used' => $measurementData['standards_used'] ?? null,
            ]);

            // Calculate pass/fail status
            $measurement->calculateAsFoundStatus();
            $measurement->calculateAsLeftStatus();
            $measurement->save();

            // Check for out-of-tolerance conditions
            if ($measurement->as_found_pass === false || $measurement->as_left_pass === false) {
                $hasOutOfToleranceConditions = true;
                $failedMeasurements[] = [
                    'description' => $measurement->description ?: "Point {$measurement->sequence}",
                    'nominal' => $measurement->nominal,
                    'as_found_value' => $measurement->as_found_value,
                    'as_left_value' => $measurement->as_left_value,
                    'as_found_pass' => $measurement->as_found_pass,
                    'as_left_pass' => $measurement->as_left_pass,
                ];
            }
        }

        // If out-of-tolerance conditions detected, redirect to risk analysis workflow
        if ($hasOutOfToleranceConditions) {
            // Check if tolerance record already exists for this calibration record
            $existingToleranceRecord = \App\Models\GageToleranceRecord::where('calibration_record_id', $measurementGroup->calibration_record_id)
                ->where('status', '!=', 'resolved')
                ->first();

            if (!$existingToleranceRecord) {
                return redirect()->route('gage-tolerance-records.create', [
                    'calibration_record_id' => $measurementGroup->calibration_record_id
                ])->with('warning', 'Out-of-tolerance condition detected! Please complete the risk analysis and document corrective actions.')
                ->with('failed_measurements', $failedMeasurements);
            } else {
                return back()->with('warning', 'Out-of-tolerance condition detected! An existing tolerance record is already tracking this issue.')
                ->with('existing_tolerance_record', $existingToleranceRecord->id);
            }
        }

        return back()->with('success', 'Measurements updated successfully. All measurements are within tolerance.');
    }
}
