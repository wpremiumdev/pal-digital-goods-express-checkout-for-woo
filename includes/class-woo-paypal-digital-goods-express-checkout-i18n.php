<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://premiumdev.com/
 * @since      1.0.0
 *
 * @package    Woo_Paypal_Digital_Goods_Express_Checkout
 * @subpackage Woo_Paypal_Digital_Goods_Express_Checkout/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woo_Paypal_Digital_Goods_Express_Checkout
 * @subpackage Woo_Paypal_Digital_Goods_Express_Checkout/includes
 * @author     wppaypalcontact <wppaypalcontact@gmail.com>
 */
class Premiumdev_Woo_Paypal_Digital_Goods_Express_Checkout_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function premiumdev_load_plugin_textdomain() {

		load_plugin_textdomain(
			'woo-pal-digital-goods-express-checkout',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
