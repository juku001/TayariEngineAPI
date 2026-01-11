<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
</head>
<body style="margin:0; padding:0; background-color:#FFF7EF; font-family:Arial, Helvetica, sans-serif;">

    <!-- Outer Wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
        <tr>
            <td align="center">

                <!-- Card -->
                <table width="100%" cellpadding="0" cellspacing="0"
                       style="max-width:520px; background-color:#FFFFFF; border-radius:24px; padding:40px; box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding-bottom:24px;">
                            <img src="{{ asset('images/tayari_logo.jpeg') }}"
                                 alt="Tayari"
                                 width="120"
                                 style="display:block;">
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td align="center" style="font-size:28px; font-weight:700; color:#0F172A; padding-bottom:12px;">
                            Password Reset
                        </td>
                    </tr>

                    <!-- Wave Emoji -->
                    <tr>
                        <td align="center" style="font-size:32px; padding-bottom:20px;">
                            👋
                        </td>
                    </tr>

                    <!-- Message -->
                    <tr>
                        <td align="center"
                            style="font-size:16px; line-height:24px; color:#475569; padding-bottom:30px;">
                            You requested to reset your password.  
                            Please use the verification code below to continue.
                        </td>
                    </tr>

                    <!-- Code Box -->
                    <tr>
                        <td align="center" style="padding-bottom:32px;">
                            <div style="
                                display:inline-block;
                                background-color:#FFF4E6;
                                color:#FF8A00;
                                font-size:24px;
                                font-weight:700;
                                letter-spacing:4px;
                                padding:16px 28px;
                                border-radius:14px;">
                                {{ $token }}
                            </div>
                        </td>
                    </tr>

                    <!-- Info Text -->
                    <tr>
                        <td align="center"
                            style="font-size:14px; line-height:22px; color:#64748B; padding-bottom:32px;">
                            If you did not request a password reset, no further action is required.
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="border-top:1px solid #E5E7EB; padding-top:24px;">
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="font-size:16px; color:#0F172A; padding-top:16px;">
                            Regards,<br>
                            <strong>The Tayari Team</strong>
                        </td>
                    </tr>

                </table>

                <!-- Support -->
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;">
                    <tr>
                        <td align="center"
                            style="font-size:14px; color:#64748B; padding-top:24px;">
                            Need help? Contact our support team at<br>
                            <a href="mailto:support@tayari.work"
                               style="color:#FF8A00; text-decoration:none; font-weight:600;">
                                support@tayari.work
                            </a>
                        </td>
                    </tr>

                    <!-- Copyright -->
                    <tr>
                        <td align="center"
                            style="font-size:12px; color:#94A3B8; padding-top:16px;">
                            © {{ date('Y') }} TAYARI PLATFORM. ALL RIGHTS RESERVED.
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
