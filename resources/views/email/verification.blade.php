<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
</head>

<body style="margin: 0; padding: 0; background-color: #f0f4f8; font-family: 'Helvetica Neue', Arial, sans-serif;">

    <div>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
            style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
            <tr>
                <td style="padding: 20px;">
                    <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 20px; text-align: center;">Verify
                        Your Email Address</h2>
                    <p style="margin-top: 20px; text-align: center;">Thank you for signing up at Ngechat. To complete
                        your registration,
                        we require verification of your email address.</p>
                    <p style="text-align: center; margin-top: 20px;">
                        <a href="{{ env('FRONDEND_URL') }}email/verify?token={{ $verificationUrl }}"
                            style="display: inline-block; background-color: #1476c5; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Verify
                            Your Email Address</a>
                    </p>
                    <p style="margin-top: 20px; text-align: center;">If the button above doesn't work, you can copy and
                        paste the following
                        link into your web browser:</p>
                    <p style="margin-top: 20px; text-align: center;">
                        <a target="_blank" href="{{ env('FRONDEND_URL') }}email/verify?token={{ $verificationUrl }}"
                            style="color: #0415b4; text-decoration: underline;">
                            http://localhost:5173/email/verify?token={{ $verificationUrl }}
                        </a>
                    </p>
                    <p style="margin-top: 20px; text-align: center;">Feel free to contact our support if you encounter
                        any issues or have
                        questions. Thank you for your participation!</p>
                    <p style="margin-top: 20px; text-align: center;">Best regards,<br>The Ngechat Team</p>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
