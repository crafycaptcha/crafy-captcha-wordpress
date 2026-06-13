<?php
/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Limpiar las opciones de la base de datos
delete_option( 'crafycaptcha_public_key' );
delete_option( 'crafycaptcha_secret_key' );
delete_option( 'crafycaptcha_signing_public_key' );
