<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://premiumdev.com/
 * @since             1.0.0
 * @package           Woo_Paypal_Digital_Goods_Express_Checkout
 *
 * @wordpress-plugin
 * Plugin Name:       PayPal Digital Goods For Woocommerce
 * Plugin URI:        https://premiumdev.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            wppaypalcontact
 * Author URI:        https://premiumdev.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-pal-digital-goods-express-checkout
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (!defined('WPDG_DIR_URL')) {
    define('WPDG_DIR_URL', plugin_dir_url(__FILE__));
}

if (!defined('PREMIUMDEV_WPDG_PLUGIN_BASENAME')) {
    define('PREMIUMDEV_WPDG_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-paypal-digital-goods-express-checkout-activator.php
 */
function premiumdev_activate_woo_paypal_digital_goods_express_checkout() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-paypal-digital-goods-express-checkout-activator.php';
	Premiumdev_Woo_Paypal_Digital_Goods_Express_Checkout_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-paypal-digital-goods-express-checkout-deactivator.php
 */
function premiumdev_deactivate_woo_paypal_digital_goods_express_checkout() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-paypal-digital-goods-express-checkout-deactivator.php';
	Premiumdev_Woo_Paypal_Digital_Goods_Express_Checkout_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'premiumdev_activate_woo_paypal_digital_goods_express_checkout' );
register_deactivation_hook( __FILE__, 'premiumdev_deactivate_woo_paypal_digital_goods_express_checkout' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-paypal-digital-goods-express-checkout.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function premiumdev_run_woo_paypal_digital_goods_express_checkout() {

	$plugin = new Premiumdev_Woo_Paypal_Digital_Goods_Express_Checkout();
	$plugin->run();

}

add_action('plugins_loaded', 'premiumdev_load_woo_paypal_digital_goods_express_checkout');

function premiumdev_load_woo_paypal_digital_goods_express_checkout() {
    premiumdev_run_woo_paypal_digital_goods_express_checkout();
}


