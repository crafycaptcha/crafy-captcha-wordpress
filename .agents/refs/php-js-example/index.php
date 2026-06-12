<?php
// Require the configuration file to load the SDK and credentials
require_once __DIR__ . '/config.php';

// Fetch the dynamic public token using the PHP SDK
// This token may change if you upgrade or downgrade your plan, so always fetch it dynamically
$publicToken = $global_CrafyCAPTCHA->getPublicToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrafyCAPTCHA Integration Example</title>

    <style>
        body {
            font-family: sans-serif;
            padding: 2rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.5rem;
            box-sizing: border-box;
        }

        button {
            padding: 0.75rem 1.5rem;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>

    <!-- Register the CrafyCAPTCHALoaded event listener BEFORE loading the SDK -->
    <script>
        // This event is automatically fired when the SDK script finishes loading
        // We use it to ensure the CrafyCAPTCHA class is available before initializing
        window.addEventListener('CrafyCAPTCHALoaded', () => {
            CrafyCAPTCHA.init(
                'crafy-container',                                  // The ID of the container element
                '<?php echo $global_config['public_key']; ?>',      // Your Public Key
                '<?php echo $publicToken; ?>',                      // Your Dynamic Public Token
                '<?php echo $global_config['signing_public_key']; ?>', // Your Signing Public Key
                {
                    // URL that returns the encrypted iframe options
                    // The SDK will make a POST request to this URL to fetch the challenge options
                    optionsUrl: 'options.php',

                    // The name of the hidden input field that will contain the token upon completion
                    inputName: 'CrafyCAPTCHA_token',

                    // Callback executed when the captcha is successfully solved
                    onSuccess: (token) => {
                        console.log("CAPTCHA solved successfully! Token:", token);
                        // Optional: you can automatically submit the form here if desired
                    }
                }
            );
        });
    </script>

    <!-- Include the CrafyCAPTCHA JS SDK using the tag defined in config.php -->
    <?php echo str_replace('></script>', ' defer></script>', $global_config['js_cdn_tag']); ?>

</head>

<body>
    <h2>Login Form Example</h2>
    <p>This is a basic example of how to integrate the CrafyCAPTCHA JS SDK and PHP SDK.</p>

    <!-- The form sends data to verif.php for server-side verification -->
    <form action="verif.php" method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username">
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password">
        </div>

        <!-- The container where CrafyCAPTCHA will be rendered -->
        <div class="form-group">
            <div id="crafy-container"></div>
        </div>

        <button type="submit">Login</button>
    </form>
</body>

</html>