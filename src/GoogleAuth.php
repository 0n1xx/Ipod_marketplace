<?php

/**
 * Minimal Google OAuth 2.0 helper (authorization code flow).
 * Needs GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET / APP_URL.
 */
class GoogleAuth
{
    private const AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USERINFO  = 'https://openidconnect.googleapis.com/v1/userinfo';

    public static function isConfigured(): bool
    {
        return GOOGLE_CLIENT_ID !== '' && GOOGLE_CLIENT_SECRET !== '';
    }

    public static function redirectUri(): string
    {
        return rtrim(APP_URL, '/') . '/auth-google-callback.php';
    }

    public static function startUrl(): string
    {
        $state = bin2hex(random_bytes(16));
        Session::set('google_oauth_state', $state);

        $params = http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'redirect_uri'  => self::redirectUri(),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ]);

        return self::AUTH_URL . '?' . $params;
    }

    public static function validateState(?string $state): bool
    {
        $expected = Session::get('google_oauth_state');
        Session::set('google_oauth_state', null);
        return is_string($state)
            && is_string($expected)
            && $expected !== ''
            && hash_equals($expected, $state);
    }

    /**
     * Exchange code for tokens, then fetch Google userinfo.
     * @return array{sub:string,email:string,name?:string,email_verified?:bool}|null
     */
    public static function fetchUser(string $code): ?array
    {
        $token = self::postForm(self::TOKEN_URL, [
            'code'          => $code,
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => self::redirectUri(),
            'grant_type'    => 'authorization_code',
        ]);

        if (!$token || empty($token['access_token'])) {
            return null;
        }

        $profile = self::getJson(self::USERINFO, $token['access_token']);
        if (!$profile || empty($profile['sub']) || empty($profile['email'])) {
            return null;
        }

        return $profile;
    }

    private static function postForm(string $url, array $fields): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($fields),
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_TIMEOUT        => 20,
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status >= 400) {
            return null;
        }

        $json = json_decode($body, true);
        return is_array($json) ? $json : null;
    }

    private static function getJson(string $url, string $accessToken): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            CURLOPT_TIMEOUT        => 20,
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status >= 400) {
            return null;
        }

        $json = json_decode($body, true);
        return is_array($json) ? $json : null;
    }
}
