<body>
    <h1>Appointment Completed</h1>
    <p>Hi {{ $appointment->user->full_name }},</p>
    <p>Your appointment with {{ $appointment->doctor->user->full_name }} has been completed.</p>
    <p>Thanks,</p>
    <p>{{ config('app.name') }}</p>
</body>
