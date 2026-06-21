<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/load_env.php';
require_once __DIR__ . '/EmailFailureLogger.php';

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

class BrevoMailer
{
    private const DEFAULT_MAX_BODY_BYTES = 1048576;
    private const MAX_SUBJECT_LENGTH = 255;

    /**
     * @param array{
     *     to: array<int, array{email: string, name?: string}>,
     *     subject: string,
     *     htmlBody?: string|null,
     *     textBody?: string|null,
     *     replyTo?: array{email: string, name?: string}|null,
     *     fromEmail?: string|null,
     *     fromName?: string|null,
     *     context: string
     * } $options
     * @return array{success: bool, error: ?string, bodyBytes: int}
     */
    public static function send(array $options): array
    {
        $to = $options['to'] ?? [];
        $subject = trim($options['subject'] ?? '');
        $htmlBody = $options['htmlBody'] ?? null;
        $textBody = $options['textBody'] ?? null;
        $replyTo = $options['replyTo'] ?? null;
        $fromEmail = trim($options['fromEmail'] ?? self::env('MAIL_FROM_EMAIL', ''));
        $fromName = trim($options['fromName'] ?? self::env('MAIL_FROM_NAME', 'YARAMAY Website'));
        $context = $options['context'] ?? 'unknown';

        $body = $htmlBody ?? $textBody ?? '';
        $bodyBytes = strlen($body);

        if ($subject === '' || $body === '' || $to === []) {
            self::logFailure($context, $to, $subject, $htmlBody, $textBody, 'invalid_payload', $bodyBytes, $replyTo);
            return ['success' => false, 'error' => 'invalid_payload', 'bodyBytes' => $bodyBytes];
        }

        if (strlen($subject) > self::MAX_SUBJECT_LENGTH) {
            self::logFailure($context, $to, $subject, $htmlBody, $textBody, 'subject_too_long', $bodyBytes, $replyTo);
            return ['success' => false, 'error' => 'subject_too_long', 'bodyBytes' => $bodyBytes];
        }

        $maxBodyBytes = (int) self::env('MAIL_MAX_BODY_BYTES', (string) self::DEFAULT_MAX_BODY_BYTES);
        if ($maxBodyBytes <= 0) {
            $maxBodyBytes = self::DEFAULT_MAX_BODY_BYTES;
        }

        if ($bodyBytes > $maxBodyBytes) {
            self::logFailure($context, $to, $subject, $htmlBody, $textBody, 'body_too_large', $bodyBytes, $replyTo);
            return ['success' => false, 'error' => 'body_too_large', 'bodyBytes' => $bodyBytes];
        }

        foreach ($to as $recipient) {
            $email = trim($recipient['email'] ?? '');
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                self::logFailure($context, $to, $subject, $htmlBody, $textBody, 'invalid_recipient', $bodyBytes, $replyTo);
                return ['success' => false, 'error' => 'invalid_recipient', 'bodyBytes' => $bodyBytes];
            }
        }

        if ($replyTo !== null) {
            $replyEmail = trim($replyTo['email'] ?? '');
            if ($replyEmail !== '' && !filter_var($replyEmail, FILTER_VALIDATE_EMAIL)) {
                self::logFailure($context, $to, $subject, $htmlBody, $textBody, 'invalid_recipient', $bodyBytes, $replyTo);
                return ['success' => false, 'error' => 'invalid_recipient', 'bodyBytes' => $bodyBytes];
            }
        }

        $smtpHost = self::env('BREVO_SMTP_HOST', '');
        $smtpPort = (int) self::env('BREVO_SMTP_PORT', '587');
        $smtpLogin = self::env('BREVO_SMTP_LOGIN', '');
        $smtpPassword = self::env('BREVO_SMTP_PASSWORD', '');

        if ($smtpHost === '' || $smtpLogin === '' || $smtpPassword === '' || $fromEmail === '') {
            self::logFailure($context, $to, $subject, $htmlBody, $textBody, 'missing_config', $bodyBytes, $replyTo);
            return ['success' => false, 'error' => 'missing_config', 'bodyBytes' => $bodyBytes];
        }

        if ($fromEmail === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            self::logFailure($context, $to, $subject, $htmlBody, $textBody, 'invalid_sender', $bodyBytes, $replyTo);
            return ['success' => false, 'error' => 'invalid_sender', 'bodyBytes' => $bodyBytes];
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->Port = $smtpPort > 0 ? $smtpPort : 587;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpLogin;
            $mail->Password = $smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $mail->setFrom($fromEmail, $fromName);

            foreach ($to as $recipient) {
                $name = trim($recipient['name'] ?? '');
                $email = trim($recipient['email']);
                if ($name !== '') {
                    $mail->addAddress($email, $name);
                } else {
                    $mail->addAddress($email);
                }
            }

            if ($replyTo !== null && ($replyTo['email'] ?? '') !== '') {
                $replyName = trim($replyTo['name'] ?? '');
                $replyEmail = trim($replyTo['email']);
                if ($replyName !== '') {
                    $mail->addReplyTo($replyEmail, $replyName);
                } else {
                    $mail->addReplyTo($replyEmail);
                }
            }

            $mail->Subject = $subject;

            if ($htmlBody !== null) {
                $mail->isHTML(true);
                $mail->Body = $htmlBody;
                if ($textBody !== null) {
                    $mail->AltBody = $textBody;
                }
            } else {
                $mail->isHTML(false);
                $mail->Body = $textBody ?? '';
            }

            $mail->send();

            return ['success' => true, 'error' => null, 'bodyBytes' => $bodyBytes];
        } catch (PHPMailerException $e) {
            $errorInfo = isset($mail) ? trim($mail->ErrorInfo) : '';
            $error = $errorInfo !== '' ? $errorInfo : $e->getMessage();
            self::logFailure($context, $to, $subject, $htmlBody, $textBody, $error, $bodyBytes, $replyTo);
            return ['success' => false, 'error' => 'send_failed', 'bodyBytes' => $bodyBytes];
        }
    }

    /**
     * @return array<int, array{email: string, name: string}>
     */
    public static function adminRecipients(): array
    {
        $raw = self::env('MAIL_ADMIN_RECIPIENTS', '');
        $parts = array_filter(array_map('trim', explode(',', $raw)));

        $recipients = [];
        foreach ($parts as $part) {
            $recipients[] = ['email' => $part, 'name' => ''];
        }

        return $recipients;
    }

    private static function env(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        return trim((string) $value);
    }

    /**
     * @param array<int, array{email: string, name?: string}> $recipients
     * @param array{email: string, name?: string}|null $replyTo
     */
    private static function logFailure(
        string $context,
        array $recipients,
        string $subject,
        ?string $htmlBody,
        ?string $textBody,
        string $error,
        int $bodyBytes,
        ?array $replyTo
    ): void {
        EmailFailureLogger::log(
            $context,
            $recipients,
            $subject,
            $htmlBody,
            $textBody,
            $error,
            $bodyBytes,
            $replyTo
        );
    }
}
