<?php
/**
 * Gravity Forms Integration
 *
 * Adds CrafyCAPTCHA to Gravity Forms.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CrafyCaptcha_GravityForms {

    public function __construct() {
        // Frontend: Inject widget HTML into the submit button
        add_filter( 'gform_submit_button', array( $this, 'inject_widget' ), 10, 2 );

        // Backend: Validate token during submission
        add_filter( 'gform_validation', array( $this, 'validate_submission' ) );
    }

    /**
     * Injects the CrafyCAPTCHA widget before the submit button.
     *
     * @param string $button HTML string of the submit button.
     * @param array  $form   The form object.
     * @return string Modified HTML.
     */
    public function inject_widget( $button, $form ) {
        ob_start();
        CrafyCaptcha_Frontend_Injector::render_widget();
        $widget_html = ob_get_clean();

        return $widget_html . $button;
    }

    /**
     * Validates the form submission.
     *
     * @param array $validation_result Array containing 'is_valid' and 'form'.
     * @return array Modified validation result.
     */
    public function validate_submission( $validation_result ) {
        // Skip validation if already failed
        if ( ! $validation_result['is_valid'] ) {
            return $validation_result;
        }

        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $validation_result['is_valid'] = false;
            
            // Add a global error message
            add_filter( 'gform_validation_message', array( $this, 'validation_message' ), 10, 2 );
        }

        return $validation_result;
    }

    /**
     * Filters the global validation message for Gravity Forms.
     *
     * @param string $message The original validation message.
     * @param array  $form    The form object.
     * @return string Modified validation message.
     */
    public function validation_message( $message, $form ) {
        return '<div class="validation_error">' . esc_html__( 'Security Error: Please verify that you are human.', 'crafycaptcha' ) . '</div>';
    }
}
