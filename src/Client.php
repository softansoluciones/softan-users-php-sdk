<?php
namespace SoftanUsers;

final class Client
{
    /**
     * Perform an HTTP request to the Softan Users API.
     *
     * @param string      $endpoint        Relative endpoint (e.g. 'users', 'usersapps/42').
     * @param string      $method          HTTP method (GET, POST, PUT, DELETE).
     * @param array       $headers         Associative array of request headers.
     * @param array|null  $data            Request body (will be JSON-encoded).
     * @param string|null $baseUrlOverride Override the base URL from sdk_meta.json.
     * @param bool        $verifyTLS       Whether to verify TLS certificates.
     *
     * @return array Decoded JSON response, or ['error' => ..., 'detail' => ...] on failure.
     */
    public static function request(
        string  $endpoint,
        string  $method          = 'GET',
        array   $headers         = [],
        ?array  $data            = null,
        ?string $baseUrlOverride = null,
        bool    $verifyTLS       = true
    ): array {
        $base = $baseUrlOverride ?: Config::getBaseUrl();
        $url  = rtrim($base, '/') . '/' . ltrim($endpoint, '/');

        $httpHeaders = [];
        foreach ($headers as $k => $v) {
            $httpHeaders[] = $k . ': ' . $v;
        }

        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HTTPHEADER     => $httpHeaders,
            CURLOPT_TIMEOUT        => 20,
        ];

        if ($data !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        if (!$verifyTLS) {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = false;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int) (curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 0);
        curl_close($ch);

        if ($raw === false) {
            return ['error' => 'network_error', 'detail' => $err, 'http_status' => 0];
        }

        $json = json_decode((string) $raw, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }

        return ['error' => 'parse_error', 'http_status' => $code, 'raw' => $raw];
    }
}
