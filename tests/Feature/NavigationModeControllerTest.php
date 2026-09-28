<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationModeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_switch_to_employee_navigation_mode(): void
    {
        $administrator = User::factory()->create(['role' => 'admin']);

        $this->actingAs($administrator)
            ->from(route('dashboard'))
            ->patch(route('navigation-mode.update'), ['mode' => 'employee'])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('navigation_mode', 'employee');
    }

    public function test_employee_cannot_switch_navigation_mode(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)
            ->patch(route('navigation-mode.update'), ['mode' => 'employee'])
            ->assertForbidden();
    }
}
