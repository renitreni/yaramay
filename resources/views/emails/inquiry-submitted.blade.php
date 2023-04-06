
<!DOCTYPE html>
<html>
<head>
    <title>New Reservation</title>
</head>
<body>
    <h2>New Inquiry Submitted</h2>

        <p>Name: {{ $mailData['name'] }}</p>
        <p>Email: {{ $mailData['email'] }}</p>
        <p>Subject: {{ $mailData['subject'] }}</p>
        <p>Message: {{ $mailData['message'] }}</p>
</body>
</html>
