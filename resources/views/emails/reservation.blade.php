<!DOCTYPE html>
<html>
<head>
    <title>New Reservation</title>
</head>
<body>
    <h1>New Reservation</h1>
    <p>A new reservation has been made:</p>
    <ul>
        <li>Name: {{ $mailData['name'] }}</li>
        <li>Email: {{ $mailData['email'] }}</li>
        <li>Phone Number: {{ $mailData['phone_number'] }}</li>
        <li>Date: {{ $mailData['date'] }}</li>
        <li>Time: {{ $mailData['time'] }}</li>
        <li>Number of Guests: {{ $mailData['number_of_guests'] }}</li>
        <li>Message: {{ $mailData['message'] }}</li>
    </ul>
</body>
</html>
