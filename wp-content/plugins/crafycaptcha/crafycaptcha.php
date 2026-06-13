<?php
/**
 * Plugin Name: CrafyCAPTCHA
 * Plugin URI:  https://captcha.crafy.net/
 * Description: CrafyCAPTCHA integration for WordPress. Protección avanzada contra bots con fricción adaptativa.
 * Version:     1.0.0
 * Author:      CrafyCAPTCHA
 * Author URI:  https://captcha.crafy.net/
 * Text Domain: crafycaptcha
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CRAFYCAPTCHA_VERSION', '1.0.0' );
define( 'CRAFYCAPTCHA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CRAFYCAPTCHA_PLUGIN_FILE', __FILE__ );

// Cargar el autoloader de Strauss o hacer un fallback al vendor original
$strauss_autoloader = CRAFYCAPTCHA_PLUGIN_DIR . 'includes/vendor-prefixed/autoload.php';
$vendor_autoloader  = CRAFYCAPTCHA_PLUGIN_DIR . 'vendor/autoload.php';

if ( file_exists( $strauss_autoloader ) ) {
    require_once $strauss_autoloader;
}

if ( file_exists( $vendor_autoloader ) ) {
    require_once $vendor_autoloader;
}

// Cargar clases core
require_once CRAFYCAPTCHA_PLUGIN_DIR . 'includes/class-core.php';
require_once CRAFYCAPTCHA_PLUGIN_DIR . 'includes/class-settings.php';
require_once CRAFYCAPTCHA_PLUGIN_DIR . 'includes/class-frontend-injector.php';
require_once CRAFYCAPTCHA_PLUGIN_DIR . 'includes/class-backend-validator.php';

class CrafyCaptcha_Plugin {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Init textdomain
        add_action( 'init', array( $this, 'load_textdomain' ) );

        // Inicializar sistema de configuración (Settings API)
        new CrafyCaptcha_Settings();

        // Inicializar Inyección y Validación base
        new CrafyCaptcha_Frontend_Injector();
        new CrafyCaptcha_Backend_Validator();
        
        // Registrar Endpoints AJAX de WP
        CrafyCaptcha_Core::init_ajax_endpoints();

        // Inicializar integraciones de terceros
        add_action( 'init', array( $this, 'load_integrations' ), 0 );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'crafycaptcha', false, dirname( plugin_basename( CRAFYCAPTCHA_PLUGIN_FILE ) ) . '/languages' );
    }

    public function load_integrations() {
        CrafyCaptcha_Core::log( 'load_integrations() running.' );
        // Integración con WooCommerce
        if ( class_exists( 'WooCommerce' ) ) {
            CrafyCaptcha_Core::log( 'WooCommerce detected. Loading integration.' );
            require_once CRAFYCAPTCHA_PLUGIN_DIR . 'integrations/class-woocommerce.php';
            new CrafyCaptcha_WooCommerce();
        } else {
            CrafyCaptcha_Core::log( 'WooCommerce class NOT found.' );
        }

        // Integración con Easy Digital Downloads
        if ( class_exists( 'Easy_Digital_Downloads' ) ) {
            CrafyCaptcha_Core::log( 'EDD detected. Loading integration.' );
            require_once CRAFYCAPTCHA_PLUGIN_DIR . 'integrations/class-edd.php';
            new CrafyCaptcha_EDD();
        }
    }

    public static function activate() {
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            wp_die( esc_html__( 'CrafyCAPTCHA requiere PHP 7.4 o superior.', 'crafycaptcha' ) );
        }
    }

    public static function deactivate() {
        // Cleanup en deactivation si se requiere
    }
}

// Hooks de activación y desactivación
register_activation_hook( __FILE__, array( 'CrafyCaptcha_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'CrafyCaptcha_Plugin', 'deactivate' ) );

// Instanciar el plugin mediante Singleton en plugins_loaded
add_action( 'plugins_loaded', array( 'CrafyCaptcha_Plugin', 'instance' ), 10 );
