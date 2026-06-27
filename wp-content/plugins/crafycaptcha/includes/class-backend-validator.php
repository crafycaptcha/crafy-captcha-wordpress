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
        // Priority 30 in authenticate to ensure other basic verifications run first
        add_filter( 'authenticate', array( $this, 'validate_login' ), 30, 3 );
        add_filter( 'registration_errors', array( $this, 'validate_registration' ), 10, 3 );
        add_action( 'pre_comment_on_post', array( $this, 'validate_comment' ) );
        add_action( 'lostpassword_post', array( $this, 'validate_lostpassword_post' ), 10, 1 );
        add_filter( 'allow_password_reset', array( $this, 'validate_lostpassword' ), 10, 2 );
    }

    /**
     * Intercepts WordPress login.
     * 
     * @param WP_User|WP_Error|null $user     WP_User or WP_Error object.
     * @param string                $username Username.
     * @param string                $password Password.
     * @return WP_User|WP_Error User object or validation error.
     */
    public function validate_login( $user, $username, $password ) {
        // Ignore if username or password not provided
        if ( empty( $username ) || empty( $password ) ) {
            return $user;
        }

        // Exclude validation if it's an XML-RPC, REST, or background request
        if ( defined( 'XMLRPC_REQUEST' ) || defined( 'REST_REQUEST' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            return $user;
        }

        // Skip if WooCommerce handles its own login validation
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( isset( $_POST['woocommerce-login-nonce'] ) ) {
            return $user;
        }

        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $message = __( '<strong>ERROR</strong>: Please complete the security verification.', 'crafycaptcha' );
            return new WP_Error( 'crafycaptcha_invalid', wp_kses( $message, array( 'strong' => array() ) ) );
        }

        return $user;
    }

    /**
     * Intercepts native WordPress registration.
     * 
     * @param WP_Error $errors               Registration errors.
     * @param string   $sanitized_user_login Sanitized username.
     * @param string   $user_email           Email address.
     * @return WP_Error Updated errors.
     */
    public function validate_registration( $errors, $sanitized_user_login, $user_email ) {
        // Skip if WooCommerce handles its own registration validation
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( isset( $_POST['woocommerce-register-nonce'] ) ) {
            return $errors;
        }

        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $message = __( '<strong>ERROR</strong>: Security validation failed. Please verify that you are human.', 'crafycaptcha' );
            $errors->add( 'crafycaptcha_invalid', wp_kses( $message, array( 'strong' => array() ) ) );
        }
        return $errors;
    }

    /**
     * Intercepts comment posting.
     * 
     * @param int $post_id Post ID.
     */
    public function validate_comment( $post_id ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            wp_die(
                esc_html__( 'Error: Security validation failed.', 'crafycaptcha' ),
                esc_html__( 'Comment Rejected', 'crafycaptcha' ),
                array( 'response' => 403, 'back_link' => true )
            );
        }
    }

    /**
     * Intercepts password recovery form (Primary Hook).
     * 
     * @param WP_Error $errors Errors object.
     */
    public function validate_lostpassword_post( $errors ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $message = __( '<strong>ERROR</strong>: Security validation failed.', 'crafycaptcha' );
            $errors->add( 'crafycaptcha_invalid', wp_kses( $message, array( 'strong' => array() ) ) );
        }
    }

    /**
     * Intercepts password recovery form.
     * 
     * @param bool|WP_Error $allow Whether to allow the password reset.
     * @param int           $user_id User ID.
     * @return bool|WP_Error
     */
    public function validate_lostpassword( $allow, $user_id ) {
        if ( is_wp_error( $allow ) ) {
            return $allow;
        }

        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $message = __( '<strong>ERROR</strong>: Security validation failed.', 'crafycaptcha' );
            return new WP_Error( 'crafycaptcha_invalid', wp_kses( $message, array( 'strong' => array() ) ) );
        }
        
        return $allow;
    }
}
