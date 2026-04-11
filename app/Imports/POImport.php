<?php

namespace App\Imports;

use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Agar baris judul di Excel dilewati

class POImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new PurchaseOrder([
            'po_number'     => $row['po_number'],   // Sesuaikan dengan nama kolom di Excel
            'customer_code' => $row['customer_code'],
            'part_no'       => $row['part_no'],
            'quantity'      => $row['qty'],
            'due_date'      => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['due_date']),
        ]);
    }
}