<!DOCTYPE html>
<html>

<head>
    <title>Password Reset Request</title>
</head>

<body>
    <p>Hello,</p>
    {{-- <p>You have requested a password reset. Click the link below to reset your password:</p> --}}
    <p>You have requested a password reset. </p>
    <p>
        {{-- <a href="{{ url('/api/reset_password?token=' . $token) }}"> --}}
        {{-- Reset Your Password --}}
        {{-- </a> --}}
    <p>Please enter the code {{ $token }} to verify your account. </p>
    </p>
    <p>If you did not request this, please ignore this email.</p>
</body>

</html>