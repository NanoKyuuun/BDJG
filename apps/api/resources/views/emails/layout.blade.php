<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'BDJG Creative Studio' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #09090b;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #f4f4f5;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #09090b;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #121215;
            border-radius: 12px;
            border: 1px solid #27272a;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
        }
        .header {
            padding: 32px 32px 24px;
            text-align: center;
            border-bottom: 1px solid #1e1e24;
            background: linear-gradient(180deg, #18181b 0%, #121215 100%);
        }
        .logo {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.15em;
            color: #f59e0b;
            text-transform: uppercase;
        }
        .logo-sub {
            font-size: 11px;
            letter-spacing: 0.25em;
            color: #a1a1aa;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .content {
            padding: 36px 32px;
            line-height: 1.6;
            font-size: 15px;
            color: #d4d4d8;
        }
        .content h1 {
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .content p {
            margin: 0 0 16px;
        }
        .highlight-box {
            background-color: #18181b;
            border: 1px solid #27272a;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
            padding: 16px 20px;
            margin: 24px 0;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            display: inline-block;
            background-color: #f59e0b;
            color: #000000 !important;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            letter-spacing: 0.02em;
        }
        .footer {
            padding: 24px 32px;
            background-color: #0c0c0e;
            border-top: 1px solid #1e1e24;
            text-align: center;
            font-size: 12px;
            color: #71717a;
            line-height: 1.5;
        }
        .footer a {
            color: #a1a1aa;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <div class="container">
                    <div class="header">
                        <div class="logo">BDJG STUDIO</div>
                        <div class="logo-sub">Creative Cinema & Studio OS</div>
                    </div>
                    <div class="content">
                        @yield('content')
                    </div>
                    <div class="footer">
                        <p>© {{ date('Y') }} BDJG Creative Studio. All rights reserved.</p>
                        <p>This is an automated operational notification. Please do not reply directly to this email.</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
