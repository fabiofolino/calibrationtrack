<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Gage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->company = Company::factory()->create();
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        $this->department = Department::factory()->create(['company_id' => $this->company->id]);
        
        // Other company for testing scoping
        $this->otherCompany = Company::factory()->create();
        $this->otherUser = User::factory()->create(['company_id' => $this->otherCompany->id]);
        $this->otherDepartment = Department::factory()->create(['company_id' => $this->otherCompany->id]);
    }

    public function test_users_can_view_their_company_gages(): void
    {
        $gage = Gage::factory()->create(['department_id' => $this->department->id]);
        
        $response = $this->actingAs($this->user)
            ->get(route('gages.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->has('gages', 1)
                ->where('gages.0.id', $gage->id)
        );
    }

    public function test_users_cannot_see_other_company_gages(): void
    {
        Gage::factory()->create(['department_id' => $this->otherDepartment->id]);
        
        $response = $this->actingAs($this->user)
            ->get(route('gages.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->has('gages', 0)
        );
    }

    public function test_users_can_create_gages_in_their_company(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('gages.store'), [
                'department_id' => $this->department->id,
                'name' => 'Test Gage',
                'serial_number' => 'TG001',
                'frequency_days' => 365,
            ]);

        $response->assertRedirect(route('gages.index'));
        $this->assertDatabaseHas('gages', [
            'name' => 'Test Gage',
            'serial_number' => 'TG001',
            'department_id' => $this->department->id,
        ]);
    }

    public function test_users_cannot_create_gages_in_other_company_departments(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('gages.store'), [
                'department_id' => $this->otherDepartment->id,
                'name' => 'Test Gage',
                'serial_number' => 'TG001',
                'frequency_days' => 365,
            ]);

        $response->assertStatus(404); // Department not found in user's company
    }

    public function test_users_can_view_their_company_gage(): void
    {
        $gage = Gage::factory()->create(['department_id' => $this->department->id]);
        
        $response = $this->actingAs($this->user)
            ->get(route('gages.show', $gage));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->where('gage.id', $gage->id)
        );
    }

    public function test_users_cannot_view_other_company_gages(): void
    {
        $gage = Gage::factory()->create(['department_id' => $this->otherDepartment->id]);
        
        $response = $this->actingAs($this->user)
            ->get(route('gages.show', $gage));

        $response->assertForbidden();
    }

    public function test_users_can_update_their_company_gages(): void
    {
        $gage = Gage::factory()->create(['department_id' => $this->department->id]);
        
        $response = $this->actingAs($this->user)
            ->put(route('gages.update', $gage), [
                'department_id' => $this->department->id,
                'name' => 'Updated Gage',
                'serial_number' => $gage->serial_number,
                'frequency_days' => 180,
            ]);

        $response->assertRedirect(route('gages.index'));
        $this->assertDatabaseHas('gages', [
            'id' => $gage->id,
            'name' => 'Updated Gage',
            'frequency_days' => 180,
        ]);
    }

    public function test_users_cannot_update_other_company_gages(): void
    {
        $gage = Gage::factory()->create(['department_id' => $this->otherDepartment->id]);
        
        $response = $this->actingAs($this->user)
            ->put(route('gages.update', $gage), [
                'department_id' => $this->otherDepartment->id,
                'name' => 'Updated Gage',
                'serial_number' => $gage->serial_number,
                'frequency_days' => 180,
            ]);

        $response->assertForbidden();
    }

    public function test_users_can_delete_their_company_gages(): void
    {
        $gage = Gage::factory()->create(['department_id' => $this->department->id]);
        
        $response = $this->actingAs($this->user)
            ->delete(route('gages.destroy', $gage));

        $response->assertRedirect(route('gages.index'));
        $this->assertDatabaseMissing('gages', ['id' => $gage->id]);
    }

    public function test_users_cannot_delete_other_company_gages(): void
    {
        $gage = Gage::factory()->create(['department_id' => $this->otherDepartment->id]);
        
        $response = $this->actingAs($this->user)
            ->delete(route('gages.destroy', $gage));

        $response->assertForbidden();
        $this->assertDatabaseHas('gages', ['id' => $gage->id]);
    }
}
