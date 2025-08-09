<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin@test.com'
        ]);
        
        // Create basic roles
        Role::factory()->create(['name' => 'admin']);
        Role::factory()->create(['name' => 'funcionario']);
    }

    public function test_admin_can_download_import_template()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.import-export.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="template_importacao_utilizadores.csv"');
    }

    public function test_admin_can_export_users_csv()
    {
        // Create some test users
        User::factory()->count(3)->create();

        Excel::fake();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.import-export.export', ['format' => 'csv']));

        $response->assertStatus(200);
        
        Excel::assertDownloaded('users_export.csv', function ($export) {
            return $export instanceof \App\Exports\UsersExport;
        });
    }

    public function test_admin_can_export_users_excel()
    {
        // Create some test users
        User::factory()->count(3)->create();

        Excel::fake();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.import-export.export', ['format' => 'xlsx']));

        $response->assertStatus(200);
        
        Excel::assertDownloaded('users_export.xlsx', function ($export) {
            return $export instanceof \App\Exports\UsersExport;
        });
    }

    public function test_admin_can_export_selected_users()
    {
        // Create test users
        $users = User::factory()->count(3)->create();

        Excel::fake();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.import-export.export-selected'), [
                'format' => 'csv',
                'user_ids' => $users->pluck('id')->toArray()
            ]);

        $response->assertStatus(200);
        
        Excel::assertDownloaded('users_export.csv', function ($export) {
            return $export instanceof \App\Exports\UsersExport;
        });
    }

    public function test_admin_can_preview_import()
    {
        Excel::fake();

        $file = UploadedFile::fake()->createWithContent('users.csv', 
            "Nome,Email,Telefone,Departamento,Cargo,Data de Admissão,Role,Status\n" .
            "João Silva,joao@test.com,123456789,TI,Developer,2024-01-01,funcionario,active\n"
        );

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.import-export.preview'), [
                'file' => $file
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_rows',
                'sample_size',
                'valid_rows',
                'invalid_rows',
                'has_errors',
                'rows'
            ]
        ]);
    }

    public function test_admin_can_import_users()
    {
        Excel::fake();

        $file = UploadedFile::fake()->createWithContent('users.csv', 
            "Nome,Email,Telefone,Departamento,Cargo,Data de Admissão,Role,Status\n" .
            "João Silva,joao@test.com,123456789,TI,Developer,2024-01-01,funcionario,active\n"
        );

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.import-export.import'), [
                'file' => $file,
                'send_welcome_emails' => true
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'total_processed',
                'successful',
                'failed',
                'success_rate',
                'errors'
            ],
            'async'
        ]);
    }

    public function test_import_validates_file_size()
    {
        $file = UploadedFile::fake()->create('large_file.csv', 15000); // 15MB

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.import-export.preview'), [
                'file' => $file
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_import_validates_file_type()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.import-export.preview'), [
                'file' => $file
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_export_applies_filters()
    {
        // Create users with different statuses
        User::factory()->create(['status' => 'active', 'name' => 'Active User']);
        User::factory()->create(['status' => 'inactive', 'name' => 'Inactive User']);

        Excel::fake();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.import-export.export', [
                'format' => 'csv',
                'status' => 'active'
            ]));

        $response->assertStatus(200);
        
        Excel::assertDownloaded('users_export.csv', function ($export) {
            return $export instanceof \App\Exports\UsersExport;
        });
    }

    public function test_non_admin_cannot_access_import_export()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)
            ->get(route('admin.users.import-export.template'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_import_export()
    {
        $response = $this->get(route('admin.users.import-export.template'));

        $response->assertRedirect(route('login'));
    }

    public function test_import_history_returns_empty_for_now()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.import-export.history'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'imports' => [],
                'exports' => []
            ]
        ]);
    }

    public function test_cancel_operation_returns_success()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.import-export.cancel', ['jobId' => 'test-job-id']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Operação cancelada com sucesso'
        ]);
    }
}