<?php

require __DIR__ . '/config.php';

$success = false;
$errorMessage = '';
$hasErrorDetails = false;
$errorDetails = '';

$verifiedTokens = 0;
$totalTokensReceived = 0;

$tokensToCheck = [
    'CrafyCAPTCHA_token_1' => 'Formulario 1',
    'CrafyCAPTCHA_token_2' => 'Formulario 2 (Widget 2)',
    'CrafyCAPTCHA_token_3' => 'Formulario 2 (Widget 3)'
];

foreach ($tokensToCheck as $inputName => $formName) {
    if (isset($_POST[$inputName])) {
        $totalTokensReceived++;
        $token = (string) trim($_POST[$inputName]);
        if (!empty($token)) {
            // Se usa el context action = demo_twoForms que se definió en twoForms_options.php
            if ($GLOBALS['global_CrafyCAPTCHA']->verifyFlow($token, ['action' => 'demo_twoForms'])) {
                $verifiedTokens++;
            } else {
                if (empty($errorMessage)) {
                    $errorMessage = "Invalid Flow verification for $formName";
                    $hasErrorDetails = true;
                    $errorDetails = $GLOBALS['global_CrafyCAPTCHA']->getLastFlowVerifyError();
                }
            }
        } else {
            if (empty($errorMessage)) {
                $errorMessage = "Token is empty for $formName";
            }
        }
    }
}

if ($totalTokensReceived > 0 && $verifiedTokens === $totalTokensReceived) {
    $success = true;
} else if ($totalTokensReceived === 0) {
    $success = false;
    $errorMessage = 'No tokens were received from any of the forms.';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Result - CrafyCAPTCHA</title>
    <style>
        :root {
            --bg-gradient-start: #f8fafc;
            --bg-gradient-end: #e2e8f0;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #1877f2;
            --primary-hover: #166fe5;
            --success-color: #10b981;
            --success-bg: #ecfdf5;
            --error-color: #ef4444;
            --error-bg: #fef2f2;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-gradient-start: #0f172a;
                --bg-gradient-end: #020617;
                --card-bg: #1e293b;
                --text-main: #f8fafc;
                --text-muted: #94a3b8;
                --success-bg: rgba(16, 185, 129, 0.1);
                --error-bg: rgba(239, 68, 68, 0.1);
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .result-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 440px;
            padding: 40px 30px;
            text-align: center;
            animation: slideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            animation: scaleIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s forwards;
            opacity: 0;
            transform: scale(0.5);
        }

        @keyframes scaleIn {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .status-icon.success {
            background-color: var(--success-bg);
            color: var(--success-color);
        }

        .status-icon.error {
            background-color: var(--error-bg);
            color: var(--error-color);
        }

        .status-icon svg {
            width: 40px;
            height: 40px;
            stroke-width: 2.5;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 12px;
        }

        .message {
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 30px;
            word-break: break-word;
        }

        .error-details {
            display: inline-block;
            font-family: 'Courier New', Courier, monospace;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
            padding: 6px 10px;
            margin-top: 12px;
            font-size: 13px;
            color: var(--error-color);
            max-width: 100%;
        }

        @media (prefers-color-scheme: dark) {
            .error-details {
                background: rgba(255, 255, 255, 0.08);
            }
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 14px 24px;
            background-color: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 12px rgba(24, 119, 242, 0.2);
        }

        .btn-back:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(24, 119, 242, 0.3);
        }

        .btn-back:active {
            transform: translateY(1px);
        }
    </style>
</head>

<body>

    <div class="result-card">
        <?php if ($success): ?>
            <div class="status-icon success">
                <svg viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h2>Verification Successful</h2>
            <p class="message">
                The CAPTCHA response has been successfully verified.<br>
                <strong><?php echo $verifiedTokens; ?></strong> widget(s) validated correctly.
            </p>
        <?php else: ?>
            <div class="status-icon error">
                <svg viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
            <h2>Verification Failed</h2>
            <p class="message">
                <?php echo htmlspecialchars($errorMessage); ?>.
                <?php if ($hasErrorDetails): ?>
                    <br>
                    <span class="error-details"><?php echo htmlspecialchars($errorDetails); ?></span>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <a href="twoForms.php" class="btn-back">Go Back</a>
    </div>

</body>

</html>
