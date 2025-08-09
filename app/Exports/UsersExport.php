<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles
{
    protected Collection $users;

    public function __construct(Collection $users)
    {
        $this->users = $users;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->users;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nome',
            'Email',
            'Telefone',
            'Departamento',
            'Cargo',
            'Data de Admissão',
            'Status',
            'Roles',
            'Último Acesso',
            'Data de Criação'
        ];
    }

    /**
     * @param mixed $user
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone ?? '',
            $user->department ?? '',
            $user->position ?? '',
            $user->hire_date ? $user->hire_date->format('Y-m-d') : '',
            $this->getStatusLabel($user->status ?? 'active'),
            $user->roles ? $user->roles->pluck('name')->implode(', ') : '',
            $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Nunca',
            $user->created_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_DATE_YYYYMMDD, // Data de Admissão
            'J' => NumberFormat::FORMAT_DATE_DATETIME, // Último Acesso
            'K' => NumberFormat::FORMAT_DATE_DATETIME, // Data de Criação
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Auto-size columns
            'A:K' => ['alignment' => ['wrapText' => true]],
        ];
    }

    /**
     * Get status label in Portuguese
     */
    protected function getStatusLabel(string $status): string
    {
        $labels = [
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'pending' => 'Pendente',
            'blocked' => 'Bloqueado',
            'suspended' => 'Suspenso'
        ];

        return $labels[$status] ?? $status;
    }
}