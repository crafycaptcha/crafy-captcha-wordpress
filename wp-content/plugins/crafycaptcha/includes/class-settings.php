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

    public function register_settings() {
        register_setting( 'crafycaptcha_options_group', 'crafycaptcha_public_key' );
        register_setting( 'crafycaptcha_options_group', 'crafycaptcha_secret_key' );
        register_setting( 'crafycaptcha_options_group', 'crafycaptcha_signing_public_key' );

        add_settings_section(
            'crafycaptcha_main_section',
            'Credenciales de API',
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
        echo '<p>Introduce las credenciales de tu cuenta de CrafyCAPTCHA. Puedes obtenerlas en tu panel de control.</p>';
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
            <h1>Configuración de CrafyCAPTCHA</h1>
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
