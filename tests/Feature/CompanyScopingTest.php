<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_only_see_their_company_departments(): void
    {
        // Create two companies with departments
        $company1 = Company::factory()->create(['name' => 'Company 1']);
        $company2 = Company::factory()->create(['name' => 'Company 2']);

        $user1 = User::factory()->create(['company_id' => $company1->id]);
        $user2 = User::factory()->create(['company_id' => $company2->id]);

        $department1 = Department::factory()->create([
            'company_id' => $company1->id,
            'name' => 'Company 1 Department'
        ]);
        
        $department2 = Department::factory()->create([
            'company_id' => $company2->id,
            'name' => 'Company 2 Department'
        ]);

        // User 1 should only see Company 1's departments
        $response = $this->actingAs($user1)->get('/departments');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Departments/Index')
                 ->has('departments', 1)
                 ->where('departments.0.name', 'Company 1 Department')
        );

        // User 2 should only see Company 2's departments
        $response = $this->actingAs($user2)->get('/departments');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Departments/Index')
                 ->has('departments', 1)
                 ->where('departments.0.name', 'Company 2 Department')
        );
    }

    public function test_users_cannot_access_other_company_departments(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $user1 = User::factory()->create(['company_id' => $company1->id]);
        
        $department2 = Department::factory()->create(['company_id' => $company2->id]);

        // User 1 should not be able to access Company 2's department
        $response = $this->actingAs($user1)->get("/departments/{$department2->id}");
        $response->assertStatus(403);
    }

    public function test_department_creation_is_scoped_to_company(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/departments', [
            'name' => 'Test Department',
            'manager_name' => 'Test Manager',
            'manager_email' => 'manager@test.com',
        ]);

        $response->assertRedirect('/departments');
        
        $department = Department::first();
        $this->assertEquals($user->company_id, $department->company_id);
        $this->assertEquals('Test Department', $department->name);
    }
}
