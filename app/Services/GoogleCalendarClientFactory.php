<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Calendar;
use RuntimeException;

class GoogleCalendarClientFactory
{
    public function make(): GoogleCalendarGateway
    {
        return new GoogleCalendarGateway(new Calendar($this->googleClient()));
    }

    public function usesServiceAccount(): bool
    {
        $credentials = $this->loadCredentials();

        return $credentials !== null && ($credentials['type'] ?? null) === 'service_account';
    }

    public function hasValidAuthentication(): bool
    {
        $credentials = $this->loadCredentials();

        if ($credentials !== null && ($credentials['type'] ?? null) === 'service_account') {
            return filled($credentials['client_email'] ?? null)
                && filled($credentials['private_key'] ?? null);
        }

        if ($credentials !== null && (isset($credentials['web']) || isset($credentials['installed']))) {
            return filled(config('services.google.calendar_refresh_token'))
                && ! str_starts_with((string) config('services.google.calendar_refresh_token'), 'ya29.');
        }

        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.calendar_refresh_token'))
            && ! str_starts_with((string) config('services.google.calendar_refresh_token'), 'ya29.');
    }

    public function credentialsPath(): string
    {
        $path = (string) config('services.google.calendar_credentials');

        if ($path === '') {
            return '';
        }

        if (! str_starts_with($path, '/') && ! preg_match('/^[A-Za-z]:[\\\\\\/]/', $path)) {
            return base_path($path);
        }

        return $path;
    }

    private function googleClient(): GoogleClient
    {
        $client = new GoogleClient;
        $client->setApplicationName(config('app.name'));
        $client->setScopes([Calendar::CALENDAR]);

        $credentials = $this->loadCredentials();

        if ($credentials !== null && ($credentials['type'] ?? null) === 'service_account') {
            $client->setAuthConfig($credentials);

            return $client;
        }

        $refreshToken = (string) config('services.google.calendar_refresh_token');

        if ($refreshToken === '') {
            throw new RuntimeException('Google Calendar refresh token is required for OAuth credentials.');
        }

        if (str_starts_with($refreshToken, 'ya29.')) {
            throw new RuntimeException('GOOGLE_CALENDAR_REFRESH_TOKEN appears to be an access token (starts with ya29.). Use the refresh token instead — it usually starts with 1//.');
        }

        $oauthConfig = $credentials['web'] ?? $credentials['installed'] ?? null;

        if ($oauthConfig !== null) {
            $client->setAuthConfig($credentials);
        } else {
            $client->setClientId((string) config('services.google.client_id'));
            $client->setClientSecret((string) config('services.google.client_secret'));
        }

        $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);

        if (isset($token['error'])) {
            $error = (string) $token['error'];
            $description = (string) ($token['error_description'] ?? $error);

            throw new RuntimeException("Google Calendar token exchange failed [{$error}]: {$description}");
        }

        if (! is_array($client->getAccessToken())) {
            throw new RuntimeException('Google Calendar token exchange did not return an access token.');
        }

        return $client;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadCredentials(): ?array
    {
        $path = $this->credentialsPath();

        if ($path === '' || ! is_readable($path)) {
            return null;
        }

        $credentials = json_decode((string) file_get_contents($path), true);

        return is_array($credentials) ? $credentials : null;
    }
}
