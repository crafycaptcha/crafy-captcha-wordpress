<?php
/**
 * Contact Form 7 Integration
 *
 * Adds CrafyCAPTCHA to Contact Form 7 forms.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CrafyCaptcha_CF7 {

    public function __construct() {
        // Frontend: Inject widget HTML before the submit button or closing form tag
        add_filter( 'wpcf7_form_elements', array( $this, 'inject_widget' ), 10, 1 );

        // Backend: Validate token during submission
        add_filter( 'wpcf7_spam', array( $this, 'validate_spam' ), 10, 1 );
    }

    /**
     * Injects the CrafyCAPTCHA widget into the CF7 form HTML.
     *
     * @param string $form_html The form HTML.
     * @return string Modified form HTML.
     */
    public function inject_widget( $form_html ) {
        // Obtenemos el widget HTML usando ob_start ya que render_widget hace echo.
        ob_start();
        CrafyCaptcha_Frontend_Injector::render_widget();
        $widget_html = ob_get_clean();

        // Si no se generó el widget (ej. no keys), retornar original.
        if ( empty( $widget_html ) ) {
            return $form_html;
        }

        // Intentar insertarlo justo antes del botón [submit] si es posible.
        // Los submits en CF7 normalmente se parsean a input type="submit".
        // Sin embargo, wpcf7_form_elements se ejecuta DESPUÉS de que se procesan los shortcodes,
        // por lo que el HTML contiene elementos <input type="submit"...>
        if ( strpos( $form_html, '<input type="submit"' ) !== false ) {
            $form_html = preg_replace( '/(<input[^>]*type="submit"[^>]*>)/i', $widget_html . '$1', $form_html, 1 );
        } else {
            // Fallback: agregarlo al final.
            $form_html .= $widget_html;
        }

        return $form_html;
    }

    /**
     * Validates the submission to check if it's spam (invalid token).
     *
     * @param bool $spam Whether it's already marked as spam.
     * @return bool True if spam, false otherwise.
     */
    public function validate_spam( $spam ) {
        // Si ya está marcado como spam por otra validación, lo respetamos.
        if ( $spam ) {
            return $spam;
        }

        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            // Al retornar true, CF7 marca el mensaje como spam.
            return true;
        }

        return $spam;
    }
}
