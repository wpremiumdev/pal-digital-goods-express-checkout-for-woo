<?php

function premiumdev_woo_pdge_setting_field() {
    return array(
        'premium_enabled' => array(
            'title' => __('Enable/Disable', 'woo-pal-digital-goods-express-checkout'),
            'label' => __('Enable PayPal Digital Goods Express Checkout For Woo', 'woo-pal-digital-goods-express-checkout'),
            'type' => 'checkbox',
            'description' => '',
            'default' => 'no'
        ),
        'premium_title' => array(
            'title' => __('Title', 'woo-pal-digital-goods-express-checkout'),
            'type' => 'text',
            'description' => __('This controls the title which the user sees during checkout.', 'woo-pal-digital-goods-express-checkout'),
            'desc_tip' => true,
            'default' => __('PayPal Digital Goods Express Checkout', 'woo-pal-digital-goods-express-checkout')
        ),
        'premium_description' => array(
            'title' => __('Description', 'woo-pal-digital-goods-express-checkout'),
            'type' => 'textarea',
            'description' => __('This controls the description which the user sees during checkout.', 'woo-pal-digital-goods-express-checkout'),
            'desc_tip' => true,
            'default' => __("Pay With Digital Goods PayPal Express Checkout.", 'woo-pal-digital-goods-express-checkout')
        ),
        'premium_testmode' => array(
            'title' => __('Test Mode', 'woo-pal-digital-goods-express-checkout'),
            'type' => 'checkbox',
            'default' => 'yes',
            'description' => __('Place The Payment Gateway in Development Mode.', 'woo-pal-digital-goods-express-checkout'),
            'desc_tip' => true,
            'label' => __('Enable PayPal Digital Goods PayPal Express Checkout Sandbox / Test Mode', 'woo-pal-digital-goods-express-checkout')
        ),
        'premium_sandbox_username' => array(
            'title' => __('API Username', 'woo-pal-digital-goods-express-checkout'),
            'type' => 'text',
            'description' => __('Get your API credentials from PayPal.', 'woo-pal-digital-goods-express-checkout'),
            'desc_tip' => true,
            'label' => __('Create sandbox accounts and obtain API credentials from within your <a href="http://developer.paypal.com">PayPal developer account</a>.', 'woo-pal-digital-goods-express-checkout'),
            'default' => ''
        ),
        'premium_sandbox_password' => array(
            'title' => __('API Password', 'woo-pal-digital-goods-express-checkout'),
            'type' => 'password',
            'description' => __('Get your API credentials from PayPal.', 'woo-pal-digital-goods-express-checkout'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_sandbox_signature' => array(
            'title' => __('API Signature', 'woo-pal-digital-goods-express-checkout'),
            'type' => 'password',
            'description' => __('Get your API credentials from PayPal.', 'woo-pal-digital-goods-express-checkout'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_live_username' => array(
            'title' => __('API Username', 'woo-pal-digital-goods-express-checkout'),
            'type' => 'text',
            'label' => __('Get your live account API credentials from your PayPal account profile under the API Access section <br />or by using <a target="_blank" href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_login-api-run">this tool</a>.', 'woo-pal-digital-goods-express-checkout'),
            'description' => __('Get your API credentials from PayPal.', 'woo-pal-digital-goods-express-checkout'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_live_password' => array(
            'title' => __('API Password', 'woo-pal-digital-goods-express-checkout'),
            'type' => 'password',
            'description' => __('Get your API credentials from PayPal.', 'woo-pal-digital-goods-express-checkout'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_live_signature' => array(
            'title' => __('API Signature', 'woo-pal-digital-goods-express-checkout'),
            'type' => 'password',
            'description' => __('Get your API credentials from PayPal.', 'woo-pal-digital-goods-express-checkout'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_invoice_prefix' => array(
            'title' => __('Invoice ID Prefix', 'woo-pal-digital-goods-express-checkout'),
            'type' => 'text',
            'description' => __('Add a prefix to the invoice ID sent to PayPal. This can resolve duplicate invoice problems when working with multiple websites on the same PayPal account.', 'woo-pal-digital-goods-express-checkout'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_debug_log' => array(
            'title' => __('Debug Log', 'woo-pal-digital-goods-express-checkout'),
            'type' => 'checkbox',
            'description' => __('Enable Log PayPal Digital Goods Express Checkout', 'woo-pal-digital-goods-express-checkout'),
            'desc_tip' => true,
            'default' => 'no',
            'label' => __('Enable Log PayPal Digital Goods Express Checkout <code>/wp-content/uploads/wc-logs/</code>', 'woo-pal-digital-goods-express-checkout')
        )
    );
}
