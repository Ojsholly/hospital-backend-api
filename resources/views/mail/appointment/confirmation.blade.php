<body>
    Hello {{ $appointment->user->first_name }},<br>
    <br>
    Your appointment with {{ $appointment->doctor->user->full_name }} has been confirmed.<br> This is a reminder that your appointment is on {{ $appointment->datetime->toDayDateTimeString() }}.
</body>
