<?php
/**
 * Plugin Name: CrafyCAPTCHA
 * Plugin URI:  https://captcha.crafy.net/
 * Description: CrafyCAPTCHA integration for WordPress. Protección avanzada contra bots con fricción adaptativa.
 * Version:     1.0.0
 * Author:      CrafyCAPTCHA
 * Author URI:  https://captcha.crafy.net/
 * Text Domain: crafycaptcha
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Cargar el autoloader de Strauss o hacer un fallback al vendor original
$strauss_autoloader = plugin_dir_path( __FILE__ ) . 'includes/vendor-prefixed/autoload.php';
$vendor_autoloader  = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

if ( file_exists( $strauss_autoloader ) ) {
    require_once $strauss_autoloader;
}

if ( file_exists( $vendor_autoloader ) ) {
    require_once $vendor_autoloader;
}

// Cargar clases core
require_once plugin_dir_path( __FILE__ ) . 'includes/class-core.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frontend-injector.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-backend-validator.php';

class CrafyCaptcha_Plugin {

    public function __construct() {
        // Inicializar sistema de configuración (Settings API)
        new CrafyCaptcha_Settings();

        // Inicializar Inyección y Validación base
        new CrafyCaptcha_Frontend_Injector();
        new CrafyCaptcha_Backend_Validator();
        
        // Registrar Endpoints AJAX de WP
        CrafyCaptcha_Core::init_ajax_endpoints();

        // Inicializar integraciones de terceros en el hook plugins_loaded
        add_action( 'plugins_loaded', array( $this, 'load_integrations' ), 99 );
    }

    public function load_integrations() {
        error_log( 'CrafyCAPTCHA: load_integrations() running.' );
        // Integración con WooCommerce
        if ( class_exists( 'WooCommerce' ) ) {
            error_log( 'CrafyCAPTCHA: WooCommerce detected. Loading integration.' );
            require_once plugin_dir_path( __FILE__ ) . 'integrations/class-woocommerce.php';
            new CrafyCaptcha_WooCommerce();
        } else {
            error_log( 'CrafyCAPTCHA: WooCommerce class NOT found.' );
        }

        // Integración con Easy Digital Downloads
        if ( class_exists( 'Easy_Digital_Downloads' ) ) {
            error_log( 'CrafyCAPTCHA: EDD detected. Loading integration.' );
            require_once plugin_dir_path( __FILE__ ) . 'integrations/class-edd.php';
            new CrafyCaptcha_EDD();
        }
    }
}

// Instanciar el plugin
new CrafyCaptcha_Plugin();
