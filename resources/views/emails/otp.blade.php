<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset Code</title>
</head>
<body style="margin:0; padding:0; background-color:#eef4f2; font-family: 'Segoe UI', Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef4f2; padding:2rem 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:1rem; overflow:hidden; box-shadow:0 8px 24px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #1f6f5c, #123f34); padding:1.75rem 2rem;">
                            <span style="color:#fff; font-size:1.25rem; font-weight:700;">CFMC Cooperative</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:2rem;">
                            <h2 style="margin:0 0 0.75rem; color:#0f172a;">Password Reset Code</h2>
                            <p style="margin:0 0 1.5rem; color:#475569; font-size:0.95rem; line-height:1.5;">
                                Hi {{ $user->name }}, use the code below to reset your account password. This code expires in 10 minutes.
                            </p>
                            <div style="text-align:center; margin:1.5rem 0;">
                                <span style="display:inline-block; font-size:2rem; font-weight:800; letter-spacing:0.3em; color:#1f6f5c; background:#e4f1ee; padding:0.75rem 1.5rem; border-radius:0.75rem;">
                                    {{ $otp }}
                                </span>
                            </div>
                            <p style="margin:0; color:#94a3b8; font-size:0.85rem; line-height:1.5;">
                                If you didn't request this, you can safely ignore this email &mdash; your password will remain unchanged.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
