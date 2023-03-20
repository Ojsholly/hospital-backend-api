<body>
    <h1>Appointment Completed</h1>
    <p>Hi {{ $appointment->doctor->user->full_name }},</p>
    <p>Your appointment booking with {{ $appointment->user->full_name }} has been completed successfully and your wallet has been credited as well.</p>
    <p>Thanks,</p>
    <p>{{ config('app.name') }}</p>
</body>
