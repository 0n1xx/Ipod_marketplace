<?php
// HTML bodies for transactional emails (contact inquiries + newsletter).
// Kept separate from Mailer so copy can change without touching the API wrapper.
class EmailTemplates {
    public static function contactInquiryAdmin(array $data): string {
        $rows = [
            'Name'    => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
            'Email'   => $data['email'] ?? '',
            'Phone'   => $data['phone'] ?? '',
            'Subject' => $data['subject_label'] ?? ($data['subject'] ?? ''),
            'Message' => $data['message'] ?? '',
        ];

        $details = '';
        foreach ($rows as $label => $value) {
            $details .= '<tr>'
                . '<td style="padding:8px 0;border-bottom:1px solid #eef2f6;width:120px;font-weight:700;vertical-align:top;">'
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                . '</td>'
                . '<td style="padding:8px 0;border-bottom:1px solid #eef2f6;vertical-align:top;white-space:pre-wrap;">'
                . nl2br(htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'))
                . '</td></tr>';
        }

        $body = '<p>A new message arrived from the contact form.</p>'
            . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:12px;">'
            . $details
            . '</table>'
            . '<p style="margin-top:18px;color:#5c5a66;">You can reply directly to this email to answer the customer.</p>';

        return Mailer::wrap('New contact inquiry', $body);
    }

    public static function contactInquiryCustomer(array $data, array $L): string {
        $name = htmlspecialchars(trim($data['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $subject = htmlspecialchars($data['subject_label'] ?? '', ENT_QUOTES, 'UTF-8');
        $message = nl2br(htmlspecialchars($data['message'] ?? '', ENT_QUOTES, 'UTF-8'));

        $body = '<p>' . htmlspecialchars($L['contactEmailCustomerIntro'], ENT_QUOTES, 'UTF-8') . '</p>'
            . ($name !== '' ? '<p><strong>' . htmlspecialchars($L['firstName'], ENT_QUOTES, 'UTF-8') . ':</strong> ' . $name . '</p>' : '')
            . '<p><strong>' . htmlspecialchars($L['subject'], ENT_QUOTES, 'UTF-8') . ':</strong> ' . $subject . '</p>'
            . '<p><strong>' . htmlspecialchars($L['message'], ENT_QUOTES, 'UTF-8') . ':</strong></p>'
            . '<div style="padding:14px 16px;background:#f5f8fc;border-radius:12px;border:1px solid #e6edf5;">' . $message . '</div>'
            . '<p style="margin-top:18px;">' . htmlspecialchars($L['contactEmailCustomerOutro'], ENT_QUOTES, 'UTF-8') . '</p>';

        return Mailer::wrap($L['contactEmailCustomerSubject'], $body);
    }

    public static function newsletterWelcome(array $L): string {
        $body = '<p>' . htmlspecialchars($L['newsletterEmailBody'], ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p style="margin-top:18px;">' . htmlspecialchars($L['newsletterEmailExtra'], ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p style="margin-top:22px;">'
            . '<a href="https://ipod-marketplace.up.railway.app/shop.php" '
            . 'style="display:inline-block;padding:12px 22px;border-radius:999px;background:#3575e2;color:#ffffff;text-decoration:none;font-weight:700;">'
            . htmlspecialchars($L['newsletterEmailCta'], ENT_QUOTES, 'UTF-8')
            . '</a></p>';

        return Mailer::wrap($L['newsletterEmailSubject'], $body);
    }
}
