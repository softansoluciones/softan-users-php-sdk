<?php
namespace SoftanUsers;

final class Headers
{
    /**
     * Build the runtime headers for Softan Users API requests.
     * Sends X-API-KEY + X-App-Id (internal/write access).
     */
    public static function buildRuntimeHeaders(?string $env = null): array
    {
        $apiKey = Config::getApiKey($env);
        $appId  = Config::getAppId();

        if ($apiKey === '') {
            throw new \RuntimeException(
                'SoftanUsers: api_key is not configured. Run bin/install.php or set up sdk_config.json.'
            );
        }

        $headers = [
            'Content-Type' => 'application/json',
            'X-API-KEY'    => $apiKey,
        ];

        if ($appId !== '') {
            $headers['X-App-Id'] = $appId;
        }

        return $headers;
    }

    /**
     * Build headers for public (read-only) access — only X-API-KEY, no X-App-Id.
     */
    public static function buildPublicHeaders(?string $env = null): array
    {
        $apiKey = Config::getApiKey($env);

        if ($apiKey === '') {
            throw new \RuntimeException(
                'SoftanUsers: api_key is not configured. Run bin/install.php or set up sdk_config.json.'
            );
        }

        return [
            'Content-Type' => 'application/json',
            'X-API-KEY'    => $apiKey,
        ];
    }
}
