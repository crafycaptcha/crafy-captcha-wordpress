=== CrafyCAPTCHA ===
Contributors: crafycaptcha
Tags: captcha, antispam, security, login protection, woocommerce
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced bot protection with adaptive friction for WordPress and WooCommerce.

== Description ==

CrafyCAPTCHA is a security plugin for WordPress that protects your forms against bots, spam, and automated attacks using adaptive friction and intelligent analysis.

### Key Features

*   Protection for login, registration, password recovery, and comment forms.
*   Native integration with WooCommerce (Login, Registration, Checkout — including Block Checkout).
*   Native integration with Easy Digital Downloads (Checkout).
*   Adaptive friction: Only challenges suspicious users.

== Installation ==

**Important:** Before activating the plugin, you must obtain your API credentials (Public Key, Secret Key, and Signing Public Key) by creating a free account at [captcha.crafy.net](https://captcha.crafy.net/).

= Automatic Installation =

1. In your WordPress admin panel, go to **Plugins > Add New**.
2. Search for **CrafyCAPTCHA**.
3. Click **Install Now**, then **Activate**.
4. Go to **Settings > CrafyCAPTCHA** and enter your API credentials (Public Key, Secret Key, and Signing Public Key). You can get these by signing up at [captcha.crafy.net](https://captcha.crafy.net/).
5. The plugin will automatically start protecting your forms.

= Manual Installation =

1. Download the plugin ZIP file.
2. Upload and extract it to the `/wp-content/plugins/crafycaptcha` directory on your server, or upload the ZIP via **Plugins > Add New > Upload Plugin** in the WordPress admin panel.
3. Activate the plugin through the **Plugins** screen.
4. Go to **Settings > CrafyCAPTCHA** and enter your API credentials (Public Key, Secret Key, and Signing Public Key). You can get these by signing up at [captcha.crafy.net](https://captcha.crafy.net/).
5. The plugin will automatically start protecting your forms.

== Frequently Asked Questions ==

= Where do I get the API keys? =
You can sign up at [CrafyCAPTCHA](https://captcha.crafy.net/) and create a new site in your dashboard to obtain your access keys.

= Does it work with WooCommerce? =
Yes, CrafyCAPTCHA automatically integrates with all major WooCommerce forms, including the Block-based Checkout.

= Can I have multiple CAPTCHA widgets on the same page? =
Yes, CrafyCAPTCHA fully supports multiple independent widgets on a single page (e.g., login and registration forms side by side).

== Screenshots ==

1. Plugin settings screen at Settings > CrafyCAPTCHA.

== Changelog ==

= 1.0.0 =
* Initial stable release with WordPress, WooCommerce, and EDD integration.
* Robust CSRF support, anti-spam protection, and fail-closed/open-fail mitigation.
* Multi-widget support for pages with multiple forms.
