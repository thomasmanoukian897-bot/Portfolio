<?php

use App\Services\GoogleCalendarClientFactory;
use Tests\TestCase;

uses(TestCase::class);

test('credentials path resolves relative paths from application base path', function () {
    config(['services.google.calendar_credentials' => 'storage/test-credentials.json']);

    expect(app(GoogleCalendarClientFactory::class)->credentialsPath())
        ->toBe(base_path('storage/test-credentials.json'));
});

test('credentials path keeps absolute paths unchanged', function () {
    $absolutePath = base_path('storage/google-calendar-credentials.json');

    config(['services.google.calendar_credentials' => $absolutePath]);

    expect(app(GoogleCalendarClientFactory::class)->credentialsPath())
        ->toBe($absolutePath);
});

test('has valid authentication returns false when credentials file is missing', function () {
    config(['services.google.calendar_credentials' => 'storage/missing-credentials.json']);

    expect(app(GoogleCalendarClientFactory::class)->hasValidAuthentication())->toBeFalse();
});

test('has valid authentication returns true for service account credentials', function () {
    $path = tempnam(sys_get_temp_dir(), 'google-sa-');
    file_put_contents($path, json_encode([
        'type' => 'service_account',
        'client_email' => 'calendar@project.iam.gserviceaccount.com',
        'private_key' => "-----BEGIN PRIVATE KEY-----\ntest\n-----END PRIVATE KEY-----\n",
    ]));

    config(['services.google.calendar_credentials' => $path]);

    expect(app(GoogleCalendarClientFactory::class)->hasValidAuthentication())->toBeTrue();
    expect(app(GoogleCalendarClientFactory::class)->usesServiceAccount())->toBeTrue();

    unlink($path);
});

test('has valid authentication returns true for oauth credentials with refresh token', function () {
    $path = tempnam(sys_get_temp_dir(), 'google-oauth-');
    file_put_contents($path, json_encode([
        'web' => [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
        ],
    ]));

    config([
        'services.google.calendar_credentials' => $path,
        'services.google.calendar_refresh_token' => 'test-refresh-token',
    ]);

    $factory = app(GoogleCalendarClientFactory::class);

    expect($factory->hasValidAuthentication())->toBeTrue();
    expect($factory->usesServiceAccount())->toBeFalse();

    unlink($path);
});

test('has valid authentication returns false for oauth credentials without refresh token', function () {
    $path = tempnam(sys_get_temp_dir(), 'google-oauth-');
    file_put_contents($path, json_encode([
        'web' => [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
        ],
    ]));

    config([
        'services.google.calendar_credentials' => $path,
        'services.google.calendar_refresh_token' => null,
    ]);

    expect(app(GoogleCalendarClientFactory::class)->hasValidAuthentication())->toBeFalse();

    unlink($path);
});

test('has valid authentication returns false when refresh token is an access token', function () {
    config([
        'services.google.calendar_credentials' => 'storage/missing-credentials.json',
        'services.google.client_id' => 'test-client-id',
        'services.google.client_secret' => 'test-client-secret',
        'services.google.calendar_refresh_token' => 'ya29.access-token-not-refresh-token',
    ]);

    expect(app(GoogleCalendarClientFactory::class)->hasValidAuthentication())->toBeFalse();
});
