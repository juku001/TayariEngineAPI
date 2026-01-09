<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Team Invitation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .header {
            background: #1e40af;
            /* Tayari blue */
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 24px;
            line-height: 1.6;
        }

        .button-wrapper {
            text-align: center;
            margin: 32px 0;
        }

        .btn {
            display: inline-block;
            background: #2563eb;
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
        }

        .footer {
            background: #f1f5f9;
            padding: 16px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }

        .team-name {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h2>You’ve Been Invited 🎉</h2>
        </div>

        <div class="content">
            <p>Hello,</p>

            <p>
                <strong>{{ $inviterName ?? 'Team Admin' }}</strong>
                has invited you to join
                <span class="team-name">
                    {{ $teamName ?? 'their team' }}
                </span>
                on <strong>Tayari</strong>.
            </p>

            <p>
                Click the button below to accept the invitation and get started.
            </p>

            <div class="button-wrapper">
                <a href="{{ $inviteLink }}" class="btn">
                    Accept Invitation
                </a>
            </div>

            <p>
                If the button doesn’t work, copy and paste the link below into your browser:
            </p>

            <p style="word-break: break-all;">
                <a href="{{ $inviteLink }}">{{ $inviteLink }}</a>
            </p>

            <p>
                This invitation link is unique and will expire once used.
            </p>
        </div>

        <div class="footer">
            <p>
                © {{ date('Y') }} Tayari. All rights reserved.
            </p>
            <p>
                If you did not expect this invitation, you can safely ignore this email.
            </p>
        </div>
    </div>

</body>

</html>