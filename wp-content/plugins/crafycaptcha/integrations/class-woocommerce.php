<?php
/**
 * WooCommerce Integration
 *
 * Añade CrafyCAPTCHA a los formularios de login, registro y checkout de WooCommerce.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CrafyCaptcha_WooCommerce {

    public function __construct() {
        // Inyección en Frontend
        $hooks = array(
            'woocommerce_login_form',
            'woocommerce_register_form',
            'woocommerce_review_order_before_submit'
        );

        foreach ( $hooks as $hook ) {
            add_action( $hook, array( 'CrafyCaptcha_Frontend_Injector', 'render_widget' ) );
        }

        // Validación en Backend
        add_filter( 'woocommerce_process_login_errors', array( $this, 'validate_login_register' ), 10, 3 );
        add_filter( 'woocommerce_process_registration_errors', array( $this, 'validate_login_register' ), 10, 3 );
        add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_checkout' ), 10, 2 );
    }

    public function validate_login_register( $errors, $username = '', $password = '' ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $errors->add( 'crafycaptcha_invalid', __( '<strong>ERROR</strong>: Validación de seguridad fallida. Por favor, verifica que eres humano.', 'crafycaptcha' ) );
        }
        return $errors;
    }

    public function validate_checkout( $data, $errors ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $errors->add( 'crafycaptcha_invalid', __( '<strong>ERROR</strong>: Validación de seguridad fallida. Por favor, verifica que eres humano.', 'crafycaptcha' ) );
        }
    }
}
