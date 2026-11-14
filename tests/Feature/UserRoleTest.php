<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_is_not_mass_assignable(): void
    {
        $this->assertFalse((new User)->isFillable('role'));
    }

    public function test_mass_assigning_role_does_not_make_user_owner(): void
    {
        $user = new User([
            'name' => 'Pengguna Uji',
            'email' => 'uji@example.test',
            'password' => 'Sandi-Uji-12345',
            'role' => User::ROLE_PEMILIK,
        ]);

        $this->assertSame(User::ROLE_PENGHUNI, $user->role);
        $this->assertFalse($user->isPemilik());
        $this->assertTrue($user->isPenghuni());
    }

    public function test_new_user_defaults_to_penghuni_in_database(): void
    {
        $user = User::factory()->create();

        $this->assertSame(
            User::ROLE_PENGHUNI,
            $user->fresh()->role
        );
        $this->assertTrue($user->isPenghuni());
        $this->assertFalse($user->isPemilik());
    }

    public function test_role_can_be_set_explicitly(): void
    {
        $user = User::factory()->create();

        $user->forceFill([
            'role' => User::ROLE_PEMILIK,
        ])->save();

        $this->assertTrue($user->fresh()->isPemilik());
    }
}
