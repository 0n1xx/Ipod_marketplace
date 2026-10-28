<?php
// Thin wrapper around the Resend HTTP API (no SDK, same pattern already
// used for Cloudinary elsewhere in this project)
class Mailer {
    public static function send(string $to, string $subject, string $html): bool {
        if (empty(RESEND_API_KEY)) {
            error_log("Mailer: RESEND_API_KEY is not set, skipping email to {$to}");
            return false;
        }

        $payload = json_encode([
            'from'    => RESEND_FROM_EMAIL,
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $html,
        ]);

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . RESEND_API_KEY,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $status >= 300) {
            error_log("Mailer: Resend send to {$to} failed (HTTP {$status}): " . ($curlError ?: $response));
            return false;
        }
        return true;
    }
}
