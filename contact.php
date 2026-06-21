<?php

require_once __DIR__ . '/includes/BrevoMailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html#contact');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    header('Location: index.html?contact=error&reason=missing#contact');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.html?contact=error&reason=email#contact');
    exit;
}

$subject = 'YARAMAY Contact Form: ' . $name;

$body = "New contact message from the YARAMAY website\r\n";
$body .= str_repeat('=', 50) . "\r\n\r\n";
$body .= "Name:    {$name}\r\n";
$body .= "Email:   {$email}\r\n\r\n";
$body .= "Message:\r\n";
$body .= str_repeat('-', 50) . "\r\n";
$body .= wordwrap($message, 70) . "\r\n\r\n";
$body .= str_repeat('=', 50) . "\r\n";
$body .= 'Submitted: ' . date('F j, Y \a\t g:i A T') . "\r\n";

$result = BrevoMailer::send([
    'to' => BrevoMailer::adminRecipients(),
    'subject' => $subject,
    'textBody' => $body,
    'replyTo' => ['email' => $email, 'name' => $name],
    'context' => 'contact',
]);

if ($result['success']) {
    header('Location: index.html?contact=success#contact');
    exit;
}

$reason = $result['error'] === 'body_too_large' ? 'toolarge' : 'send';
header('Location: index.html?contact=error&reason=' . $reason . '#contact');
exit;
