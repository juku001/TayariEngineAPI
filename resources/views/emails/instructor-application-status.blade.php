<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application Status</title>
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
                                 width="124"
                                 style="display:block;">
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td align="center"
                            style="font-size:28px; font-weight:700; color:#0F172A; padding-bottom:12px;">
                            Application Status
                        </td>
                    </tr>

                    <!-- Greeting -->
                    <tr>
                        <td align="center"
                            style="font-size:16px; color:#475569; padding-bottom:24px;">
                            Dear <strong>{{ $application['name'] }}</strong>,
                        </td>
                    </tr>

                    @if ($status === 'approved')
                        <!-- Approved Emoji -->
                        <tr>
                            <td align="center" style="font-size:36px; padding-bottom:16px;">
                                🎉
                            </td>
                        </tr>

                        <!-- Approved Message -->
                        <tr>
                            <td align="center"
                                style="font-size:16px; line-height:24px; color:#475569; padding-bottom:20px;">
                                Congratulations! Your instructor application has been
                                <strong style="color:#16A34A;">approved</strong>.
                            </td>
                        </tr>

                        <!-- Instructions -->
                        <tr>
                            <td align="center"
                                style="font-size:15px; line-height:23px; color:#64748B; padding-bottom:24px;">
                                You can now log in and start creating courses on Tayari.
                            </td>
                        </tr>

                        <!-- Password Box -->
                        <tr>
                            <td align="center" style="padding-bottom:32px;">
                                <div style="
                                    display:inline-block;
                                    background-color:#ECFDF5;
                                    color:#16A34A;
                                    font-size:16px;
                                    font-weight:700;
                                    padding:14px 22px;
                                    border-radius:14px;">
                                    Login Password: {{ $pass }}
                                </div>
                            </td>
                        </tr>
                    @else
                        <!-- Rejected Emoji -->
                        <tr>
                            <td align="center" style="font-size:36px; padding-bottom:16px;">
                                😔
                            </td>
                        </tr>

                        <!-- Rejected Message -->
                        <tr>
                            <td align="center"
                                style="font-size:16px; line-height:24px; color:#475569; padding-bottom:20px;">
                                We regret to inform you that your instructor application has been
                                <strong style="color:#DC2626;">rejected</strong>.
                            </td>
                        </tr>

                        <!-- Support Info -->
                        <tr>
                            <td align="center"
                                style="font-size:15px; line-height:23px; color:#64748B; padding-bottom:32px;">
                                If you have questions or need clarification, feel free to contact our support team.
                            </td>
                        </tr>
                    @endif

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
