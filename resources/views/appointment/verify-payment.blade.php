<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} || Order Payment Confirmation</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
</head>
<body>
<div class="flex-center position-ref full-height">

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.17/dist/sweetalert2.all.min.js" integrity="sha256-RhRrbx+dLJ7yhikmlbEyQjEaFMSutv6AzLv3m6mQ6PQ=" crossorigin="anonymous"></script>
<script type="text/javascript">
    $('document ').ready(function () {
        @isset($status)
        Swal.fire({
            icon: '{{ $status }}',
            title: '{{ $status == 'error' ? "Oops..." : "Success" }}',
            text: '{{ $message }}'
        }).then((result) => {
            window.location.href = "{{ $redirect_url ?? config('settings.landing-page-url') }}"
        });
        @endisset
    });
</script>
</body>
</html>
