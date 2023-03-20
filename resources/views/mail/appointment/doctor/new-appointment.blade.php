<body>
    Hello, {{ $appointment->doctor->user->first_name }}<br>
    <br>
    You have a new appointment with {{ $appointment->user->full_name }} on {{ $appointment->datetime->toDayDateTimeString() }}.
</body>
