<?php

namespace Tests\Feature;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class STPRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_stp_can_register()
    {
        $response = $this->post('/register', [
            'institute_name' => 'Test Training Institute',
            'institute_code' => 'STP001',
            'mobile' => '01712345678',
            'admin_name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('institutes', [
            'name' => 'Test Training Institute',
            'code' => 'STP001',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $institute = Institute::where('code', 'STP001')->first();
        $user = User::where('email', 'admin@test.com')->first();

        $this->assertEquals($institute->id, $user->institute_id);
        $this->assertAuthenticated();
    }

    public function test_stp_cannot_register_with_duplicate_code()
    {
        Institute::create([
            'name' => 'Existing',
            'code' => 'STP001',
            'mobile' => '01712345678',
            'email' => 'existing@test.com',
        ]);

        $response = $this->post('/register', [
            'institute_name' => 'New Institute',
            'institute_code' => 'STP001',
            'mobile' => '01712345679',
            'admin_name' => 'Admin',
            'email' => 'admin@new.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('institute_code');
    }

    public function test_register_page_has_institute_fields()
    {
        $response = $this->get('/register');

        $response->assertSee('Institute Name');
        $response->assertSee('Institute Code');
        $response->assertSee('Mobile Number');
        $response->assertSee('Admin Name');
    }
}
