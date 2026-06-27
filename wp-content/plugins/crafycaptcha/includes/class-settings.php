<?php
/**
 * Settings Management Class
 * 
 * Handles the WordPress admin options page for CrafyCAPTCHA.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class CrafyCaptcha_Settings {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    public function add_admin_menu() {
        add_options_page(
            'CrafyCAPTCHA',
            'CrafyCAPTCHA',
            'manage_options',
            'crafycaptcha',
            array( $this, 'render_options_page' )
        );
    }

    public function admin_notices() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $public_key = get_option( 'crafycaptcha_public_key', '' );
        $secret_key = get_option( 'crafycaptcha_secret_key', '' );

        if ( empty( $public_key ) || empty( $secret_key ) ) {
            $settings_url = admin_url( 'options-general.php?page=crafycaptcha' );
            $message = sprintf(
                /* translators: %s: URL to settings page */
                __( 'The plugin is active but the API keys are not configured. Forms will not be protected until you <a href="%s">complete the configuration</a>.', 'crafycaptcha' ),
                esc_url( $settings_url )
            );
            $allowed_html = array( 'a' => array( 'href' => array() ) );

            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>' . esc_html__( 'CrafyCAPTCHA:', 'crafycaptcha' ) . '</strong> ' . wp_kses( $message, $allowed_html ) . '</p>';
            echo '</div>';
        }
    }

    public function register_settings() {
        register_setting( 'crafycaptcha_options_group', 'crafycaptcha_public_key', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ) );
        
        register_setting( 'crafycaptcha_options_group', 'crafycaptcha_secret_key', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ) );
        
        register_setting( 'crafycaptcha_options_group', 'crafycaptcha_signing_public_key', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ) );

        add_settings_section(
            'crafycaptcha_main_section',
            'API Credentials',
            array( $this, 'render_section_info' ),
            'crafycaptcha'
        );

        add_settings_field(
            'crafycaptcha_public_key',
            'Public Key',
            array( $this, 'render_public_key_field' ),
            'crafycaptcha',
            'crafycaptcha_main_section'
        );

        add_settings_field(
            'crafycaptcha_secret_key',
            'Secret Key',
            array( $this, 'render_secret_key_field' ),
            'crafycaptcha',
            'crafycaptcha_main_section'
        );

        add_settings_field(
            'crafycaptcha_signing_public_key',
            'Signing Public Key',
            array( $this, 'render_signing_public_key_field' ),
            'crafycaptcha',
            'crafycaptcha_main_section'
        );
    }

    public function render_section_info() {
        echo '<p>' . esc_html__( 'Enter your CrafyCAPTCHA account credentials. You can obtain them in your dashboard.', 'crafycaptcha' ) . '</p>';
    }

    public function render_public_key_field() {
        $val = get_option( 'crafycaptcha_public_key', '' );
        echo '<input type="text" name="crafycaptcha_public_key" value="' . esc_attr( $val ) . '" class="regular-text" />';
    }

    public function render_secret_key_field() {
        $val = get_option( 'crafycaptcha_secret_key', '' );
        echo '<input type="password" name="crafycaptcha_secret_key" value="' . esc_attr( $val ) . '" class="regular-text" />';
    }

    public function render_signing_public_key_field() {
        $val = get_option( 'crafycaptcha_signing_public_key', '' );
        echo '<input type="text" name="crafycaptcha_signing_public_key" value="' . esc_attr( $val ) . '" class="regular-text" />';
    }

    public function render_options_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'CrafyCAPTCHA Settings', 'crafycaptcha' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'crafycaptcha_options_group' );
                do_settings_sections( 'crafycaptcha' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
