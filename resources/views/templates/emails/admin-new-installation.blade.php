<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Submission Details</title>
    <style>
        /* Basic email styles */
        body, table, td, a {
            font-family: Arial, sans-serif;
            color: #333;
            text-decoration: none;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        table {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            border-spacing: 0;
        }
        td {
            padding: 10px;
        }
        .header {
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 24px;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 0 0 10px 10px;
        }
        .content h3 {
            color: #007bff;
            font-size: 20px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin: 5px 0;
        }
        .footer {
            font-size: 12px;
            text-align: center;
            color: #777;
            margin-top: 20px;
        }
        @media only screen and (max-width: 600px) {
            .header {
                font-size: 20px;
            }
            .content h3 {
                font-size: 18px;
            }
            .content p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td class="header">
                New public installation form submission
            </td>
        </tr>
        <tr>
            <td class="content">
                <p><strong>First Name:</strong> {{ $formData['firstName'] }}</p>
                <p><strong>Last Name:</strong> {{ $formData['lastName'] }}</p>
                <p><strong>Email:</strong> {{ $formData['email'] }}</p>
                <p><strong>Site Name:</strong> {{ $formData['siteName'] }}</p>
                <p><strong>Phone:</strong> {{ $formData['phone'] }}</p>
                <p><strong>Terms and Conditions:</strong> {{ isset($formData['phone']) }}</p>
                <p><strong>Optional Agreements:</strong> {{ $formData['phone'] }}</p>
            </td>
        </tr>
        <tr>
            <td class="footer">
                <p>&copy; {{ date('Y') }} Your Company. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
