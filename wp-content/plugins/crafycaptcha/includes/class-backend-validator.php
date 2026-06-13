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
        add_action( 'lostpassword_post', array( $this, 'validate_lostpassword' ) );
    }

    /**
     * Intercepta el login de WordPress.
     * 
     * @param WP_User|WP_Error|null $user     Objeto WP_User o WP_Error.
     * @param string                $username Nombre de usuario.
     * @param string                $password Contraseña.
     * @return WP_User|WP_Error Objeto de usuario o error de validación.
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
            $message = __( '<strong>ERROR</strong>: Por favor, completa la verificación de seguridad.', 'crafycaptcha' );
            return new WP_Error( 'crafycaptcha_invalid', wp_kses( $message, array( 'strong' => array() ) ) );
        }

        return $user;
    }

    /**
     * Intercepta el registro nativo de WordPress.
     * 
     * @param WP_Error $errors               Errores de registro.
     * @param string   $sanitized_user_login Nombre de usuario sanitizado.
     * @param string   $user_email           Correo electrónico.
     * @return WP_Error Errores actualizados.
     */
    public function validate_registration( $errors, $sanitized_user_login, $user_email ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $message = __( '<strong>ERROR</strong>: Validación de seguridad fallida. Por favor, verifica que eres humano.', 'crafycaptcha' );
            $errors->add( 'crafycaptcha_invalid', wp_kses( $message, array( 'strong' => array() ) ) );
        }
        return $errors;
    }

    /**
     * Intercepta la publicación de comentarios.
     * 
     * @param int $post_id ID del post.
     */
    public function validate_comment( $post_id ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            wp_die(
                esc_html__( 'Error: Validación de seguridad fallida.', 'crafycaptcha' ),
                esc_html__( 'Comentario Rechazado', 'crafycaptcha' ),
                array( 'response' => 403, 'back_link' => true )
            );
        }
    }

    /**
     * Intercepta el formulario de recuperación de contraseña.
     * 
     * @param WP_Error $errors Errores de recuperación pasados por referencia.
     */
    public function validate_lostpassword( $errors ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $message = __( '<strong>ERROR</strong>: Validación de seguridad fallida.', 'crafycaptcha' );
            $errors->add( 'crafycaptcha_invalid', wp_kses( $message, array( 'strong' => array() ) ) );
        }
    }
}
