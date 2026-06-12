<?php
/**
 * Core utility class for CrafyCAPTCHA
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CrafyCaptcha_Core {

    private static $crafy = null;

    /**
     * Initializes and returns the CrafyCAPTCHA SDK instance.
     */
    public static function get_instance() {
        if ( self::$crafy === null ) {
            $public_key = get_option( 'crafycaptcha_public_key', '' );
            $secret_key = get_option( 'crafycaptcha_secret_key', '' );

            if ( empty( $public_key ) || empty( $secret_key ) ) {
                error_log( 'CrafyCAPTCHA: get_instance() aborted - public_key or secret_key is empty.' );
                return null;
            }

            try {
                // Compatibilidad con Strauss (namespace prefijado) o fallback al original
                if ( class_exists( '\\CrafyCaptcha\\Dependencies\\Crafy\\Captcha\\CrafyCAPTCHA' ) ) {
                    error_log( 'CrafyCAPTCHA: using Strauss vendor prefix class.' );
                    self::$crafy = new \CrafyCaptcha\Dependencies\Crafy\Captcha\CrafyCAPTCHA( $public_key, $secret_key );
                } elseif ( class_exists( '\\Crafy\\Captcha\\CrafyCAPTCHA' ) ) {
                    error_log( 'CrafyCAPTCHA: using standard vendor class.' );
                    self::$crafy = new \Crafy\Captcha\CrafyCAPTCHA( $public_key, $secret_key );
                } else {
                    error_log( 'CrafyCAPTCHA: Init Error - Class CrafyCAPTCHA not found!' );
                }
            } catch ( Exception $e ) {
                error_log( 'CrafyCAPTCHA Init Error: ' . $e->getMessage() );
                return null;
            }
        }
        return self::$crafy;
    }

    /**
     * Registra los endpoints AJAX necesarios para el widget frontend.
     */
    public static function init_ajax_endpoints() {
        add_action( 'wp_ajax_crafycaptcha_options', array( __CLASS__, 'get_options' ) );
        add_action( 'wp_ajax_nopriv_crafycaptcha_options', array( __CLASS__, 'get_options' ) );
    }

    /**
     * Retorna las opciones cifradas requeridas por el Frontend SDK.
     */
    public static function get_options() {
        $crafy = self::get_instance();
        
        if ( ! $crafy ) {
            wp_send_json_error( array( 'message' => 'CrafyCAPTCHA no está configurado correctamente.' ) );
        }

        try {
            $encrypted_options = $crafy->createFlow( array( 'mode' => 'auto' ) );
            wp_send_json( array( 'eo' => $encrypted_options ) );
        } catch ( Exception $e ) {
            error_log( 'CrafyCAPTCHA Flow Error: ' . $e->getMessage() );
            wp_send_json_error( array( 'message' => 'Error creando el flujo de seguridad.' ) );
        }
    }

    /**
     * Valida el token del request actual.
     */
    public static function is_token_valid() {
        $crafy = self::get_instance();
        
        if ( ! $crafy ) {
            return true;
        }

        $token = isset( $_POST['crafycaptcha_token'] ) ? sanitize_text_field( $_POST['crafycaptcha_token'] ) : '';

        if ( empty( $token ) ) {
            return false;
        }

        try {
            return $crafy->verifyFlow( $token );
        } catch ( Exception $e ) {
            error_log( 'CrafyCAPTCHA Verify Error: ' . $e->getMessage() );
            return false;
        }
    }
}
