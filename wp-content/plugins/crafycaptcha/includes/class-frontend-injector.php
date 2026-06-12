<?php
/**
 * Frontend Injector Class
 * 
 * Handles loading the Javascript SDK and rendering the widget in WordPress forms.
 */

if (!defined('ABSPATH')) {
    exit;
}

class CrafyCaptcha_Frontend_Injector
{

    public function __construct()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
        add_action('login_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));

        $hooks = array(
            'login_form',
            'register_form',
            'lostpassword_form',
            'comment_form_after_fields'
        );

        foreach ($hooks as $hook) {
            add_action($hook, array(__CLASS__, 'render_widget'));
        }
    }

    /**
     * Encola el script JS del SDK en el footer.
     */
    public static function enqueue_scripts()
    {
        $public_key = get_option('crafycaptcha_public_key', '');
        if (empty($public_key)) {
            return;
        }

        wp_enqueue_script(
            'crafycaptcha-js',
            'https://cdn.jsdelivr.net/gh/crafycaptcha/crafy-captcha-js/dist/CrafyCAPTCHA.min.js', // As per the documentation examples
            array(),
            null,
            true
        );
    }

    /**
     * Renderiza el contenedor HTML y el script de inicialización inline.
     */
    public static function render_widget()
    {
        error_log('CrafyCAPTCHA: render_widget() called.');
        $public_key = get_option('crafycaptcha_public_key', '');
        if (empty($public_key)) {
            error_log('CrafyCAPTCHA: render_widget() aborted - public_key is empty.');
            return;
        }

        $crafy = CrafyCaptcha_Core::get_instance();
        if (!$crafy) {
            error_log('CrafyCAPTCHA: render_widget() aborted - get_instance() returned null. Check secret_key or SDK loading.');
            return;
        }

        try {
            $public_token = $crafy->getPublicToken();
        } catch (Exception $e) {
            error_log('CrafyCAPTCHA: render_widget() Error getting public token: ' . $e->getMessage());
            echo '<div class="crafycaptcha-error" style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 15px;">';
            echo '<strong>CrafyCAPTCHA Error:</strong> No se pudo inicializar (Error al obtener Public Token). Detalles: ' . esc_html($e->getMessage());
            echo '</div>';
            return;
        }

        error_log('CrafyCAPTCHA: render_widget() proceeding to render.');
        $signing_public_key = get_option('crafycaptcha_signing_public_key', '');
        // Endpoint AJAX para obtener las opciones seguras cifradas
        $options_url = admin_url('admin-ajax.php?action=crafycaptcha_options');

        $container_id = uniqid('crafycaptcha_');

        ?>
        <div id="<?php echo esc_attr($container_id); ?>" style="margin-bottom: 15px;"></div>
        <script>
            window.addEventListener('CrafyCAPTCHALoaded', () => {
                CrafyCAPTCHA.setAutoLoad(false);
                CrafyCAPTCHA.init(
                    '<?php echo esc_js($container_id); ?>',
                    '<?php echo esc_js($public_key); ?>',
                    '<?php echo esc_js($public_token); ?>',
                    '<?php echo esc_js($signing_public_key); ?>',
                    {
                        optionsUrl: '<?php echo esc_url_raw($options_url); ?>',
                        inputName: 'crafycaptcha_token'
                    }
                );
            });
        </script>
        <?php
    }
}
