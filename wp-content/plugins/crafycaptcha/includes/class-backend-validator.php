<?php
/**
 * Backend Validator Class
 * 
 * Se encarga de interceptar y validar los envíos de formularios.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CrafyCaptcha_Backend_Validator {

    public function __construct() {
        // Prioridad 30 en authenticate para asegurar que otras verificaciones básicas se hagan primero
        add_filter( 'authenticate', array( $this, 'validate_login' ), 30, 3 );
        add_filter( 'registration_errors', array( $this, 'validate_registration' ), 10, 3 );
        add_action( 'pre_comment_on_post', array( $this, 'validate_comment' ) );
    }

    /**
     * Intercepta el login de WordPress.
     */
    public function validate_login( $user, $username, $password ) {
        // Ignorar si no se ingresó usuario o password
        if ( empty( $username ) || empty( $password ) ) {
            return $user;
        }

        // Excluir validación si es una petición XML-RPC, REST, o background
        if ( defined( 'XMLRPC_REQUEST' ) || defined( 'REST_REQUEST' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            return $user;
        }

        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            return new WP_Error( 'crafycaptcha_invalid', __( '<strong>ERROR</strong>: Por favor, completa la verificación de seguridad.', 'crafycaptcha' ) );
        }

        return $user;
    }

    /**
     * Intercepta el registro nativo de WordPress.
     */
    public function validate_registration( $errors, $sanitized_user_login, $user_email ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $errors->add( 'crafycaptcha_invalid', __( '<strong>ERROR</strong>: Validación de seguridad fallida. Por favor, verifica que eres humano.', 'crafycaptcha' ) );
        }
        return $errors;
    }

    /**
     * Intercepta la publicación de comentarios.
     */
    public function validate_comment( $post_id ) {
        // Los usuarios logueados normalmente no necesitan resolver captchas en los comentarios
        if ( is_user_logged_in() ) {
            return;
        }

        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            wp_die(
                __( 'Error: Validación de seguridad fallida.', 'crafycaptcha' ),
                __( 'Comentario Rechazado', 'crafycaptcha' ),
                array( 'response' => 403, 'back_link' => true )
            );
        }
    }
}
