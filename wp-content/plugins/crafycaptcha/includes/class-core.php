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
     * Helper de logging seguro
     */
    public static function log( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'CrafyCAPTCHA: ' . $message );
        }
    }

    /**
     * Initializes and returns the CrafyCAPTCHA SDK instance.
     */
    public static function get_instance() {
        if ( self::$crafy === null ) {
            $public_key = get_option( 'crafycaptcha_public_key', '' );
            $secret_key = get_option( 'crafycaptcha_secret_key', '' );

            if ( empty( $public_key ) || empty( $secret_key ) ) {
                self::log( 'get_instance() aborted - public_key or secret_key is empty.' );
                return null;
            }

            try {
                // Compatibilidad con Strauss (namespace prefijado) o fallback al original
                if ( class_exists( '\\CrafyCaptcha\\Dependencies\\Crafy\\Captcha\\CrafyCAPTCHA' ) ) {
                    self::log( 'using Strauss vendor prefix class.' );
                    self::$crafy = new \CrafyCaptcha\Dependencies\Crafy\Captcha\CrafyCAPTCHA( $public_key, $secret_key );
                } elseif ( class_exists( '\\Crafy\\Captcha\\CrafyCAPTCHA' ) ) {
                    self::log( 'using standard vendor class.' );
                    self::$crafy = new \Crafy\Captcha\CrafyCAPTCHA( $public_key, $secret_key );
                } else {
                    self::log( 'Init Error - Class CrafyCAPTCHA not found!' );
                }
            } catch ( Exception $e ) {
                self::log( 'Init Error: ' . $e->getMessage() );
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
        // Leer el body de la petición JSON
        $body = file_get_contents('php://input');
        $json = json_decode($body, true);

        // Verificar el Nonce
        if ( ! is_array( $json ) || ! isset( $json['security'] ) || ! is_string( $json['security'] ) ) {
            wp_send_json_error( array( 'message' => 'Petición inválida o malformada.' ) );
        }

        $nonce = sanitize_text_field( $json['security'] );
        if ( ! wp_verify_nonce( $nonce, 'crafycaptcha_options_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'CSRF Token inválido o expirado.' ) );
        }

        $crafy = self::get_instance();
        
        if ( ! $crafy ) {
            wp_send_json_error( array( 'message' => 'CrafyCAPTCHA no está configurado correctamente.' ) );
        }

        try {
            $encrypted_options = $crafy->createFlow( array( 'mode' => 'auto' ) );
            wp_send_json( array( 'eo' => $encrypted_options ) );
        } catch ( Exception $e ) {
            self::log( 'Flow Error: ' . $e->getMessage() );
            wp_send_json_error( array( 'message' => 'Error creando el flujo de seguridad.' ) );
        }
    }

    private static $token_validation_cache = array();

    /**
     * Valida el token del request actual.
     */
    public static function is_token_valid() {
        self::log( 'is_token_valid() called.' );

        $raw_token = null;
        $found_key = '';

        foreach ( $_POST as $key => $value ) {
            if ( strpos( $key, 'crafycaptcha_token' ) === 0 && is_string( $value ) ) {
                $raw_token = $value;
                $found_key = $key;
                break;
            }
        }

        if ( $raw_token === null ) {
            self::log( 'is_token_valid() - No key starting with crafycaptcha_token found in $_POST.' );
            self::log( '$_POST keys: ' . implode(', ', array_keys($_POST)) );
            return false;
        }

        $token = sanitize_text_field( wp_unslash( $raw_token ) );

        if ( empty( $token ) ) {
            self::log( 'is_token_valid() - Token is EMPTY after sanitization.' );
            return false;
        }

        // Si ya verificamos este token en esta misma petición, devolvemos el resultado en caché.
        if ( isset( self::$token_validation_cache[ $token ] ) ) {
            self::log( 'is_token_valid() - Token result loaded from cache: ' . ( self::$token_validation_cache[ $token ] ? 'true' : 'false' ) );
            return self::$token_validation_cache[ $token ];
        }

        $crafy = self::get_instance();
        
        if ( ! $crafy ) {
            self::log( 'is_token_valid() - get_instance() returned null.' );
            // Fail-closed: si el SDK no puede inicializarse, NO permitir bypass
            $public_key = get_option( 'crafycaptcha_public_key', '' );
            $secret_key = get_option( 'crafycaptcha_secret_key', '' );
            if ( empty( $public_key ) && empty( $secret_key ) ) {
                self::log( 'is_token_valid() - Both keys empty. Allowing bypass.' );
                return true; // Plugin no configurado, no bloquear
            }
            return false; // Plugin configurado pero SDK falló, bloquear
        }

        self::log( 'is_token_valid() - Token found. Length: ' . strlen( $token ) );

        try {
            $is_valid = $crafy->verifyFlow( $token );
            self::log( 'is_token_valid() - verifyFlow returned: ' . ( $is_valid ? 'true' : 'false' ) );
            self::$token_validation_cache[ $token ] = $is_valid;
            return $is_valid;
        } catch ( Exception $e ) {
            self::log( 'Verify Error: ' . $e->getMessage() );
            self::$token_validation_cache[ $token ] = false;
            return false;
        }
    }
}
