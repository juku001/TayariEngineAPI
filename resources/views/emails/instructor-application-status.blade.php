<!DOCTYPE html>
<html>

<head>
    <title>Application Status</title>
</head>

<body>
    <p>Dear {{ $application['name'] }},</p>

    @if ($status === 'approved')
        <p>Congratulations! 🎉 Your instructor application has been <strong>approved</strong>.</p>
        <p>You can now log in and start creating courses.</p>
        <p>Please use {{ $pass }} as your log in password.</p>
    @else
        <p>We regret to inform you that your instructor application has been <strong>rejected</strong>.</p>
        <p>If you have questions, feel free to contact our support team.</p>
    @endif

    <p>Thank you for your interest,<br>Tayari Team</p>
</body>

</html>
