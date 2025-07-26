<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GagesExport implements FromCollection, WithHeadings
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
            'Gage ID',
            'Description',
            'Model',
            'Manufacturer',
            'Serial Number',
            'Location',
            'Custodian',
            'Department',
            'Frequency (Days)',
            'Next Due Date',
            'Status',
            'Days Until Due',
            'Last Calibration',
            'Created At'
        ];
    }
}