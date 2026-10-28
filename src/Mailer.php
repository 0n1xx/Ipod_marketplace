<?php
// Thin wrapper around the Resend HTTP API (no SDK, same pattern already
// used for Cloudinary elsewhere in this project)
class Mailer {
    public static function send(
        string $to,
        string $subject,
        string $html,
        ?string $replyTo = null
    ): bool {
        if (empty(RESEND_API_KEY)) {
            error_log("Mailer: RESEND_API_KEY is not set, skipping email to {$to}");
            return false;
        }

        $payload = [
            'from'    => RESEND_FROM_EMAIL,
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $html,
        ];
        if ($replyTo !== null && $replyTo !== '') {
            $payload['reply_to'] = $replyTo;
        }

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
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

    // Shared branded HTML shell so every transactional email looks the same
    public static function wrap(string $title, string $bodyHtml): string {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>{$safeTitle}</title></head>
<body style="margin:0;padding:0;background:#f1f7fd;font-family:Arial,Helvetica,sans-serif;color:#1C1A22;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f1f7fd;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(28,26,34,0.08);">
          <tr>
            <td style="padding:24px 28px;background:linear-gradient(135deg,#3575e2,#00c2cb);color:#ffffff;">
              <div style="font-size:13px;letter-spacing:0.12em;text-transform:uppercase;opacity:0.9;">QuébecIpod</div>
              <div style="font-size:24px;font-weight:700;margin-top:6px;">{$safeTitle}</div>
            </td>
          </tr>
          <tr>
            <td style="padding:28px;font-size:15px;line-height:1.6;">
              {$bodyHtml}
            </td>
          </tr>
          <tr>
            <td style="padding:18px 28px;background:#0f0f1a;color:#cccccc;font-size:12px;line-height:1.5;">
              QuébecIpod · Montréal, Québec<br>
              Preserving the joy of personal music.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }
}
