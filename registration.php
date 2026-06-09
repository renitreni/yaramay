<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registration.html');
    exit;
}

function field(string $key): string
{
    return trim($_POST[$key] ?? '');
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function mailHeaders(string $fromAddress, string $fromName, ?string $replyTo = null): array
{
    $headers = [
        'From: ' . $fromName . ' <' . $fromAddress . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion(),
    ];

    if ($replyTo !== null) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    return $headers;
}

function confirmationEmailHtml(string $fullName): string
{
    $name = e($fullName);

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Confirmed</title>
</head>
<body style="margin:0;padding:0;background-color:#eef1f6;font-family:'Segoe UI',Inter,system-ui,sans-serif;color:#1a1a2e;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#eef1f6;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 32px rgba(26,26,126,0.12);">
          <tr>
            <td style="background:linear-gradient(135deg,#1a1a7e 0%,#2d2da8 100%);padding:36px 40px;text-align:center;">
              <p style="margin:0 0 8px;font-size:13px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.75);">Yaramay Company IT Services</p>
              <h1 style="margin:0;font-size:28px;line-height:1.3;font-weight:700;color:#ffffff;">Registration Confirmed</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:40px;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:28px;">
                <tr>
                  <td align="center">
                    <div style="display:inline-block;width:64px;height:64px;line-height:64px;border-radius:50%;background-color:#ecfdf3;color:#16a34a;font-size:32px;text-align:center;">&#10003;</div>
                  </td>
                </tr>
              </table>
              <h2 style="margin:0 0 16px;font-size:22px;line-height:1.4;color:#1a1a7e;text-align:center;">Registration Successfully Received</h2>
              <p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:#5c6370;">Dear {$name},</p>
              <p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:#5c6370;">Thank you for registering for our upcoming <strong style="color:#1a1a2e;">Quality Management System (QMS) Orientation and Awareness Program</strong>. We are pleased to confirm that your registration information has been successfully received and securely recorded in our system.</p>
              <p style="margin:0 0 28px;font-size:16px;line-height:1.7;color:#5c6370;">Our team will provide further event details, reminders, and important updates through your registered contact information as the event date approaches. We sincerely appreciate your interest and participation and look forward to connecting with you soon.</p>
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8f9fc;border-left:4px solid #e63946;border-radius:8px;">
                <tr>
                  <td style="padding:20px 24px;">
                    <p style="margin:0;font-size:15px;line-height:1.6;color:#1a1a2e;font-style:italic;">&mdash; Yaramay Company IT Services</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:24px 40px;background-color:#f8f9fc;border-top:1px solid #e2e6ef;text-align:center;">
              <p style="margin:0;font-size:13px;line-height:1.6;color:#5c6370;">This is an automated confirmation message. Please do not reply directly to this email.</p>
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

function adminEmailHtml(
    string $fullName,
    string $contactNumber,
    string $email,
    string $positionLabel,
    string $companyName,
    string $interested,
    array $phHandlers,
    array $foreignHandlers,
    string $comments
): string {
    $rows = [
        'Name / Full Name' => $fullName,
        'Contact Number' => $contactNumber,
        'Email Address' => $email,
        'Position' => $positionLabel,
        'Company Name or Individual' => $companyName,
        'Interested in System' => $interested,
    ];

    $personalRows = '';
    foreach ($rows as $label => $value) {
        $personalRows .= '<tr>'
            . '<td style="padding:10px 16px;border-bottom:1px solid #e2e6ef;font-size:14px;color:#5c6370;width:38%;vertical-align:top;">' . e($label) . '</td>'
            . '<td style="padding:10px 16px;border-bottom:1px solid #e2e6ef;font-size:14px;color:#1a1a2e;font-weight:600;vertical-align:top;">' . e($value) . '</td>'
            . '</tr>';
    }

    $phSection = handlerSectionHtml('Agency Handlers in Philippines', $phHandlers, 'handler');
    $foreignSection = handlerSectionHtml('Foreign Recruitment / Manpower Handlers', $foreignHandlers, 'agency');
    $commentsBlock = $comments !== ''
        ? '<p style="margin:0;font-size:14px;line-height:1.7;color:#1a1a2e;white-space:pre-wrap;">' . e($comments) . '</p>'
        : '<p style="margin:0;font-size:14px;color:#5c6370;">(none provided)</p>';
    $submittedAt = e(date('F j, Y \a\t g:i A T'));
    $safeFullName = e($fullName);

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New YARAMAY Registration</title>
</head>
<body style="margin:0;padding:0;background-color:#eef1f6;font-family:'Segoe UI',Inter,system-ui,sans-serif;color:#1a1a2e;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#eef1f6;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="680" cellspacing="0" cellpadding="0" style="max-width:680px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 32px rgba(26,26,126,0.12);">
          <tr>
            <td style="background:linear-gradient(135deg,#1a1a7e 0%,#2d2da8 100%);padding:32px 40px;">
              <p style="margin:0 0 8px;font-size:13px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.75);">YARAMAY Website</p>
              <h1 style="margin:0;font-size:26px;line-height:1.3;font-weight:700;color:#ffffff;">New Registration Received</h1>
              <p style="margin:12px 0 0;font-size:15px;color:rgba(255,255,255,0.85);">Submitted by <strong style="color:#ffffff;">{$safeFullName}</strong></p>
            </td>
          </tr>
          <tr>
            <td style="padding:32px 40px 12px;">
              <h2 style="margin:0 0 12px;font-size:18px;color:#1a1a7e;">Personal Information</h2>
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e2e6ef;border-radius:12px;overflow:hidden;">
                {$personalRows}
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:12px 40px;">
              {$phSection}
            </td>
          </tr>
          <tr>
            <td style="padding:12px 40px;">
              {$foreignSection}
            </td>
          </tr>
          <tr>
            <td style="padding:12px 40px 32px;">
              <h2 style="margin:0 0 12px;font-size:18px;color:#1a1a7e;">Comments</h2>
              <div style="padding:16px 20px;background-color:#f8f9fc;border:1px solid #e2e6ef;border-radius:12px;">
                {$commentsBlock}
              </div>
            </td>
          </tr>
          <tr>
            <td style="padding:20px 40px;background-color:#f8f9fc;border-top:1px solid #e2e6ef;text-align:center;">
              <p style="margin:0;font-size:13px;color:#5c6370;">Submitted: {$submittedAt}</p>
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

function handlerSectionHtml(string $title, array $handlers, string $nameKey): string
{
    if (!$handlers) {
        return '<h2 style="margin:0 0 12px;font-size:18px;color:#1a1a7e;">' . e($title) . '</h2>'
            . '<p style="margin:0;font-size:14px;color:#5c6370;">(none provided)</p>';
    }

    $items = '';
    foreach ($handlers as $num => $handler) {
        $items .= '<tr>'
            . '<td style="padding:12px 16px;border-bottom:1px solid #e2e6ef;font-size:14px;color:#1a1a7e;font-weight:700;width:8%;">' . e((string) $num) . '.</td>'
            . '<td style="padding:12px 16px;border-bottom:1px solid #e2e6ef;font-size:14px;color:#1a1a2e;">'
            . '<strong>' . e($handler[$nameKey]) . '</strong><br>'
            . '<span style="color:#5c6370;">Contact Person:</span> ' . e($handler['person']) . '<br>'
            . '<span style="color:#5c6370;">Contact Number:</span> ' . e($handler['number'])
            . '</td>'
            . '</tr>';
    }

    return '<h2 style="margin:0 0 12px;font-size:18px;color:#1a1a7e;">' . e($title) . '</h2>'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e2e6ef;border-radius:12px;overflow:hidden;">'
        . $items
        . '</table>';
}

$fullName = field('full_name');
$contactNumber = field('contact_number');
$email = field('email');
$position = field('position');
$positionOther = field('position_other');
$companyName = field('company_name');
$interested = field('interested_in_system');
$comments = field('comments');

if ($fullName === '' || $contactNumber === '' || $email === '' || $position === '' || $companyName === '' || $interested === '') {
    header('Location: registration.html?registration=error&reason=missing');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: registration.html?registration=error&reason=email');
    exit;
}

$positionLabel = $position;
if ($position === 'other' && $positionOther !== '') {
    $positionLabel = 'Other: ' . $positionOther;
} elseif ($position === 'other') {
    $positionLabel = 'Other';
}

$phHandlers = [];
if(isset($_POST['ph_handler_name']) && is_array($_POST['ph_handler_name'])){
    $phName = $_POST['ph_handler_name'];
    $phPerson = $_POST['ph_handler_person'] ?? [];
    $phNumber = $_POST['ph_handler_number'] ?? [];

    $count = 1;
    foreach ($phName as $index => $nameValue) {
        if(trim($nameValue) !== '') {
            $phHandlers[$count] = [
                'agency' => trim($nameValue),
                'person' => trim($phPerson[$index] ?? ''),
                'number' => trim($phNumber[$index] ?? '')
            ];
            $count++;
        }
    }
}

$foreignHandlers = [];
if(isset($_POST['fr_handler_name']) && is_array($_POST['fr_handler_name'])){
    $frNames = $_POST['fr_handler_name'];
    $frPerson = $_POST['fr_handler_name'] ?? [];
    $frNumber = $_POST['fr_handler_number'] ?? [];

    $count = 1; 
    foreach($frNames as $index => $nameValue){
        if(trim($nameValue) !== ''){
            $foreignHandlers[$count] = [
                'agency' => trim($nameValue),
                'person' => trim($phPerson[$index] ?? ''),
                'number' => trim($phNumber[$index] ?? '')
            ];
            $count++;
        }
    }

}

$recipients = 'renier.trenuela@gmail.com, Sab_princes@yahoo.com';
$subject = 'YARAMAY Registration: ' . $fullName;

$body = adminEmailHtml(
    $fullName,
    $contactNumber,
    $email,
    $positionLabel,
    $companyName,
    $interested,
    $phHandlers,
    $foreignHandlers,
    $comments
);

$fromAddress = 'yaramayservices@gmail.com';
$headers = mailHeaders(
    $fromAddress,
    'YARAMAY Website',
    $fullName . ' <' . $email . '>'
);

$sentAdmin = mail($recipients, $subject, $body, implode("\r\n", $headers));

$confirmationSubject = 'Registration Confirmed – QMS Orientation & Awareness Program';
$confirmationBody = confirmationEmailHtml($fullName);
$confirmationHeaders = mailHeaders(
    $fromAddress,
    'Yaramay Company IT Services',
    'Yaramay Company IT Services <' . $fromAddress . '>'
);

$sentConfirmation = mail($email, $confirmationSubject, $confirmationBody, implode("\r\n", $confirmationHeaders));

$sent = $sentAdmin && $sentConfirmation;

if ($sent) {
    header('Location: registration.html?registration=success');
} else {
    header('Location: registration.html?registration=error&reason=send');
}
exit;
