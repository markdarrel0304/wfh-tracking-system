<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\WorkScheduleAssigned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_opening_a_notification_marks_it_as_read_and_follows_its_link(): void
    {
        $user = User::factory()->create();
        $user->notify(new WorkScheduleAssigned('Regular', 'Oct 1, 2026'));
        $notification = $user->notifications()->sole();

        $this->actingAs($user)
            ->get(route('notifications.show', $notification))
            ->assertRedirect(route('my-work-schedule'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_open_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $owner->notify(new WorkScheduleAssigned('Regular'));

        $this->actingAs($otherUser)
            ->get(route('notifications.show', $owner->notifications()->sole()))
            ->assertNotFound();
    }
}
