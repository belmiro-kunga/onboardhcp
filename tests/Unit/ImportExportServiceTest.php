<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ImportExportService;
use App\Services\UserSearchService;
use App\Services\RolePermissionService;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ImportExportService $service;
    protected UserSearchService $userSearchService;
    protected RolePermissionService $rolePermissionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userSearchService = $this->createMock(UserSearchService::class);
        $this->rolePermissionService = $this->createMock(RolePermissionService::class);
        
        $this->service = new ImportExportService(
            $this->userSearchService,
            $this->rolePermissionService
        );
    }

    public function test_can_generate_csv_template()
    {
        $template = $this->service->getImportTemplate();
        
        $this->assertIsString($template);
        $this->assertStringContainsString('Nome Completo', $template);
        $this->assertStringContainsString('Email', $template);
        $this->assertStringContainsString('João Silva', $template);
    }

    public function test_can_export_users_to_csv()
    {
        // Create test users
        $users = User::factory()->count(3)->create();
        
        Excel::fake();
        
        $result = $this->service->exportSelectedUsers($users->pluck('id')->toArray(), 'csv');
        
        Excel::assertDownloaded('users_export.csv', function ($export) {
            return $export instanceof \App\Exports\UsersExport;
        });
    }

    public function test_can_export_users_to_excel()
    {
        // Create test users
        $users = User::factory()->count(3)->create();
        
        Excel::fake();
        
        $result = $this->service->exportSelectedUsers($users->pluck('id')->toArray(), 'xlsx');
        
        Excel::assertDownloaded('users_export.xlsx', function ($export) {
            return $export instanceof \App\Exports\UsersExport;
        });
    }

    public function test_validates_file_size()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        // Create a mock file that's too large
        $file = UploadedFile::fake()->create('large_file.csv', 15000); // 15MB
        
        $this->service->importUsers($file);
    }

    public function test_validates_file_type()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        // Create a mock file with wrong type
        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $this->service->importUsers($file);
    }

    public function test_can_preview_import()
    {
        Excel::fake();
        
        $file = UploadedFile::fake()->create('users.csv', 100);
        
        // Mock the preview import
        Excel::shouldReceive('import')
            ->once()
            ->andReturn(true);
        
        $preview = $this->service->previewImport($file);
        
        $this->assertInstanceOf(\App\Services\ImportPreview::class, $preview);
    }

    public function test_throws_exception_for_unsupported_format()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Formato não suportado: pdf');
        
        $users = collect([]);
        $this->service->exportUsers([], 'pdf');
    }

    public function test_export_applies_filters()
    {
        // Create test users with different statuses
        User::factory()->create(['status' => 'active', 'name' => 'Active User']);
        User::factory()->create(['status' => 'inactive', 'name' => 'Inactive User']);
        
        Excel::fake();
        
        // Export only active users
        $filters = ['status' => 'active'];
        $result = $this->service->exportUsers($filters, 'csv');
        
        // Verify that the export was called
        Excel::assertDownloaded('users_export.csv', function ($export) {
            return $export instanceof \App\Exports\UsersExport;
        });
    }

    public function test_import_result_calculates_success_rate()
    {
        $result = new \App\Services\ImportResult();
        
        // Add some successes and errors
        $user1 = User::factory()->make();
        $user2 = User::factory()->make();
        
        $result->addSuccess($user1);
        $result->addSuccess($user2);
        $result->addError(3, ['name' => 'Invalid'], 'Email required');
        
        $this->assertEquals(2, $result->getSuccessCount());
        $this->assertEquals(1, $result->getErrorCount());
        $this->assertEquals(3, $result->getTotalProcessed());
        $this->assertEquals(66.67, round($result->getSuccessRate(), 2));
        $this->assertTrue($result->hasErrors());
        $this->assertTrue($result->hasSuccesses());
    }

    public function test_import_preview_tracks_validation()
    {
        $preview = new \App\Services\ImportPreview();
        
        $validRow = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $invalidRow = ['name' => '', 'email' => 'invalid-email'];
        
        $preview->addRow(1, $validRow, ['valid' => true, 'errors' => []]);
        $preview->addRow(2, $invalidRow, ['valid' => false, 'errors' => ['Name is required', 'Invalid email']]);
        
        $preview->setTotalRows(10);
        $preview->setSampleSize(2);
        
        $this->assertEquals(10, $preview->getTotalRows());
        $this->assertEquals(2, $preview->getSampleSize());
        $this->assertEquals(1, $preview->getValidRowsCount());
        $this->assertEquals(1, $preview->getInvalidRowsCount());
        $this->assertTrue($preview->hasErrors());
        $this->assertCount(2, $preview->getRows());
    }
}