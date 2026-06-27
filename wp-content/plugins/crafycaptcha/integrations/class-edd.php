<?php
/**
 * Easy Digital Downloads Integration
 *
 * Adds CrafyCAPTCHA to EDD checkout, login, and registration forms.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CrafyCaptcha_EDD {

    public function __construct() {
        // Checkout Form
        add_action( 'edd_purchase_form_after_user_info', array( 'CrafyCaptcha_Frontend_Injector', 'render_widget' ) );
        add_action( 'edd_checkout_error_checks', array( $this, 'validate_checkout' ), 10, 2 );

        // Registration Form
        add_action( 'edd_register_form_fields_before_submit', array( 'CrafyCaptcha_Frontend_Injector', 'render_widget' ) );
        add_action( 'edd_process_register_form_errors', array( $this, 'validate_edd_forms' ) );

        // Login Form
        add_action( 'edd_login_fields_after', array( 'CrafyCaptcha_Frontend_Injector', 'render_widget' ) );
        add_action( 'edd_process_login_errors', array( $this, 'validate_edd_forms' ) );
    }

    /**
     * Validates the Easy Digital Downloads checkout form.
     * 
     * @param array $valid_data Validated data by EDD.
     * @param array $posted     POST data.
     */
    public function validate_checkout( $valid_data, $posted ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            edd_set_error( 'crafycaptcha_invalid', esc_html__( 'Security Error: Please verify that you are human.', 'crafycaptcha' ) );
        }
    }

    /**
     * Validates EDD login and registration forms.
     */
    public function validate_edd_forms() {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            edd_set_error( 'crafycaptcha_invalid', esc_html__( 'Security Error: Please verify that you are human.', 'crafycaptcha' ) );
        }
    }
}
