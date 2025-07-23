<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CalibrationRecordsExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Gage Name',
            'Gage Serial Number',
            'Department',
            'Measured Value',
            'Calibrated Value', 
            'Calibrated At',
            'Calibrated By',
            'Created At'
        ];
    }
}