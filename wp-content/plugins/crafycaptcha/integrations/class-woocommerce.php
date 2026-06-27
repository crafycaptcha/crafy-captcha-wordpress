<?php
/**
 * WooCommerce Integration
 *
 * Añade CrafyCAPTCHA a los formularios de login, registro y checkout de WooCommerce.
 * Soporta tanto el checkout clásico (shortcode) como el checkout por bloques (Gutenberg).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CrafyCaptcha_WooCommerce {

    public function __construct() {
        // ─── Hooks para formularios clásicos de WooCommerce (login, registro) ───
        add_action( 'woocommerce_login_form', array( 'CrafyCaptcha_Frontend_Injector', 'render_widget' ) );
        add_action( 'woocommerce_register_form', array( 'CrafyCaptcha_Frontend_Injector', 'render_widget' ) );
        add_action( 'woocommerce_lostpassword_form', array( 'CrafyCaptcha_Frontend_Injector', 'render_widget' ) );

        // ─── Checkout Clásico (shortcode [woocommerce_checkout]) ───
        add_action( 'woocommerce_review_order_before_submit', array( 'CrafyCaptcha_Frontend_Injector', 'render_widget' ) );
        add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_checkout_classic' ), 10, 2 );

        // ─── Checkout por Bloques (wp:woocommerce/checkout) ───
        // Inyectar el widget HTML antes del botón "Realizar pedido"
        add_filter( 'render_block_woocommerce/checkout-actions-block', array( $this, 'inject_widget_in_checkout_block' ), 10, 1 );

        // Validación server-side via Store API (WooCommerce Blocks)
        add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'validate_checkout_blocks' ), 10, 2 );
        
        if ( function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
            $this->register_store_api_schema();
        } else {
            add_action( 'woocommerce_blocks_loaded', array( $this, 'register_store_api_schema' ) );
        }

        // ─── Validación Backend para login/registro (funciona en ambos modos) ───
        add_filter( 'woocommerce_process_login_errors', array( $this, 'validate_login_register' ), 10, 3 );
        add_filter( 'woocommerce_process_registration_errors', array( $this, 'validate_login_register' ), 10, 4 );
    }

    /**
     * Registra el esquema de datos para la extensión en el Store API de WooCommerce Blocks.
     * Esto evita que WooCommerce descarte los datos inyectados en extensions.crafycaptcha.
     */
    public function register_store_api_schema() {
        CrafyCaptcha_Core::log( 'register_store_api_schema() called.' );
        if ( function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
            $result = woocommerce_store_api_register_endpoint_data( array(
                'endpoint'        => 'checkout',
                'namespace'       => 'crafycaptcha',
                'schema_callback' => array( $this, 'store_api_schema_callback' ),
            ) );
            
            if ( is_wp_error( $result ) ) {
                CrafyCaptcha_Core::log( 'woocommerce_store_api_register_endpoint_data ERROR: ' . $result->get_error_message() );
            } else {
                CrafyCaptcha_Core::log( 'woocommerce_store_api_register_endpoint_data SUCCESS.' );
            }
        } else {
            CrafyCaptcha_Core::log( 'woocommerce_store_api_register_endpoint_data DOES NOT exist!' );
        }
    }

    /**
     * Callback del esquema del Store API.
     */
    public function store_api_schema_callback() {
        CrafyCaptcha_Core::log( 'store_api_schema_callback() called.' );
        return array(
            'crafycaptcha_token' => array(
                'description' => 'CrafyCAPTCHA verification token',
                'type'        => 'string',
                'readonly'    => false,
            ),
        );
    }

    /**
     * Inyecta el widget CrafyCAPTCHA dentro del bloque checkout-actions (antes del botón).
     * Solo se activa cuando el checkout usa bloques de WooCommerce.
     *
     * Usa monkey-patching de window.fetch para interceptar la petición POST al
     * endpoint /wc/store/v1/checkout e inyectar el token de CrafyCAPTCHA en el
     * body JSON, ya que WooCommerce Blocks no usa formularios HTML tradicionales.
     *
     * @param string $block_content El HTML renderizado del bloque.
     * @return string El HTML con el widget prepended.
     */
    public function inject_widget_in_checkout_block( $block_content ) {
        if ( ! is_checkout() ) {
            return $block_content;
        }

        CrafyCaptcha_Core::log( 'inject_widget_in_checkout_block() called.' );

        $public_key = get_option( 'crafycaptcha_public_key', '' );
        if ( empty( $public_key ) ) {
            return $block_content;
        }

        $crafy = CrafyCaptcha_Core::get_instance();
        if ( ! $crafy ) {
            return $block_content;
        }

        try {
            $public_token = $crafy->getPublicToken();
        } catch ( Exception $e ) {
            CrafyCaptcha_Core::log( 'inject_widget_in_checkout_block() Error getting public token: ' . $e->getMessage() );
            return $block_content;
        }

        $signing_public_key = get_option( 'crafycaptcha_signing_public_key', '' );
        $options_url        = admin_url( 'admin-ajax.php?action=crafycaptcha_options' );
        $container_id       = 'crafycaptcha_' . uniqid();
        $input_name         = 'crafycaptcha_token_checkout_' . uniqid();
        $nonce              = wp_create_nonce( 'crafycaptcha_options_nonce' );

        CrafyCaptcha_Core::log( 'inject_widget_in_checkout_block() container_id: ' . $container_id . ' input_name: ' . $input_name );

        // Construir el HTML del widget + script de inicialización + fetch interceptor
        $widget_html = '<div id="' . esc_attr( $container_id ) . '" style="margin-bottom: 15px;"></div>';
        $inline_js = '(function () {
            const CRAFY_DEBUG = false;
            var crafyNonce = ' . wp_json_encode( $nonce ) . ';
            var crafyContainerId = ' . wp_json_encode( $container_id ) . ';
            var crafyInputName = ' . wp_json_encode( $input_name ) . ';
            let initialized = false;
            if (CRAFY_DEBUG) console.log("[CrafyCAPTCHA Plugin] Checkout block inline script executed.");

            // ── 1. Inicializar el widget ──
            function initCrafyCheckout() {
                if (initialized) return;
                initialized = true;
                if (CRAFY_DEBUG) console.log("[CrafyCAPTCHA Plugin] Initializing checkout widget...");
                try {
                    CrafyCAPTCHA.setAutoLoad(false);
                    CrafyCAPTCHA.init(
                        crafyContainerId,
                        ' . wp_json_encode( $public_key ) . ',
                        ' . wp_json_encode( $public_token ) . ',
                        ' . wp_json_encode( $signing_public_key ) . ',
                        {
                            optionsUrl: ' . wp_json_encode( $options_url ) . ',
                            inputName: crafyInputName
                        },
                        {
                            fetchOptionsParameters: { security: crafyNonce }
                        }
                    );
                    if (CRAFY_DEBUG) console.log("[CrafyCAPTCHA Plugin] Checkout widget initialized.");
                } catch(e) {
                    if (CRAFY_DEBUG) console.error("[CrafyCAPTCHA Plugin] Error during CrafyCAPTCHA.init:", e);
                }
            }
            
            if (typeof CrafyCAPTCHA !== "undefined") {
                initCrafyCheckout();
            } else {
                window.addEventListener("CrafyCAPTCHALoaded", initCrafyCheckout);
            }

            // ── 2. Interceptar fetch para inyectar el token en el body JSON ──
            // WooCommerce Blocks envía el checkout como POST JSON a /wc/store/v1/checkout
            // El formulario HTML no se serializa, solo el estado interno de React.
            // Necesitamos interceptar el fetch y añadir el token al JSON body.
            if (!window.__crafyCaptchaFetchPatched) {
                window.__crafyCaptchaFetchPatched = true;
                var originalFetch = window.fetch;
                window.fetch = function(input, init) {
                    try {
                        var url = (typeof input === "string") ? input : (input && input.url ? input.url : "");
                        if (
                            init &&
                            init.method &&
                            init.method.toUpperCase() === "POST" &&
                            url.indexOf("/wc/store") !== -1 &&
                            url.indexOf("checkout") !== -1
                        ) {
                            // Buscar el token en el DOM por su nombre dinámico
                            var tokenInput = document.querySelector("input[name=\'" + crafyInputName + "\']");
                            var tokenValue = tokenInput ? tokenInput.value : "";
                            if (CRAFY_DEBUG) console.log("[CrafyCAPTCHA Plugin] Fetch intercepted for checkout. Token found:", !!tokenValue, "Token length:", tokenValue.length);

                            if (tokenValue && init.body) {
                                try {
                                    var bodyObj = JSON.parse(init.body);
                                    if (!bodyObj.extensions) {
                                        bodyObj.extensions = {};
                                    }
                                    if (!bodyObj.extensions.crafycaptcha) {
                                        bodyObj.extensions.crafycaptcha = {};
                                    }
                                    bodyObj.extensions.crafycaptcha.crafycaptcha_token = tokenValue;
                                    init.body = JSON.stringify(bodyObj);
                                    if (CRAFY_DEBUG) console.log("[CrafyCAPTCHA Plugin] Token injected into checkout request body.");
                                } catch (parseErr) {
                                    if (CRAFY_DEBUG) console.error("[CrafyCAPTCHA Plugin] Error parsing fetch body:", parseErr);
                                }
                            }
                        }
                    } catch (fetchErr) {
                        if (CRAFY_DEBUG) console.error("[CrafyCAPTCHA Plugin] Error in fetch interceptor:", fetchErr);
                    }
                    return originalFetch.apply(this, arguments);
                };
                if (CRAFY_DEBUG) console.log("[CrafyCAPTCHA Plugin] Fetch interceptor installed.");
            }
        })();';

        $widget_html .= wp_get_inline_script_tag( $inline_js );

        return $widget_html . $block_content;
    }

    /**
     * Validación para login y registro de WooCommerce.
     */
    public function validate_login_register( $errors ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $message = __( '<strong>ERROR</strong>: Security validation failed. Please verify that you are human.', 'crafycaptcha' );
            $errors->add( 'crafycaptcha_invalid', wp_kses( $message, array( 'strong' => array() ) ) );
        }
        return $errors;
    }

    /**
     * Validación para el checkout clásico (shortcode).
     */
    public function validate_checkout_classic( $data, $errors ) {
        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            $message = __( '<strong>ERROR</strong>: Security validation failed. Please verify that you are human.', 'crafycaptcha' );
            $errors->add( 'crafycaptcha_invalid', wp_kses( $message, array( 'strong' => array() ) ) );
        }
    }

    /**
     * Validación para el checkout por bloques (Store API).
     * Lee el token del JSON body (extensions.crafycaptcha.crafycaptcha_token)
     * que fue inyectado por el fetch interceptor del frontend.
     *
     * @param \WC_Order         $order   La orden que se está procesando.
     * @param \WP_REST_Request  $request El request del Store API.
     */
    public function validate_checkout_blocks( $order, $request ) {
        CrafyCaptcha_Core::log( 'validate_checkout_blocks() called.' );

        // Leer el body JSON del request REST
        $body = $request->get_json_params();
        $token = '';

        // Log del contenido de extensions para depuración
        if ( isset( $body['extensions'] ) ) {
            CrafyCaptcha_Core::log( 'validate_checkout_blocks() extensions keys: ' . implode( ', ', array_keys( $body['extensions'] ) ) );
        } else {
            CrafyCaptcha_Core::log( 'validate_checkout_blocks() no extensions in body.' );
        }

        // El token viene en extensions.crafycaptcha.crafycaptcha_token
        // (inyectado por el fetch interceptor del frontend)
        if ( isset( $body['extensions']['crafycaptcha']['crafycaptcha_token'] ) && is_string( $body['extensions']['crafycaptcha']['crafycaptcha_token'] ) ) {
            $token = sanitize_text_field( $body['extensions']['crafycaptcha']['crafycaptcha_token'] );
            CrafyCaptcha_Core::log( 'validate_checkout_blocks() token found in extensions. Length: ' . strlen( $token ) );
        }

        if ( empty( $token ) ) {
            CrafyCaptcha_Core::log( 'validate_checkout_blocks() - No token received.' );
            throw new \Exception(
                esc_html__( 'Security validation failed. Please verify that you are human.', 'crafycaptcha' )
            );
        }

        // Inyectar el token en $_POST para que is_token_valid() lo pueda leer
        $_POST['crafycaptcha_token'] = $token;

        if ( ! CrafyCaptcha_Core::is_token_valid() ) {
            CrafyCaptcha_Core::log( 'validate_checkout_blocks() - Token inválido.' );
            throw new \Exception(
                esc_html__( 'Security validation failed. Please verify that you are human.', 'crafycaptcha' )
            );
        }

        CrafyCaptcha_Core::log( 'validate_checkout_blocks() - Token válido.' );
    }
}
