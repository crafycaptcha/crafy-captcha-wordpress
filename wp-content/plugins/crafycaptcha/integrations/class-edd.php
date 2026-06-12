<?php
/**
 * Easy Digital Downloads Integration
 *
 * Añade CrafyCAPTCHA a los formularios de checkout de EDD.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CrafyCaptcha_EDD {

    public function __construct() {
        // Frontend: Insertar antes de finalizar compra
        add_action( 'edd_purchase_form_after_user_info', array( 'CrafyCaptcha_Frontend_Injector', 'render_widget' ) );
        
        // Backend: Validar antes de procesar el pago
        add_action( 'edd_checkout_error_checks', array( $this, 'validate_checkout' ), 10, 2 );
    }

    public function validate_checkout( $valid_data, $posted ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            edd_set_error( 'crafycaptcha_invalid', __( 'Error de seguridad: Por favor verifica que eres humano.', 'crafycaptcha' ) );
        }
    }
}
