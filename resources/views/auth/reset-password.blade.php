<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} || Password Reset</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #f1f1f1;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }
        .full-height {
            height: 100vh;
        }
        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }
        .position-ref {
            position: relative;
        }
        input {
            padding: 10pt;
            width: 60%;
            font-size: 15pt;
            border-radius: 5pt;
            border: 1px solid lightgray;
            margin: 10pt;
        }
        .form-container {
            display: flex;
            flex-direction: column;
            width: 60%;
            align-items: center;
            margin: 20pt;
            border: 1px solid lightgray;
            padding: 20pt;
            border-radius: 5pt;
            background: white;
        }
        button {
            border-radius: 5pt;
            padding: 10pt 14pt;
            background: white;
            border: 1px solid gray;
            font-size: 14pt;
            margin: 20pt;
        }
        button:hover {
            background: lightgray;
        }
    </style>

</head>
<body>
<div class="flex-center position-ref full-height">
    <form class="form-container" action="{{ route('password.update') }}" method="POST">
        @csrf
        <h2>{{ config('app.name') }} || Password Reset</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <input name="email"  hidden value="{{ request()->get('email') }}">
        <input name="password" placeholder="Enter new password" type="password" minlength="8">
        <input name="password_confirmation" placeholder="Confirm new password" minlength="8" type="password">
        <input hidden name="token" placeholder="token" value="{{ request()->get('token') }}">

        <button type="submit">Submit</button>
    </form>
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
        });
        @endisset
    });
</script>
</body>
</html>
