<?php

namespace App\Imports;

use App\Services\ImportPreview;
use App\Services\DataTransformationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersPreviewImport implements ToCollection, WithHeadingRow
{
    protected ImportPreview $preview;
    protected DataTransformationService $transformationService;

    public function __construct()
    {
        $this->preview = new ImportPreview();
        $this->transformationService = new DataTransformationService();
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $totalRows = $collection->count();
        $sampleSize = min(10, $totalRows); // Show first 10 rows
        $sample = $collection->take($sampleSize);
        
        foreach ($sample as $index => $row) {
            $rowNumber = $index + 2; // +2 because of header row and 0-based index
            $rowData = $row->toArray();
            $validation = $this->validateRowForPreview($rowData, $rowNumber);
            
            $this->preview->addRow($rowNumber, $rowData, $validation);
        }
        
        $this->preview->setTotalRows($totalRows);
        $this->preview->setSampleSize($sampleSize);
    }

    /**
     * Validate row for preview (non-blocking validation)
     */
    protected function validateRowForPreview(array $row, int $rowNumber): array
    {
        // Transform data using the transformation service
        $transformedRow = $this->transformationService->transformRow($row);
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'role' => 'nullable|string|exists:roles,name',
            'status' => 'nullable|in:active,inactive,pending,blocked,suspended'
        ];

        $validator = Validator::make($transformedRow, $rules);
        
        return [
            'valid' => !$validator->fails(),
            'errors' => $validator->errors()->all(),
            'transformed_data' => $transformedRow,
            'original_data' => $row
        ];
    }

    /**
     * Get the preview object
     */
    public function getPreview(): ImportPreview
    {
        return $this->preview;
    }
}