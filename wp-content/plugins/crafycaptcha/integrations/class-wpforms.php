<?php
/**
 * WPForms Integration
 *
 * Adds CrafyCAPTCHA to WPForms forms.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CrafyCaptcha_WPForms {

    public function __construct() {
        // Frontend: Inject widget before the submit button
        add_action( 'wpforms_display_submit_before', array( 'CrafyCaptcha_Frontend_Injector', 'render_widget' ), 10, 2 );

        // Backend: Validate token during submission
        add_action( 'wpforms_process_initial_errors', array( $this, 'validate_submission' ), 10, 2 );
    }

    /**
     * Validates the form submission.
     *
     * @param array $errors    Array of validation errors.
     * @param array $form_data Form data and settings.
     */
    public function validate_submission( $errors, $form_data ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $form_id = absint( $form_data['id'] );
            // Añadimos el error en la clave 'header' para que se muestre arriba del formulario
            wpforms()->process->errors[ $form_id ]['header'] = esc_html__( 'Security Error: Please verify that you are human.', 'crafycaptcha' );
        }
    }
}
