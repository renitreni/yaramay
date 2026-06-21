<?php

declare(strict_types=1);

class EmailFailureLogger
{
    private const LOG_DIR = __DIR__ . '/../logs/email-failures';

    /**
     * @param array<int, array{email: string, name?: string}> $recipients
     * @param array{email: string, name?: string}|null $replyTo
     */
    public static function log(
        string $context,
        array $recipients,
        string $subject,
        ?string $htmlBody,
        ?string $textBody,
        string $error,
        int $bodyBytes,
        ?array $replyTo = null
    ): void {
        if (!is_dir(self::LOG_DIR) && !mkdir(self::LOG_DIR, 0755, true) && !is_dir(self::LOG_DIR)) {
            error_log('EmailFailureLogger: could not create log directory');
            return;
        }

        $timestamp = gmdate('Y-m-d\TH-i-s\Z');
        $safeContext = preg_replace('/[^a-z0-9_-]/i', '_', $context) ?: 'unknown';
        $filename = self::LOG_DIR . '/' . $timestamp . '_' . $safeContext . '.log';

        $recipientLines = [];
        foreach ($recipients as $recipient) {
            $name = trim($recipient['name'] ?? '');
            $email = trim($recipient['email'] ?? '');
            $recipientLines[] = $name !== '' ? "{$name} <{$email}>" : $email;
        }

        $replyToLine = '(none)';
        if ($replyTo !== null && ($replyTo['email'] ?? '') !== '') {
            $replyName = trim($replyTo['name'] ?? '');
            $replyEmail = trim($replyTo['email']);
            $replyToLine = $replyName !== '' ? "{$replyName} <{$replyEmail}>" : $replyEmail;
        }

        $body = $htmlBody ?? $textBody ?? '';
        $bodyType = $htmlBody !== null ? 'html' : 'text';

        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $content = implode("\n", [
            'timestamp=' . gmdate('c'),
            'context=' . $context,
            'recipients=' . implode(', ', $recipientLines),
            'subject=' . $subject,
            'reply_to=' . $replyToLine,
            'body_type=' . $bodyType,
            'body_bytes=' . $bodyBytes,
            'error=' . $error,
            'client_ip=' . $clientIp,
            'user_agent=' . $userAgent,
            '',
            '--- email body ---',
            $body,
            '--- end email body ---',
            '',
        ]);

        file_put_contents($filename, $content, LOCK_EX);
    }
}
