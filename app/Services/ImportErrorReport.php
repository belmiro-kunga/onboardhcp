<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ImportErrorReport
{
    protected array $errors = [];
    protected array $warnings = [];
    protected array $summary = [];

    public function addError(int $row, string $field, string $message, $value = null): void
    {
        $this->errors[] = [
            'row' => $row,
            'field' => $field,
            'message' => $message,
            'value' => $value,
            'type' => 'error',
            'timestamp' => now()->toISOString()
        ];
    }

    public function addWarning(int $row, string $field, string $message, $value = null): void
    {
        $this->warnings[] = [
            'row' => $row,
            'field' => $field,
            'message' => $message,
            'value' => $value,
            'type' => 'warning',
            'timestamp' => now()->toISOString()
        ];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getAllIssues(): array
    {
        return array_merge($this->errors, $this->warnings);
    }

    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    public function getWarningCount(): int
    {
        return count($this->warnings);
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    public function getErrorsByField(): array
    {
        $grouped = [];
        foreach ($this->errors as $error) {
            $field = $error['field'];
            if (!isset($grouped[$field])) {
                $grouped[$field] = [];
            }
            $grouped[$field][] = $error;
        }
        return $grouped;
    }

    public function getErrorsByRow(): array
    {
        $grouped = [];
        foreach ($this->errors as $error) {
            $row = $error['row'];
            if (!isset($grouped[$row])) {
                $grouped[$row] = [];
            }
            $grouped[$row][] = $error;
        }
        return $grouped;
    }

    public function generateSummary(): array
    {
        $fieldErrors = $this->getErrorsByField();
        $mostCommonErrors = [];
        
        foreach ($fieldErrors as $field => $errors) {
            $mostCommonErrors[$field] = count($errors);
        }
        
        arsort($mostCommonErrors);

        return [
            'total_errors' => $this->getErrorCount(),
            'total_warnings' => $this->getWarningCount(),
            'affected_rows' => count($this->getErrorsByRow()),
            'most_common_errors' => array_slice($mostCommonErrors, 0, 5, true),
            'error_rate' => $this->calculateErrorRate(),
            'recommendations' => $this->generateRecommendations()
        ];
    }

    protected function calculateErrorRate(): float
    {
        // This would need the total row count to be meaningful
        // For now, return a placeholder
        return 0.0;
    }

    protected function generateRecommendations(): array
    {
        $recommendations = [];
        $fieldErrors = $this->getErrorsByField();

        if (isset($fieldErrors['email']) && count($fieldErrors['email']) > 0) {
            $recommendations[] = 'Verifique se todos os emails estão no formato correto (exemplo@dominio.com)';
        }

        if (isset($fieldErrors['name']) && count($fieldErrors['name']) > 0) {
            $recommendations[] = 'Certifique-se de que todos os utilizadores têm nome preenchido';
        }

        if (isset($fieldErrors['role']) && count($fieldErrors['role']) > 0) {
            $recommendations[] = 'Verifique se as roles especificadas existem no sistema (admin, funcionario)';
        }

        if (isset($fieldErrors['hire_date']) && count($fieldErrors['hire_date']) > 0) {
            $recommendations[] = 'Use o formato YYYY-MM-DD para as datas de admissão';
        }

        return $recommendations;
    }

    public function exportToArray(): array
    {
        return [
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'summary' => $this->generateSummary(),
            'generated_at' => now()->toISOString()
        ];
    }

    public function exportToCsv(): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for proper UTF-8 encoding
        fwrite($output, "\xEF\xBB\xBF");
        
        // Headers
        fputcsv($output, ['Linha', 'Campo', 'Tipo', 'Mensagem', 'Valor', 'Data/Hora']);
        
        // Add all issues
        foreach ($this->getAllIssues() as $issue) {
            fputcsv($output, [
                $issue['row'],
                $issue['field'],
                $issue['type'] === 'error' ? 'Erro' : 'Aviso',
                $issue['message'],
                $issue['value'] ?? '',
                $issue['timestamp']
            ]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}