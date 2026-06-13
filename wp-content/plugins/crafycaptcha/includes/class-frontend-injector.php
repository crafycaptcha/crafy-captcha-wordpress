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
        add_filter('script_loader_tag', array(__CLASS__, 'add_script_attributes'), 10, 2);

        $hooks = array(
            'login_form',
            'register_form',
            'lostpassword_form',
            'comment_form_after_fields',
            'comment_form_logged_in_after'
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
            'https://cdn.jsdelivr.net/gh/crafycaptcha/crafy-captcha-js@1.1.6/dist/CrafyCAPTCHA.min.js',
            array(),
            null,
            true
        );

    }

    /**
     * Añade atributos de seguridad (integrity y crossorigin) al script del CDN.
     */
    public static function add_script_attributes($tag, $handle)
    {
        if ('crafycaptcha-js' === $handle) {
            return str_replace(' src', ' defer integrity="sha256-6U66+z7itP4Nexm7OJauXWVAKPMPY3sbdLU9/JMfbmY=" crossorigin="anonymous" src', $tag);
        }
        return $tag;
    }

    /**
     * Renderiza el contenedor HTML y el script de inicialización inline.
     */
    public static function render_widget()
    {

        CrafyCaptcha_Core::log('render_widget() called.');

        $public_key = get_option('crafycaptcha_public_key', '');
        if (empty($public_key)) {
            CrafyCaptcha_Core::log('render_widget() aborted - public_key is empty.');
            return;
        }

        $crafy = CrafyCaptcha_Core::get_instance();
        if (!$crafy) {
            CrafyCaptcha_Core::log('render_widget() aborted - get_instance() returned null. Check secret_key or SDK loading.');
            return;
        }

        try {
            $public_token = $crafy->getPublicToken();
        } catch (Exception $e) {
            CrafyCaptcha_Core::log('render_widget() Error getting public token: ' . $e->getMessage());
            echo '<div class="crafycaptcha-error" style="color: #666; padding: 10px; margin-bottom: 15px;">';
            echo esc_html__('El servicio de seguridad no está disponible temporalmente.', 'crafycaptcha');
            echo '</div>';
            return;
        }

        CrafyCaptcha_Core::log('render_widget() proceeding to render.');
        $signing_public_key = get_option('crafycaptcha_signing_public_key', '');
        $options_url = admin_url('admin-ajax.php?action=crafycaptcha_options');

        $container_id = 'crafycaptcha_' . uniqid();
        $input_name = 'crafycaptcha_token_' . uniqid();
        ?>
        <div id="<?php echo esc_attr($container_id); ?>" class="crafycaptcha-container" style="margin-bottom: 15px;"></div>
        <script>
            (function () {
                var crafyNonce = '<?php echo esc_js(wp_create_nonce('crafycaptcha_options_nonce')); ?>';
                const containerId = '<?php echo esc_js($container_id); ?>';
                const inputName = '<?php echo esc_js($input_name); ?>';
                let initialized = false;

                const initWidget = () => {
                    if (initialized) return;
                    initialized = true;
                    console.log('[CrafyCAPTCHA Plugin] Initializing widget for container:', containerId, 'with inputName:', inputName);
                    try {
                        CrafyCAPTCHA.setAutoLoad(false);
                        var crafyInstance = CrafyCAPTCHA.init(
                            containerId,
                            '<?php echo esc_js($public_key); ?>',
                            '<?php echo esc_js($public_token); ?>',
                            '<?php echo esc_js($signing_public_key); ?>',
                            {
                                optionsUrl: '<?php echo esc_url_raw($options_url); ?>',
                                inputName: inputName
                            },
                            {
                                fetchOptionsParameters: { security: crafyNonce }
                            }
                        );
                        console.log('[CrafyCAPTCHA Plugin] Widget initialized. Instance:', crafyInstance);
                    } catch (e) {
                        console.error('[CrafyCAPTCHA Plugin] Error during CrafyCAPTCHA.init:', e);
                    }
                };

                if (typeof CrafyCAPTCHA !== 'undefined') {
                    initWidget();
                } else {
                    window.addEventListener('CrafyCAPTCHALoaded', initWidget);
                }
            })();
        </script>
        <?php

        CrafyCaptcha_Core::log('render_widget() finished. Inline script printed.');


    }
}
