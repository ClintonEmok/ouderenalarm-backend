<?php

use App\Models\Device;
use App\Models\DeviceAlarm;
use App\Models\EmergencyLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can fetch emergency details for a valid link', function () {
    // Setup a user, device, and device alarm
    $user = User::factory()->create();
    $device = Device::factory()->create(['user_id' => $user->id]);
    $deviceAlarm = DeviceAlarm::factory()->create([
        'device_id' => $device->id,
        'fall_down_alert' => true,
        'sos_alert' => false,
    ]);

    $emergencyLink = EmergencyLink::create([
        'device_alarm_id' => $deviceAlarm->id,
        'link' => "https://your-nextjs-app.com/emergency/{$deviceAlarm->id}",
        'expires_at' => now()->addHours(24),
    ]);

    // Make a request to the endpoint
    $response = $this->getJson("/api/emergency/{$deviceAlarm->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'device' => ['id', 'imei', 'phone_number'],
            'triggered_at',
            'alerts' => ['fall_down_alert', 'sos_alert'],
            'user' => ['name', 'age', 'address'],
            'caregivers',
        ])
        ->assertJson([
            'device' => [
                'id' => $device->id,
                'imei' => $device->imei,
            ],
            'alerts' => [
                'fall_down_alert' => true,
                'sos_alert' => false,
            ],
        ]);
});

it('returns an error for an expired link', function () {
    // Setup a user, device, and device alarm
    $user = User::factory()->create();
    $device = Device::factory()->create(['user_id' => $user->id]);
    $deviceAlarm = DeviceAlarm::factory()->create([
        'device_id' => $device->id,
        'fall_down_alert' => true,
        'sos_alert' => false,
    ]);

    $emergencyLink = EmergencyLink::create([
        'device_alarm_id' => $deviceAlarm->id,
        'link' => "https://your-nextjs-app.com/emergency/{$deviceAlarm->id}",
        'expires_at' => now()->subMinute(), // Set to expired
    ]);

    // Make a request to the endpoint
    $response = $this->getJson("/api/emergency/{$deviceAlarm->id}");

    $response->assertStatus(404)
        ->assertJson(['error' => 'This link is expired or invalid.']);
});

it('returns an error for a non-existent link', function () {
    // Make a request with a non-existent ID
    $response = $this->getJson('/api/emergency/99999');

    $response->assertStatus(404)
        ->assertJson(['error' => 'This link is expired or invalid.']);
});
