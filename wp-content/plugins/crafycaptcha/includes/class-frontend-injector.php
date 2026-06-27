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
            CRAFYCAPTCHA_JS_URL,
            array(),
            null,
            true
        );

    }

    /**
     * Añade atributos integrity y crossorigin al script del SDK.
     */
    public static function add_script_attributes($tag, $handle)
    {
        if ('crafycaptcha-js' === $handle) {
            return str_replace(
                ' src',
                ' integrity="' . esc_attr(CRAFYCAPTCHA_JS_INTEGRITY) . '" crossorigin="anonymous" src',
                $tag
            );
        }
        return $tag;
    }


    /**
     * Renderiza el contenedor HTML y el script de inicialización inline.
     */
    public static function render_widget()
    {

        CrafyCaptcha_Core::log('render_widget() called.');

        if ( is_admin() && ! wp_doing_ajax() ) {
            return;
        }

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

        static $cached_public_token = null;
        if ( $cached_public_token === null ) {
            try {
                $cached_public_token = $crafy->getPublicToken();
            } catch (Exception $e) {
                CrafyCaptcha_Core::log('render_widget() Error getting public token: ' . $e->getMessage());
                echo '<div class="crafycaptcha-error" style="color: #666; padding: 10px; margin-bottom: 15px;">';
                echo esc_html__('The security service is temporarily unavailable.', 'crafycaptcha');
                echo '</div>';
                return;
            }
        }
        $public_token = $cached_public_token;

        CrafyCaptcha_Core::log('render_widget() proceeding to render.');
        $signing_public_key = get_option('crafycaptcha_signing_public_key', '');
        $options_url = admin_url('admin-ajax.php?action=crafycaptcha_options');

        $container_id = 'crafycaptcha_' . uniqid();
        $input_name = 'crafycaptcha_token_' . uniqid();
        ?>
        <div id="<?php echo esc_attr($container_id); ?>" class="crafycaptcha-container" style="margin-bottom: 15px;"></div>
        <?php

        $nonce = wp_create_nonce('crafycaptcha_options_nonce');

        $inline_js = '(function () {
            const CRAFY_DEBUG = false;
            var crafyNonce = "' . esc_js($nonce) . '";
            const containerId = "' . esc_js($container_id) . '";
            const inputName = "' . esc_js($input_name) . '";
            let initialized = false;

            const initWidget = () => {
                if (initialized) return;
                initialized = true;
                if (CRAFY_DEBUG) console.log("[CrafyCAPTCHA Plugin] Initializing widget for container:", containerId, "with inputName:", inputName);
                try {
                    CrafyCAPTCHA.setAutoLoad(false);
                    var crafyInstance = CrafyCAPTCHA.init(
                        containerId,
                        "' . esc_js($public_key) . '",
                        "' . esc_js($public_token) . '",
                        "' . esc_js($signing_public_key) . '",
                        {
                            optionsUrl: "' . esc_url($options_url) . '",
                            inputName: inputName
                        },
                        {
                            fetchOptionsParameters: { security: crafyNonce }
                        }
                    );
                    if (CRAFY_DEBUG) console.log("[CrafyCAPTCHA Plugin] Widget initialized. Instance:", crafyInstance);
                } catch (e) {
                    if (CRAFY_DEBUG) console.error("[CrafyCAPTCHA Plugin] Error during CrafyCAPTCHA.init:", e);
                }
            };

            if (typeof CrafyCAPTCHA !== "undefined") {
                initWidget();
            } else {
                window.addEventListener("CrafyCAPTCHALoaded", initWidget);
            }
        })();';

        wp_print_inline_script_tag( $inline_js );

        CrafyCaptcha_Core::log('render_widget() finished. Inline script printed.');


    }
}
