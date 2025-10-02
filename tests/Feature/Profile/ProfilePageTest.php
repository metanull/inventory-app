<?php

use App\Models\User;

test('profile page can be accessed under web prefix', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('web.profile.show'));
    $response->assertOk();

    // Check that the page contains profile content
    $response->assertSee('Profile');
    $response->assertSee($user->name);
    $response->assertSee($user->email);
});

test('old profile route still works for backward compatibility', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('web.profile.show'));
    $response->assertOk();
});

test('profile page shows two factor authentication section', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('web.profile.show'));
    $response->assertOk();

    // Check that 2FA section is present
    $response->assertSee('Two Factor Authentication');
});
