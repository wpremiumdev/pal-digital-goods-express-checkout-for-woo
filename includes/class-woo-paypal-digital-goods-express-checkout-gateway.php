<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Paypal_Digital_Goods_Express_Checkout
 * @subpackage Woo_Paypal_Digital_Goods_Express_Checkout/includes
 * @author     wppaypalcontact <wppaypalcontact@gmail.com>
 */
class Premiumdev_Woo_Paypal_Digital_Goods_Express_Checkout_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        try {
            $this->id = 'paypal_digital_goods_express_checkout';
            $this->api_version = '119';
            $this->method_title = __('PayPal Digital Goods Express Checkout', 'woo-pal-digital-goods-express-checkout');
            $this->icon = apply_filters('woocommerce_woo_paypal_digital_goods_express_checkout_icon', "");
            $this->has_fields = true;
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option('premium_title');
            $this->description = $this->get_option('premium_description');
            $this->enabled = $this->get_option('premium_enabled');
            $this->testmode = $this->get_option('premium_testmode', "no") === "yes" ? true : false;
            $this->debug = $this->get_option('premium_debug_log', "no") === "yes" ? true : false;
            $this->invoice_prefix = $this->get_option('premium_invoice_prefix');            
            $this->log = "";
            $this->Order_ID = "";
            $this->return_URL = "";
            $this->cancel_URL = "";
            $this->notify_URL = "";
            $this->is_mobile = "no";
            $this->incontext_url = "yes";
            $this->token = "";
            $this->api_buttonsource = "mbjtechnolabs_SP";
            $this->api_subject = "";
            $this->api_methods = "";
            $this->transaction_id = "";
            $this->locale_code = apply_filters('plugin_locale', get_locale(), 'woo-pal-digital-goods-express-checkout');
            $this->paypal_items = array();
            $this->paypal_args = array();
            if ($this->testmode) {
                $this->Pay_URL = "https://www.sandbox.paypal.com/webscr";
                $this->Transaction_URL = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=%s";
                $this->EndPoint = "https://api-3t.sandbox.paypal.com/nvp";
                $this->api_username = ($this->get_option('premium_sandbox_username')) ? trim($this->get_option('premium_sandbox_username')) : '';
                $this->api_password = ($this->get_option('premium_sandbox_password')) ? trim($this->get_option('premium_sandbox_password')) : '';
                $this->api_signature = ($this->get_option('premium_sandbox_signature')) ? trim($this->get_option('premium_sandbox_signature')) : '';
            } else {
                $this->Pay_URL = "https://www.paypal.com/webscr";
                $this->Transaction_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=%s";
                $this->EndPoint = "https://api-3t.paypal.com/nvp";
                $this->api_username = ($this->get_option('premium_live_username')) ? trim($this->get_option('premium_live_username')) : '';
                $this->api_password = ($this->get_option('premium_live_password')) ? trim($this->get_option('premium_live_password')) : '';
                $this->api_signature = ($this->get_option('premium_live_signature')) ? trim($this->get_option('premium_live_signature')) : '';
            }
            
            add_action('woocommerce_receipt_' . $this->id, array($this, 'premiumdev_woo_pdge_receipt_page'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'premiumdev_woo_pdge_thankyou_page'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_after_checkout_form', array($this, 'premiumdev_woo_pdge_customize'));   
           
        } catch (Exception $ex) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $ex->getMessage(), 'error');
            return;
        }
    }
    
    public function init_form_fields() {        
        return $this->form_fields = premiumdev_woo_pdge_setting_field();
    }
    
    public function is_available() {
        try{
            if ($this->enabled === "yes") {
                if (!is_ssl() && !$this->testmode) {
                    return false;
                }
                if (!$this->premiumdev_woo_pdge_valid_currency()) {
                    return false;
                }       
                if (!$this->api_username || !$this->api_password || !$this->api_signature) {
                    return false;
                }            
            }
            return true;      
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }
    
    public function admin_options() {        
        ?>              
        <table class="form-table">
            <?php
            if (!$this->premiumdev_woo_pdge_valid_currency()) {
                ?>
                <div class="inline error"><p><strong><?php _e('Gateway Disabled', 'woo-pal-digital-goods-express-checkout'); ?></strong>: <?php _e('PayPal does not support your store currency.', 'woo-pal-digital-goods-express-checkout'); ?></p></div>
                <?php
                return;
            } else {
                $this->generate_settings_html();
            }
            $this->premiumdev_woo_pdge_checks_field();
            ?>
        </table>
        <script type="text/javascript">
            jQuery('#woocommerce_paypal_digital_goods_express_checkout_premium_testmode').change(function () {
            var sandbox = jQuery('#woocommerce_paypal_digital_goods_express_checkout_premium_sandbox_username, #woocommerce_paypal_digital_goods_express_checkout_premium_sandbox_password, #woocommerce_paypal_digital_goods_express_checkout_premium_sandbox_signature').closest('tr'),
                    production = jQuery('#woocommerce_paypal_digital_goods_express_checkout_premium_live_username, #woocommerce_paypal_digital_goods_express_checkout_premium_live_password, #woocommerce_paypal_digital_goods_express_checkout_premium_live_signature').closest('tr');
            if (jQuery(this).is(':checked')) {
            sandbox.show();
            production.hide();
            } else {
            sandbox.hide();
            production.show();
            }
            }).change();
        </script> 
        <?php
    }

    public function process_payment($order_id) {
        try{
            $this->Order_ID = $order_id;
            $order = new WC_Order($order_id);
            $this->premiumdev_woo_pdge_paypal_object($order_id);
            if (is_ajax()) { 
                $result = array(
                    'result' => 'success',
                    'redirect' => $this->premiumdev_woo_pdge_get_checkout_url()
                );
                echo json_encode($result);
                exit();
            } else {
                return array(
                    'result' => 'success',
                    'redirect' => $order->get_checkout_payment_url(true),
                );
            }
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            if (is_ajax()) {
                $result = array(
                    'result' => 'failed',
                    'redirect' => $this->premiumdev_woo_pdge_get_checkout_url()
                );
                echo json_encode($result);
                exit();                
            } else {
                wp_redirect($order->get_checkout_payment_url(true));      
                exit;
            }
            
            return;
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        try{
            if (function_exists('wc_get_order')) {
                $order = wc_get_order($order_id);
            } else {
                $order = new WC_Order($order_id);
            }
            if (!$order || !method_exists($order, 'get_transaction_id') || !$order->get_transaction_id() || !$this->api_username || !$this->api_password || !$this->api_signature) {
                return false;
            }
            $post_data = array(
                'VERSION' => '84.0',
                'USER' => $this->api_username,
                'PWD' => $this->api_password,
                'SIGNATURE' => $this->api_signature,
                'METHOD' => 'RefundTransaction',
                'TRANSACTIONID' => $order->get_transaction_id(),
                'REFUNDTYPE' => is_null($amount) ? 'Full' : 'Partial'
            );
            if (!is_null($amount)) {
                $post_data['AMT'] = number_format($amount, 2, '.', '');
                $post_data['CURRENCYCODE'] = $order->get_order_currency();
            }
            if ($reason) {
                if (255 < strlen($reason)) {
                    $reason = substr($reason, 0, 252) . '...';
                }
                $post_data['NOTE'] = html_entity_decode($reason, ENT_NOQUOTES, 'UTF-8');
            }
            $response = wp_remote_post($this->EndPoint, array(
                'method' => 'POST',
                'body' => $post_data,
                'timeout' => 70,
                'user-agent' => 'Woo-PayPal-Digital-Goods-Express-Checkout',
                'httpversion' => '1.1'
               )
            );
            if (is_wp_error($response)) {
                return $response;
            }
            if (empty($response['body'])) {
                return new WP_Error('paypal-error', __('Empty Paypal response.', 'woo-pal-digital-goods-express-checkout'));
            }
            parse_str($response['body'], $parsed_response);
            switch (strtolower($parsed_response['ACK'])) {
                case 'success':
                case 'successwithwarning':
                    $order->add_order_note(sprintf(__('Refunded %s - Refund ID: %s', 'woo-pal-digital-goods-express-checkout'), $parsed_response['GROSSREFUNDAMT'], $parsed_response['REFUNDTRANSACTIONID']));
                    return true;
                    break;
            }
            return false;
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }
    
    public function premiumdev_woo_pdge_checks_field() {
        try{
            if ( $this->enabled == false ) {
                return;
            }
            if (!$this->api_username) {
                echo '<div class="inline error is-dismissible"><p>' . sprintf(__('Paypal Digital Goods error: Please enter your PayPal Digital Goods Express Checkout API Username.', 'woo-pal-digital-goods-express-checkout')) . '</p></div>';             
            } elseif (!$this->api_password) {
                echo '<div class="inline error"><p>' . sprintf(__('Paypal Digital Goods error: Please enter your PayPal Digital Goods Express Checkout API Password.', 'woo-pal-digital-goods-express-checkout')) . '</p></div>';
            } elseif (!$this->api_signature) {
                echo '<div class="inline error"><p>' . sprintf(__('Paypal Digital Goods error: Please enter your PayPal Digital Goods Express Checkout API Signature.', 'woo-pal-digital-goods-express-checkout')) . '</p></div>';
            }
        } catch (Exception $e) {
           wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
           return;
        }
}

    public function premiumdev_woo_pdge_get_checkout_url() {
        try{
            if (empty($this->token)) {
                $this->api_methods = "SetExpressCheckout";
                $this->premiumdev_woo_pdge_request_checkout_token();
            }
            return $this->premiumdev_woo_pdge_checkout_url();
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_request_checkout_token() {
        try{
            if (empty($this->token)) {
                $response = $this->premiumdev_woo_pdge_curl();
            }
            $this->token = $response['TOKEN'];
            return True;
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_curl() {
        try{
            $parameters = $this->premiumdev_woo_pdge_details_url();
            $parameters .= $this->premiumdev_woo_pdge_cart_details_url(); 
            
            $response = $this->premiumdev_woo_pdge_do_remote_request($this->EndPoint, $this->premiumdev_woo_pdge_request_args($parameters));
            $parsed_response = $this->premiumdev_woo_pdge_response($response);
            
            if (( 0 == sizeof($parsed_response) ) || !array_key_exists('ACK', $parsed_response)) {
                throw new Exception("Invalid HTTP Response for POST request($parameters) to " . $this->Pay_URL);
            }
            if ($parsed_response['ACK'] == 'Failure') {
                throw new Exception("Calling PayPal with action " . $this->api_methods . " has Failed: " . $parsed_response['L_LONGMESSAGE0'], $parsed_response['L_ERRORCODE0']);
            }
            return $parsed_response;
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }
    
    public function premiumdev_woo_pdge_do_remote_request($request_uri, $request_args) {
        return wp_safe_remote_request($request_uri, $request_args);
    }
    
    public function premiumdev_woo_pdge_request_args($parameters) {
        $args = array(
            'method' => 'POST',
            'timeout' => 60,
            'redirection' => 0,
            'httpversion' => '1.1',
            'sslverify' => FALSE,
            'blocking' => true,
            'user-agent' => 'Digital_Goods_Express_Checkout',
            'headers' => array(),
            'body' => $parameters,
            'cookies' => array(),
        );
        return apply_filters('premiumdev_' . $this->id . '_http_request_args', $args, $this);
    }
    
    public function premiumdev_woo_pdge_response($response) {
        if (isset($response->errors['http_request_failed'])) {
            $this->response = $response->errors;
        } else if (isset($response->errors['http_failure'])) {
            $this->response = $response->errors;
        } else {
            $this->response = $this->premiumdev_woo_pdge_parsed_response($response);
        }
        return $this->response;
    }
    
    public function premiumdev_woo_pdge_parsed_response($response) {
        if (is_wp_error($response)) {
            return;
        }
        parse_str($response['body'], $parsed_response);
        return $parsed_response;
    }

    public function premiumdev_woo_pdge_details_url() {
        try{
            $api_request = '';
            $api_request .= 'USER=' . urlencode($this->api_username);
            $api_request .= '&PWD=' . urlencode($this->api_password);
            $api_request .= '&SIGNATURE=' . urlencode($this->api_signature);
            $api_request .= '&VERSION=' . urlencode($this->api_version);
            if ('SetExpressCheckout' == $this->api_methods) {
                $api_request .= '&METHOD=SetExpressCheckout'
                        . '&RETURNURL=' . urlencode($this->return_URL)
                        . '&SOLUTIONTYPE=' . urlencode('Sole')
                        . '&LOCALECODE=' . urlencode($this->locale_code)
                        . '&CANCELURL=' . urlencode($this->cancel_URL);
            } elseif ('GetExpressCheckoutDetails' == $this->api_methods) {
                $api_request .= '&METHOD=GetExpressCheckoutDetails&TOKEN=' . $this->token;
            }
            return $api_request;
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_cart_details_url() {
        try{
            if (!is_object($this->Order_ID)) {
                $order = new WC_Order($this->Order_ID);
            }
            $order_total = ( method_exists($order, 'get_total') ) ? $order->get_total() : $order->get_order_total();
            $api_request = '';        
            if ('SetExpressCheckout' == $this->api_methods) {
                $api_request .= '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode($order->get_order_currency())
                        . '&PAYMENTREQUEST_0_PAYMENTACTION=Sale'
                        . '&PAYMENTREQUEST_0_AMT=' . $order_total
                        . '&PAYMENTREQUEST_0_ITEMAMT=' . number_format($order_total - $order->get_total_tax(), 2, '.', '')
                        . '&PAYMENTREQUEST_0_TAXAMT=' . $order->get_total_tax()
                        . '&PAYMENTREQUEST_0_DESC=' . urlencode(sprintf(__('Payment for Order #%s', 'woo-pal-digital-goods-express-checkout'), $order->id));
                if (!empty($this->invoice_prefix)) {
                    $api_request .= '&PAYMENTREQUEST_0_INVNUM=' . $this->invoice_prefix.''.$order->id;
                }            
                if (!empty($this->notify_URL)) {
                    $api_request .= '&PAYMENTREQUEST_0_NOTIFYURL=' . urlencode($this->notify_URL);
                }            
                if (!empty($this->paypal_args['custom'])) {
                    $api_request .= '&PAYMENTREQUEST_0_CUSTOM=' . urlencode($this->paypal_args['custom']);
                }            
                $item_count = 0;
                foreach ($this->paypal_items as $item) {
                    $api_request .= '&L_PAYMENTREQUEST_0_ITEMCATEGORY' . $item_count . '=Digital'
                            . '&L_PAYMENTREQUEST_0_NAME' . $item_count . '=' . urlencode($item['item_name'])
                            . '&L_PAYMENTREQUEST_0_AMT' . $item_count . '=' . $item['item_amount']
                            . '&L_PAYMENTREQUEST_0_QTY' . $item_count . '=' . $item['item_quantity'];
                    if (!empty($item['item_description'])) {
                        $api_request .= '&L_PAYMENTREQUEST_0_DESC' . $item_count . '=' . urlencode($item['item_description']);
                    }
                    if (!empty($item['item_tax'])) {
                        $api_request .= '&L_PAYMENTREQUEST_0_TAXAMT' . $item_count . '=' . $item['item_tax'];
                    }
                    if (!empty($item['item_number'])) {
                        $api_request .= '&L_PAYMENTREQUEST_0_NUMBER' . $item_count . '=' . $item['item_number'];
                    }
                    $item_count++;
                }
            } elseif ('DoExpressCheckoutPayment' == $this->api_methods) {
                $api_request .= '&METHOD=DoExpressCheckoutPayment'
                        . '&TOKEN=' . $_GET['token']
                        . '&PAYERID=' . $_GET['PayerID']                    
                        . '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode($order->get_order_currency())
                        . '&PAYMENTREQUEST_0_PAYMENTACTION=Sale'                    
                        . '&PAYMENTREQUEST_0_AMT=' . $order_total
                        . '&PAYMENTREQUEST_0_ITEMAMT=' . number_format($order_total - $order->get_total_tax(), 2, '.', '')
                        . '&PAYMENTREQUEST_0_TAXAMT=' . $order->get_total_tax()
                        . '&PAYMENTREQUEST_0_DESC=' . urlencode(sprintf(__('Payment for Order #%s', 'woo-pal-digital-goods-express-checkout'), $order->id));            
                if (!empty($this->invoice_prefix)) {
                    $api_request .= '&PAYMENTREQUEST_0_INVNUM=' . $this->invoice_prefix.''.$order->id;
                }           
                if (!empty($this->notify_URL)) {
                    $api_request .= '&PAYMENTREQUEST_0_NOTIFYURL=' . urlencode($this->notify_URL);
                }            
                if (!empty($this->paypal_args['custom'])) {
                    $api_request .= '&PAYMENTREQUEST_0_CUSTOM=' . urlencode($this->paypal_args['custom']);
                }            
                $item_count = 0;
                foreach ($this->paypal_items as $item) {
                    $api_request .= '&L_PAYMENTREQUEST_0_ITEMCATEGORY' . $item_count . '=Digital'
                            . '&L_PAYMENTREQUEST_0_NAME' . $item_count . '=' . urlencode($item['item_name'])
                            . '&L_PAYMENTREQUEST_0_AMT' . $item_count . '=' . $item['item_amount']
                            . '&L_PAYMENTREQUEST_0_QTY' . $item_count . '=' . $item['item_quantity'];
                    if (!empty($item['item_description'])) {
                        $api_request .= '&L_PAYMENTREQUEST_0_DESC' . $item_count . '=' . urlencode($item['item_description']);
                    }
                    if (!empty($item['item_tax'])) {
                        $api_request .= '&L_PAYMENTREQUEST_0_TAXAMT' . $item_count . '=' . $item['item_tax'];
                    }
                    if (!empty($item['item_number'])) {
                        $api_request .= '&L_PAYMENTREQUEST_0_NUMBER' . $item_count . '=' . $item['item_number'];
                    }
                    $item_count++;
                }
            } elseif ('GetTransactionDetails' == $this->api_methods) {
                $api_request .= '&METHOD=GetTransactionDetails&TRANSACTIONID=' . urlencode($this->transaction_id);
            }
            return $api_request;
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_checkout_url() {
        try{
            if ($this->testmode) {
                if ($this->is_mobile == 'yes') {
                    $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout-mobile&token=' . $this->token;
                } elseif ($this->incontext_url == 'yes') {
                    $url = 'https://www.sandbox.paypal.com/incontext?token=' . $this->token;
                } else {
                    $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $this->token;
                }
            } else {
                if ($this->is_mobile == 'yes') {
                    $url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout-mobile&token=' . $this->token;
                } elseif ($this->incontext_url == 'yes') {
                    $url = 'https://www.paypal.com/incontext?token=' . $this->token;
                } else {
                    $url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $this->token;
                }
            }
            return $url;
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_paypal_object($order) {
        try{
            if (!is_object($order)) {
                $order = new WC_Order($order);
            }
            $this->premiumdev_woo_pdge_log_write('PayPal Digital Goods generating payment object for order #', $order->id);
            $this->return_URL = add_query_arg(array('WPDG' => 'proceednow'), parent::get_return_url($order));
            $this->cancel_URL = add_query_arg(array('WPDG' => 'cancelled'), parent::get_return_url($order));
            $this->notify_URL = str_replace('https:', 'http:', add_query_arg('wc-api', 'WC_Gateway_Paypal', home_url('/')));
            if (isset($_REQUEST['WPDG_mobile_checkout'])) {
                $this->is_mobile = "yes";
            }
            return $this->premiumdev_woo_pdge_purchase_object($order);
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_purchase_object($order) {
        try{
            if (!is_object($order)) {
                $order = new WC_Order($order);
            }
            $order_total = ( method_exists($order, 'get_total') ) ? $order->get_total() : $order->get_order_total();
            $shipping_total = ( method_exists($order, 'get_total_shipping') ) ? $order->get_total_shipping() : $order->get_shipping();
            $this->paypal_args = array(
                'name' => sprintf(__('Order #%s', 'woo-pal-digital-goods-express-checkout'), $order->id),
                'description' => sprintf(__('Payment for Order #%s', 'woo-pal-digital-goods-express-checkout'), $order->id),
                'BUTTONSOURCE' => $this->api_buttonsource,
                'amount' => number_format($order_total, 2, '.', ''),
                'tax_amount' => number_format($order->get_total_tax(), 2, '.', ''),
                'invoice_number' => $this->invoice_prefix . $order->id,
                'custom' => $order->order_key,
            );
            if ($order->get_total_discount() > 0 || $shipping_total > 0 || get_option('woocommerce_prices_include_tax') == 'yes') {
                $items = $this->premiumdev_woo_pdge_disc_ship_tax($order);
                $this->paypal_items = array($items);
            } else {
                $this->premiumdev_woo_pdge_cart_item($order);
            }
            $this->paypal_args['items'] = $this->paypal_items;
            $this->paypal_args = apply_filters('woocommerce_paypal_digital_goods_nvp_args', $this->paypal_args);        
            return true;
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_disc_ship_tax($order) {
        try{
            $items = array();
            $items['item_name'] = sprintf(__('Order #%s', 'woo-pal-digital-goods-express-checkout'), $order->id);
            $items['item_description'] = sprintf(__('Payment for Order #%s', 'woo-pal-digital-goods-express-checkout'), $order->id);
            $items['item_number'] = $order->id;
            $items['item_quantity'] = 1;
            $items['item_amount'] = number_format($order->get_order_total() - $order->get_total_tax(), 2, '.', '');
            $items['item_tax'] = number_format($order->get_total_tax(), 2, '.', '');
            return $items;
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_cart_item($order) {
        try{
            if (count($order->get_items()) > 0) {
                $item_count = 0;
                foreach ($order->get_items() as $item) {
                    if ($item['qty'] > 0 && $order->get_item_total($item) > 0) {
                        $this->paypal_items[$item_count]['item_name'] = $item['name'];
                        $this->paypal_items[$item_count]['item_quantity'] = $item['qty'];
                        $this->paypal_items[$item_count]['item_amount'] = number_format($order->get_item_total($item), 2, '.', '');
                        $this->paypal_items[$item_count]['item_tax'] = number_format($order->get_item_total($item, true) - $order->get_item_total($item), 2, '.', '');
                        $product = $order->get_product_from_item($item);
                        if ($product->get_sku()) {
                            $this->paypal_args[$item_count]['item_number'] = $product->get_sku();
                        }                    
                        if (!defined('WC_VERSION') || version_compare(WC_VERSION, '2.4', '<')) {
                            $item_meta = new WC_Order_Item_Meta($item['item_meta']);
                        } else {
                            $item_meta = new WC_Order_Item_Meta($item);
                        }
                        if ($meta = $item_meta->display(true, true)) {
                            $this->paypal_items[$item_count]['item_description'] = $item['name'] . ' (' . $meta . ')';
                        }
                        $item_count++;
                    }
                }            
                if ($this->paypal_args['tax_amount'] > 0) {
                    $total_item_tax = 0;
                    foreach ($this->paypal_items as $paypal_item)
                        $total_item_tax += $paypal_item['item_tax'] * $paypal_item['item_quantity'];
                    if ($this->paypal_args['tax_amount'] != $total_item_tax) {
                        $this->paypal_args['tax_amount'] = $total_item_tax;
                    }
                }
            }
            return true;
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_valid_currency() {
        try{
            if (!in_array(get_woocommerce_currency(), apply_filters('woocommerce_paypal_supported_currencies', array('AUD', 'BRL', 'CAD', 'MXN', 'NZD', 'HKD', 'SGD', 'USD', 'EUR', 'JPY', 'TRY', 'NOK', 'CZK', 'DKK', 'HUF', 'ILS', 'MYR', 'PHP', 'PLN', 'SEK', 'CHF', 'TWD', 'THB', 'GBP')))) {
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_customize() {       
        ?>
        <script src ="https://www.paypalobjects.com/js/external/dg.js" type="text/javascript"></script>
        <script type="text/javascript">
        jQuery(document).ready(function($){
        $('form.checkout').on('checkout_place_order_<?php echo $this->id; ?>',function(event){
            var $form = $(this),
            form_data = $form.data(),
            checkout_url = ( typeof window['wc_checkout_params'] === 'undefined' ) ? woocommerce_params.checkout_url : wc_checkout_params.checkout_url;        	
            if(window.innerWidth <= 800 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                $('<input>').attr({
                        type: 'hidden',
                        id: 'WPDG_mobile_checkout',
                        name: 'WPDG_mobile_checkout',
                        value: 'yes',
                }).appendTo($form);
                return true;
            }
            if ( form_data["blockUI.isBlocked"] != 1 ) {
                $form.block({message: null, overlayCSS: {background: '#fff url(' + woocommerce_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
            }
            $.ajax({
                type:	 'POST',
                url:	 checkout_url,
                data:	 $form.serialize(),
                success: function(code) {
                    $('.woocommerce_error, .woocommerce_message').remove();
                    try {                            
                        if ( code.indexOf("<!--WC_START-->") >= 0 ) {
                            code = code.split("<!--WC_START-->")[1];
                        }                            
                        if ( code.indexOf("<!--WC_END-->") >= 0 ) {
                            code = code.split("<!--WC_END-->")[0];
                        }
                        var result;
                        try {
                            result = $.parseJSON( code );
                        } catch (error) {
                            result = {
                                result: 'failure',
                                messages: $('<div/>').addClass('woocommerce-error').text(code)
                            };
                        }
                        if (result.result=='success') {
                            var dg = new PAYPAL.apps.DGFlow({trigger:'place_order'});
                            try {
                                dg.startFlow(result.redirect);
                            } catch (error){
                                $('.woocommerce-error, .woocommerce-message').remove();
                                $form.prepend( $('<div/>').addClass('woocommerce-error').html('<?php _e("Could not initiate PayPal flow. Do you have popups blocked?", "woo-pal-digital-goods-express-checkout"); ?></br>'+error) );
                                $form.removeClass('processing').unblock();
                                $form.find( '.input-text, select' ).blur();
                                $('html, body').animate({
                                    scrollTop: ($('form.checkout').offset().top - 100)
                                }, 1000);
                            }
                        } else if (result.result=='failure') {
                            $('.woocommerce-error, .woocommerce-message').remove();
                            $form.prepend( result.messages );
                            $form.removeClass('processing').unblock();
                            $form.find( '.input-text, select' ).blur();
                            if (result.refresh=='true') {
                                    $('body').trigger('update_checkout');
                            }
                            $('html, body').animate({
                                    scrollTop: ($form.offset().top - 100)
                            }, 1000);
                        } else {
                            throw 'Invalid response';
                        }
                    }
                    catch(err) {
                        $('.woocommerce-error, .woocommerce-message').remove();
                        $form.prepend( $('<div/>').addClass('woocommerce-error').text(err) );
                        $form.removeClass('processing').unblock();
                        $form.find( '.input-text, select' ).blur();
                        $('html, body').animate({
                            scrollTop: ($('form.checkout').offset().top - 100)
                        }, 1000);
                    }
                },
                dataType: 'html'
            });
            return false;
        });
    });
    </script>
 <?php  
    }

    public function premiumdev_woo_pdge_receipt_page($order_id) {
        try{
            echo '<p>' . __('Thank you for your order, please click the button below to pay with PayPal.', 'woo-pal-digital-goods-express-checkout') . '</p>';
            echo $this->premiumdev_woo_pdge_paypal_button($order_id);
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_paypal_button($order_id) {
        try{
            $order = new WC_Order($order_id);
            return '<a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">' . __('Cancel order &amp; restore cart', 'woo-pal-digital-goods-express-checkout') . '</a>';
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }    
    }

    public function premiumdev_woo_pdge_thankyou_page($order_id) {
        try{
            global $woocommerce;
            if ($downloads = $woocommerce->customer->get_downloadable_products()) {
                ?>
                <h2><?php _e('Available downloads', 'woo-pal-digital-goods-express-checkout'); ?></h2>
                <ul class="digital-downloads">
                <?php foreach ($downloads as $download) { ?>
                        <?php if ($download['order_id'] != $order_id) { ?>
                            <?php continue; ?>
                        <?php } ?>
                        <li>
                        <?php if (is_numeric($download['downloads_remaining'])) { ?>
                                <span class="count">
                                <?php echo $download['downloads_remaining'] . _n(' download Remaining', ' downloads Remaining', $download['downloads_remaining'], 'woo-pal-digital-goods-express-checkout'); ?>
                                </span>
                        <?php } ?>
                            <a href="<?php echo esc_url($download['download_url']); ?>"><?php echo $download['download_name']; ?></a>
                        </li>
                <?php } ?>
                </ul>
                    <?php
            }
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_process_payment_response($transaction_details) {
        try{
            $order_id = (int) str_replace($this->invoice_prefix, '', $transaction_details['INVNUM']);
            $order = new WC_Order($order_id);
            if ($order->order_key !== $transaction_details['CUSTOM']) {
                $this->premiumdev_woo_pdge_log_write('PayPal Digital Goods Error: ', 'Order Key does not match invoice.');
                $this->premiumdev_woo_pdge_log_write('Transaction details: ', $transaction_details);
                return;
            }
            $this->premiumdev_woo_pdge_log_write('PayPal Digital Goods Payment status: ', $transaction_details['PAYMENTINFO_0_PAYMENTSTATUS']);
            switch (strtolower($transaction_details['PAYMENTINFO_0_PAYMENTSTATUS'])) :
                case 'completed' :
                    if ($order->status == 'completed') {
                        break;
                    }
                    if (!in_array(strtolower($transaction_details['PAYMENTINFO_0_TRANSACTIONTYPE']), array('cart', 'instant', 'express_checkout', 'web_accept', 'masspay', 'send_money'))) {
                        break;
                    }             
                    $order->add_order_note(__('Payment Completed via PayPal Digital Goods for Express Checkout', 'woo-pal-digital-goods-express-checkout'));
                    $order->payment_complete();
                    break;
                case 'pending' :             
                    if (!in_array(strtolower($transaction_details['PAYMENTINFO_0_TRANSACTIONTYPE']), array('cart', 'instant', 'express_checkout', 'web_accept', 'masspay', 'send_money'))) {
                        break;
                    }            
                    switch (strtolower($transaction_details['PAYMENTINFO_0_PENDINGREASON'])) {
                        case 'address':
                            $pending_reason = __('Address: The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile.', 'woo-pal-digital-goods-express-checkout');
                            break;
                        case 'authorization':
                            $pending_reason = __('Authorization: The payment is pending because it has been authorized but not settled. You must capture the funds first.', 'woo-pal-digital-goods-express-checkout');
                            break;
                        case 'echeck':
                            $pending_reason = __('eCheck: The payment is pending because it was made by an eCheck that has not yet cleared.', 'woo-pal-digital-goods-express-checkout');
                            break;
                        case 'intl':
                            $pending_reason = __('intl: The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'woo-pal-digital-goods-express-checkout');
                            break;
                        case 'multicurrency':
                        case 'multi-currency':
                            $pending_reason = __('Multi-currency: You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.', 'woo-pal-digital-goods-express-checkout');
                            break;
                        case 'order':
                            $pending_reason = __('Order: The payment is pending because it is part of an order that has been authorized but not settled.', 'woo-pal-digital-goods-express-checkout');
                            break;
                        case 'paymentreview':
                            $pending_reason = __('Payment Review: The payment is pending while it is being reviewed by PayPal for risk.', 'woo-pal-digital-goods-express-checkout');
                            break;
                        case 'unilateral':
                            $pending_reason = __('Unilateral: The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'woo-pal-digital-goods-express-checkout');
                            break;
                        case 'verify':
                            $pending_reason = __('Verify: The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'woo-pal-digital-goods-express-checkout');
                            break;
                        case 'other':
                            $pending_reason = __('Other: The payment is pending for a reason other than those listed above. For more information, contact PayPal customer service.', 'woo-pal-digital-goods-express-checkout');
                            break;
                        case 'none':
                        default:
                            $pending_reason = __('No pending reason provided.', 'woo-pal-digital-goods-express-checkout');
                            break;
                    }               
                    $order->add_order_note(sprintf(__('Payment via PayPal Digital Goods Pending. PayPal reason: %s.', 'woo-pal-digital-goods-express-checkout'), $pending_reason));
                    $order->update_status('pending');
                    break;
                case 'denied' :
                case 'expired' :
                case 'failed' :
                case 'voided' :               
                    $order->update_status('failed', sprintf(__('Payment %s via PayPal Digital Goods for Express Checkout.', 'woo-pal-digital-goods-express-checkout'), strtolower($transaction_details['PAYMENTINFO_0_PAYMENTSTATUS'])));
                    break;
                case "refunded" :
                case "reversed" :
                case "chargeback" :               
                    $order->update_status('refunded', sprintf(__('Payment %s via PayPal Digital Goods for Express Checkout.', 'woo-pal-digital-goods-express-checkout'), strtolower($transaction_details['PAYMENTINFO_0_PAYMENTSTATUS'])));
                    break;
                default:
                    break;
            endswitch;
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_do_express_checkout() {

        try {                        
            $this->api_methods = "DoExpressCheckoutPayment";            
            $this->token = isset($_GET['token']) ? $_GET['token'] : '';
            $this->Order_ID = isset($_GET['WPDG_order']) ? $_GET['WPDG_order'] : '';
            $order = $this->Order_ID;
            $paypal_object = $this->premiumdev_woo_pdge_paypal_object($this->Order_ID);            
            $response = $this->premiumdev_woo_pdge_curl();            
            $this->premiumdev_woo_pdge_log_write('Curl_Responce: ', $response);            
            $transaction_details = $this->premiumdev_woo_pdge_transaction_details($response);            
            $this->premiumdev_woo_pdge_log_write('Transaction Details', $transaction_details);            
            $transaction_details = array_merge($response, $transaction_details);              
            $this->premiumdev_woo_pdge_process_payment_response($transaction_details);
            if (!is_object($order)) {
                $order = new WC_Order($order);
            }
            $this->return_URL = parent::get_return_url($order);
            $this->return_URL = add_query_arg( array( 'WPDG' => 'proceednow' ), $this->return_URL );
            $result = array(
                'result' => 'success',
                'redirect' => remove_query_arg('WPDG', $this->return_URL)
            );            
        } catch (Exception $e) {
            $result = array(
                'result' => 'failure',
                'message' => sprintf(__('Unable to process payment with PayPal.<br/><br/> Response from PayPal: %s<br/><br/>Please try again.', 'woo-pal-digital-goods-express-checkout'), $e->getMessage())
            );
        }
        echo json_encode($result);
        exit();
    }

    public function premiumdev_woo_pdge_transaction_details($transaction_id) {
        try{
            if (!is_array($transaction_id) && !isset($transaction_id['PAYMENTINFO_0_TRANSACTIONID'])) {
                return False;
            }
            $this->api_methods = "GetTransactionDetails";
            $this->transaction_id = $transaction_id['PAYMENTINFO_0_TRANSACTIONID'];
            return $this->premiumdev_woo_pdge_curl();
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function premiumdev_woo_pdge_log_write($text = null, $message) {
        try{
            if ($this->debug) {
                if (empty($this->log)) {
                    $this->log = new WC_Logger();
                }
                $this->log->add('woo_paypal_digital_goods', $text . ' ' . print_r($message, true));
            }
        } catch (Exception $e) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-pal-digital-goods-express-checkout') . '</strong>: ' . $e->getMessage(), 'error');
            return;
        }
    }
}