<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333333;
        }

        p {
            color: #666666;
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
        }

        .reset-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
        }

        @media only screen and (max-width: 600px) {
            .container {
                margin: 10px;
                padding: 10px;
            }

            .reset-button {
                display: block;
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Password Reset Request</h1>
        <p>Hello!</p>
        <p>You've recently requested to reset your account password. Click the button below to proceed with the password
            reset:</p>

        <div class="button-container">
            <a class="reset-button" href="{{ env('FRONDEND_URL') }}reset/password?token={{ $token }}">Reset
                Password</a>
        </div>

        <p>If you did not make this request, please disregard this email.</p>

        <p>Thank you,<br>Ngechat</p>
    </div>
</body>

</html>
