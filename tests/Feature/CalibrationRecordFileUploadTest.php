<?php

namespace Tests\Feature;

use App\Models\CalibrationRecord;
use App\Models\Company;
use App\Models\Department;
use App\Models\Gage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CalibrationRecordFileUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Company $company;
    private Department $department;
    private Gage $gage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'admin',
        ]);
        $this->department = Department::factory()->create([
            'company_id' => $this->company->id,
        ]);
        $this->gage = Gage::factory()->create([
            'department_id' => $this->department->id,
        ]);
    }

    public function test_user_can_upload_certificate_when_creating_calibration_record(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('certificate.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post(route('calibration-records.store'), [
                'gage_id' => $this->gage->id,
                'measured_value' => 10.0000,
                'calibrated_value' => 10.0050,
                'calibrated_at' => now()->format('Y-m-d\TH:i'),
                'cert_file' => $file,
            ]);

        $response->assertRedirect();

        $record = CalibrationRecord::latest()->first();
        $this->assertNotNull($record->cert_file);
        $this->assertTrue($record->hasCertificate());

        Storage::disk('public')->assertExists($record->cert_file);
    }

    public function test_user_can_update_certificate(): void
    {
        Storage::fake('public');

        $record = CalibrationRecord::factory()->create([
            'gage_id' => $this->gage->id,
            'user_id' => $this->user->id,
        ]);

        $file = UploadedFile::fake()->create('new_certificate.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->put(route('calibration-records.update', $record), [
                'gage_id' => $record->gage_id,
                'measured_value' => $record->measured_value,
                'calibrated_value' => $record->calibrated_value,
                'calibrated_at' => $record->calibrated_at->format('Y-m-d\TH:i'),
                'cert_file' => $file,
            ]);

        $response->assertRedirect();

        $record->refresh();
        $this->assertNotNull($record->cert_file);
        $this->assertTrue($record->hasCertificate());

        Storage::disk('public')->assertExists($record->cert_file);
    }

    public function test_certificate_file_validation(): void
    {
        $invalidFile = UploadedFile::fake()->create('document.txt', 1024, 'text/plain');

        $response = $this->actingAs($this->user)
            ->post(route('calibration-records.store'), [
                'gage_id' => $this->gage->id,
                'measured_value' => 10.0000,
                'calibrated_value' => 10.0050,
                'calibrated_at' => now()->format('Y-m-d\TH:i'),
                'cert_file' => $invalidFile,
            ]);

        $response->assertSessionHasErrors(['cert_file']);
    }

    public function test_user_can_download_certificate(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('certificate.pdf', 1024, 'application/pdf');
        Storage::disk('public')->put('test-certificate.pdf', $file->getContent());

        $record = CalibrationRecord::factory()->create([
            'gage_id' => $this->gage->id,
            'user_id' => $this->user->id,
            'cert_file' => 'test-certificate.pdf',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('calibration-records.download-certificate', $record));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
