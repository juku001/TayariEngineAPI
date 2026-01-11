<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email</title>
</head>

<body style="margin:0; padding:0; background-color:#FFF7EF; font-family:Arial, Helvetica, sans-serif;">

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
                             width="124"
                             style="display:block;">
                    </td>
                </tr>

                <!-- Title -->
                <tr>
                    <td align="center"
                        style="font-size:28px; font-weight:700; color:#0F172A; padding-bottom:12px;">
                        Verify Your Email
                    </td>
                </tr>

                <!-- Emoji -->
                <tr>
                    <td align="center" style="font-size:34px; padding-bottom:20px;">
                        👋
                    </td>
                </tr>

                <!-- Greeting -->
                <tr>
                    <td align="center"
                        style="font-size:16px; line-height:24px; color:#475569; padding-bottom:20px;">
                        Hello <strong>{{ $user->name }}</strong>,
                    </td>
                </tr>

                <!-- Message -->
                <tr>
                    <td align="center"
                        style="font-size:16px; line-height:24px; color:#475569; padding-bottom:28px;">
                        Thanks for signing up to <strong>Tayari</strong>.
                        Please confirm your email address to complete your registration.
                    </td>
                </tr>

                <!-- CTA Button -->
                <tr>
                    <td align="center" style="padding-bottom:32px;">
                        <a href="{{ $url }}"
                           style="
                               display:inline-block;
                               background-color:#FF8A00;
                               color:#FFFFFF;
                               font-size:16px;
                               font-weight:700;
                               padding:16px 32px;
                               border-radius:9999px;
                               text-decoration:none;">
                            Verify Email Address
                        </a>
                    </td>
                </tr>

                <!-- Fallback -->
                <tr>
                    <td align="center"
                        style="font-size:14px; line-height:22px; color:#64748B; padding-bottom:20px;">
                        If the button doesn’t work, copy and paste this link into your browser:
                    </td>
                </tr>

                <tr>
                    <td align="center"
                        style="font-size:13px; color:#0F172A; word-break:break-all; padding-bottom:28px;">
                        <a href="{{ $url }}"
                           style="color:#FF8A00; text-decoration:none;">
                            {{ $url }}
                        </a>
                    </td>
                </tr>

                <!-- Expiry -->
                <tr>
                    <td align="center"
                        style="font-size:13px; line-height:20px; color:#94A3B8; padding-bottom:24px;">
                        This verification link will expire in 60 minutes.
                    </td>
                </tr>

                <!-- Divider -->
                <tr>
                    <td style="border-top:1px solid #E5E7EB; padding-top:24px;"></td>
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
