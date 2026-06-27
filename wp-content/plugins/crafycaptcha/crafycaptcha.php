<?php
/**
 * Plugin Name: CrafyCAPTCHA
 * Plugin URI:  https://captcha.crafy.net/
 * Description: CrafyCAPTCHA integration for WordPress. Advanced bot protection with adaptive friction.
 * Version:     1.0.0
 * Author:      CrafyHolding
 * Text Domain: crafycaptcha
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CRAFYCAPTCHA_VERSION', '1.0.0');
define('CRAFYCAPTCHA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CRAFYCAPTCHA_PLUGIN_FILE', __FILE__);
define('CRAFYCAPTCHA_JS_URL', 'https://captcha.crafy.net/cdn-js/1.1.7.js');
define('CRAFYCAPTCHA_JS_INTEGRITY', 'sha384-ij1DhfbKim6bT2zXcO46f/BJU4ucV6v0ajMv9NqZ/j2qi99huo54M8A4EhChcxnP');

// Cargar el autoloader de Strauss o hacer un fallback al vendor original
$crafycaptcha_strauss_autoloader = CRAFYCAPTCHA_PLUGIN_DIR . 'includes/vendor-prefixed/autoload.php';
$crafycaptcha_vendor_autoloader = CRAFYCAPTCHA_PLUGIN_DIR . 'vendor/autoload.php';

if (file_exists($crafycaptcha_strauss_autoloader)) {
    require_once $crafycaptcha_strauss_autoloader;
}

if (file_exists($crafycaptcha_vendor_autoloader)) {
    require_once $crafycaptcha_vendor_autoloader;
}

// Cargar clases core
require_once CRAFYCAPTCHA_PLUGIN_DIR . 'includes/class-core.php';
require_once CRAFYCAPTCHA_PLUGIN_DIR . 'includes/class-settings.php';
require_once CRAFYCAPTCHA_PLUGIN_DIR . 'includes/class-frontend-injector.php';
require_once CRAFYCAPTCHA_PLUGIN_DIR . 'includes/class-backend-validator.php';

class CrafyCaptcha_Plugin
{

    private static $instance = null;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Textdomain load removed (handled by WordPress automatically since 4.6)

        // Inicializar sistema de configuración (Settings API)
        new CrafyCaptcha_Settings();

        // Inicializar Inyección y Validación base
        new CrafyCaptcha_Frontend_Injector();
        new CrafyCaptcha_Backend_Validator();

        // Registrar Endpoints AJAX de WP
        CrafyCaptcha_Core::init_ajax_endpoints();

        // Inicializar integraciones de terceros
        add_action('init', array($this, 'load_integrations'), 0);
    }


    public function load_integrations()
    {
        CrafyCaptcha_Core::log('load_integrations() running.');
        // Integración con WooCommerce
        if (class_exists('WooCommerce')) {
            CrafyCaptcha_Core::log('WooCommerce detected. Loading integration.');
            require_once CRAFYCAPTCHA_PLUGIN_DIR . 'integrations/class-woocommerce.php';
            new CrafyCaptcha_WooCommerce();
        } else {
            CrafyCaptcha_Core::log('WooCommerce class NOT found.');
        }

        // Integración con Easy Digital Downloads
        if (class_exists('Easy_Digital_Downloads')) {
            CrafyCaptcha_Core::log('EDD detected. Loading integration.');
            require_once CRAFYCAPTCHA_PLUGIN_DIR . 'integrations/class-edd.php';
            new CrafyCaptcha_EDD();
        }

        // Integración con Contact Form 7
        if (defined('WPCF7_VERSION')) {
            CrafyCaptcha_Core::log('Contact Form 7 detected. Loading integration.');
            require_once CRAFYCAPTCHA_PLUGIN_DIR . 'integrations/class-cf7.php';
            new CrafyCaptcha_CF7();
        }

        // Integración con WPForms
        if (function_exists('wpforms')) {
            CrafyCaptcha_Core::log('WPForms detected. Loading integration.');
            require_once CRAFYCAPTCHA_PLUGIN_DIR . 'integrations/class-wpforms.php';
            new CrafyCaptcha_WPForms();
        }

        // Integración con Gravity Forms
        if (class_exists('GFForms')) {
            CrafyCaptcha_Core::log('Gravity Forms detected. Loading integration.');
            require_once CRAFYCAPTCHA_PLUGIN_DIR . 'integrations/class-gravityforms.php';
            new CrafyCaptcha_GravityForms();
        }

        // Integración con Elementor Pro Forms
        if (did_action('elementor_pro/init')) {
            CrafyCaptcha_Core::log('Elementor Pro detected. Loading integration.');
            require_once CRAFYCAPTCHA_PLUGIN_DIR . 'integrations/class-elementor.php';
            new CrafyCaptcha_Elementor();
        }
    }

    public static function activate()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            wp_die(esc_html__('CrafyCAPTCHA requires PHP 7.4 or higher.', 'crafycaptcha'));
        }
    }

    public static function deactivate()
    {
        // Cleanup en deactivation si se requiere
    }
}

// Hooks de activación y desactivación
register_activation_hook(__FILE__, array('CrafyCaptcha_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('CrafyCaptcha_Plugin', 'deactivate'));

// Instanciar el plugin mediante Singleton en plugins_loaded
add_action('plugins_loaded', array('CrafyCaptcha_Plugin', 'instance'), 10);
