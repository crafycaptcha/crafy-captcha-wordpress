<?php

require __DIR__ . '/config.php';

?>
<?php require './rec/menu.php'; ?>

<div class="login-container" style="max-width: 800px; display: flex; gap: 20px; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 300px;">
        <h2>Formulario 1 (Registro)</h2>

        <div class="theme-buttom"><a
                href="./twoForms.php?theme=<?php if (isset($_GET['theme']) AND $_GET['theme'] === 'dark') { ?>light<?php } else { ?>dark<?php } ?>">☀️
                🌙</a></div>

        <form action="twoForms_verif.php" method="POST">
            <div class="form-group">
                <label for="email1">Email</label>
                <input type="email" id="email1" name="email" placeholder="ejemplo1@correo.com">
            </div>

            <div class="form-group">
                <label for="password1">Password</label>
                <input type="password" id="password1" name="password" placeholder="••••••••">
            </div>

            <div class="crafy-demo-notice">
                <span class="demo-notice-icon">ℹ️</span>
                Widget 1 para el Formulario 1
            </div>

            <div id="crafy-container-1"></div>

            <button type="submit" class="submit-button">Registrar</button>
        </form>
    </div>

    <div style="flex: 1; min-width: 300px;">
        <h2>Formulario 2 (Recuperación)</h2>

        <form action="twoForms_verif.php" method="POST">
            <div class="form-group">
                <label for="email2">Email</label>
                <input type="email" id="email2" name="email" placeholder="ejemplo2@correo.com">
            </div>

            <div class="crafy-demo-notice">
                <span class="demo-notice-icon">ℹ️</span>
                Widget 2 para el Formulario 2
            </div>

            <div id="crafy-container-2"></div>
            
            <div class="crafy-demo-notice">
                <span class="demo-notice-icon">ℹ️</span>
                Widget 3 (Test de Cascading Submit en Form 2)
            </div>

            <div id="crafy-container-3"></div>

            <button type="submit" class="submit-button">Recuperar</button>
        </form>
    </div>

    <div class="footer-link" style="width: 100%;">
        <a href="../">Back to CrafyCAPTCHA</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        CrafyCAPTCHA.setAutoLoad(false);
        CrafyCAPTCHA.setDebug(true);

        const themeOpt = <?php echo (isset($_GET['theme']) && $_GET['theme'] === 'dark') ? "'dark'" : "undefined"; ?>;

        // Widget 1 - Formulario 1
        CrafyCAPTCHA.init(
            'crafy-container-1',
            '<?php echo $GLOBALS['global_config']['public_key']; ?>',
            '<?php echo $GLOBALS['global_CrafyCAPTCHA']->getPublicToken(); ?>',
            '<?php echo $GLOBALS['global_config']['signing_public_key']; ?>',
            {
                optionsUrl: '<?php echo DEMO_RUTA; ?>demo/twoForms_options.php',
                iframeUrl: '<?php echo DEMO_RUTA; ?>challenge',
                inputName: 'CrafyCAPTCHA_token_1',
                onSuccess: (token) => {
                    console.log("Formulario 1 - Humano verificado. Token:", token);
                },
                ...(themeOpt ? { theme: themeOpt } : {})
            }
        );

        // Widget 2 - Formulario 2
        CrafyCAPTCHA.init(
            'crafy-container-2',
            '<?php echo $GLOBALS['global_config']['public_key']; ?>',
            '<?php echo $GLOBALS['global_CrafyCAPTCHA']->getPublicToken(); ?>',
            '<?php echo $GLOBALS['global_config']['signing_public_key']; ?>',
            {
                optionsUrl: '<?php echo DEMO_RUTA; ?>demo/twoForms_options.php',
                iframeUrl: '<?php echo DEMO_RUTA; ?>challenge',
                inputName: 'CrafyCAPTCHA_token_2',
                onSuccess: (token) => {
                    console.log("Formulario 2 (W1) - Humano verificado. Token:", token);
                },
                ...(themeOpt ? { theme: themeOpt } : {})
            }
        );

        // Widget 3 - Formulario 2 (Prueba de Cascading Submit)
        CrafyCAPTCHA.init(
            'crafy-container-3',
            '<?php echo $GLOBALS['global_config']['public_key']; ?>',
            '<?php echo $GLOBALS['global_CrafyCAPTCHA']->getPublicToken(); ?>',
            '<?php echo $GLOBALS['global_config']['signing_public_key']; ?>',
            {
                optionsUrl: '<?php echo DEMO_RUTA; ?>demo/twoForms_options.php',
                iframeUrl: '<?php echo DEMO_RUTA; ?>challenge',
                inputName: 'CrafyCAPTCHA_token_3',
                onSuccess: (token) => {
                    console.log("Formulario 2 (W2) - Humano verificado. Token:", token);
                },
                ...(themeOpt ? { theme: themeOpt } : {})
            }
        );
    });
</script>

<?php require './rec/footer.php'; ?>
