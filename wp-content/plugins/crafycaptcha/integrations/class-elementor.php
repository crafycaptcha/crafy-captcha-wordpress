<?php
/**
 * Elementor Pro Forms Integration
 *
 * Adds CrafyCAPTCHA to Elementor Pro forms.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CrafyCaptcha_Elementor {

    public function __construct() {
        // Frontend: Inject widget during field rendering
        add_action( 'elementor_pro/forms/render_field', array( $this, 'inject_widget' ), 10, 3 );

        // Backend: Validate token during submission
        add_action( 'elementor_pro/forms/validation', array( $this, 'validate_submission' ), 10, 2 );
    }

    /**
     * Injects the CrafyCAPTCHA widget after the last form field.
     *
     * @param array  $item       The field item settings.
     * @param int    $item_index The index of the current field.
     * @param object $widget     The Elementor form widget instance.
     */
    public function inject_widget( $item, $item_index, $widget ) {
        // Obtenemos todos los campos para saber cuál es el último
        $fields = $widget->get_settings_for_display( 'form_fields' );
        
        // Si no se pueden obtener, usamos un flag estático para inyectarlo en el primer campo (fallback)
        if ( ! is_array( $fields ) ) {
            static $rendered_fallback = array();
            $form_id = $widget->get_id();
            if ( ! isset( $rendered_fallback[ $form_id ] ) ) {
                $rendered_fallback[ $form_id ] = true;
                CrafyCaptcha_Frontend_Injector::render_widget();
            }
            return;
        }

        // Si es el último campo, inyectamos el widget para que quede cerca del submit
        if ( $item_index === ( count( $fields ) - 1 ) ) {
            CrafyCaptcha_Frontend_Injector::render_widget();
        }
    }

    /**
     * Validates the form submission.
     *
     * @param object $record       The form record object.
     * @param object $ajax_handler The Elementor ajax handler.
     */
    public function validate_submission( $record, $ajax_handler ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            // Añadimos el error general de validación
            $ajax_handler->add_error( 'crafycaptcha', esc_html__( 'Security Error: Please verify that you are human.', 'crafycaptcha' ) );
        }
    }
}
