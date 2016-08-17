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
class Premiumdev_Woo_Paypal_Digital_Goods_Express_Checkout {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Woo_Paypal_Digital_Goods_Express_Checkout_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->plugin_name = 'woo-pal-digital-goods-express-checkout';
        $this->version = '1.0.0';

        $this->premiumdev_load_dependencies();
        $this->premiumdev_woo_gateway_hooks();
        $this->premiumdev_set_locale();
        add_action('get_header', array($this, 'premiumdev_WPDG_return'));
        add_action('wp_ajax_WPDG_do_express_checkout', array($this, 'premiumdev_do_payment_now'));
        add_action('wp_ajax_nopriv_WPDG_do_express_checkout', array($this, 'premiumdev_do_payment_now'));
        $prefix = is_network_admin() ? 'network_admin_' : '';
        add_filter("{$prefix}plugin_action_links_".PREMIUMDEV_WPDG_PLUGIN_BASENAME, array($this, 'premiumdev_wpdg_settings_link'), 10, 4);
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Woo_Paypal_Digital_Goods_Express_Checkout_Loader. Orchestrates the hooks of the plugin.
     * - Woo_Paypal_Digital_Goods_Express_Checkout_i18n. Defines internationalization functionality.
     * - Woo_Paypal_Digital_Goods_Express_Checkout_Admin. Defines all hooks for the admin area.
     * - Woo_Paypal_Digital_Goods_Express_Checkout_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function premiumdev_load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-paypal-digital-goods-express-checkout-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-paypal-digital-goods-express-checkout-i18n.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-paypal-digital-goods-express-checkout-common-function.php';

        if (class_exists('WC_Payment_Gateway')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-paypal-digital-goods-express-checkout-gateway.php';
        }

        $this->loader = new Premiumdev_Woo_Paypal_Digital_Goods_Express_Checkout_Loader();
    }

    /**
     * Add Payment Gateways Woocommerce Section
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function premiumdev_woo_gateway_hooks() {
        add_filter('woocommerce_payment_gateways', array($this, 'premiumdev_paypal_digital_goods_express_checkout_gateway'), 10, 1);
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Woo_Paypal_Digital_Goods_Express_Checkout_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function premiumdev_set_locale() {

        $plugin_i18n = new Premiumdev_Woo_Paypal_Digital_Goods_Express_Checkout_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'premiumdev_load_plugin_textdomain');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Woo_Paypal_Digital_Goods_Express_Checkout_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    public function premiumdev_paypal_digital_goods_express_checkout_gateway($methods) {
        $methods[] = 'Premiumdev_Woo_Paypal_Digital_Goods_Express_Checkout_Gateway';
        return $methods;
    }

    public function premiumdev_WPDG_return() {
        
        global $wp;        
        if (!isset($_GET['WPDG'])) {
            return;
        }
        $paynow = ( 'proceednow' == $_GET['WPDG'] ) ? true : false;
        unset($_GET['WPDG']);
        if (isset($wp->query_vars['order-received'])) {
            $order_id = $_GET['WPDG_order'] = $wp->query_vars['order-received'];
        } elseif (isset($_GET['order_id'])) {
            $order_id = $_GET['WPDG_order'] = $_GET['order_id'];
        } else {
            $order_id = $_GET['WPDG_order'] = $_GET['order'];
        }
        $order = new WC_Order($order_id);
        wp_register_style('WPDG-iframe', WPDG_DIR_URL . 'css/WPDG-iframe.css');
        wp_register_script('WPDG-return', WPDG_DIR_URL . 'js/WPDG-return.js', 'jquery');
        $WPDG_params = array(
            'ajaxUrl' => (!is_ssl() ) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'),
            'queryString' => http_build_query($_GET),
            'msgWaiting' => __("This won't take a minute", 'WPDG'),
            'msgComplete' => __('Payment Processed', 'WPDG'),
        );
        wp_localize_script('WPDG-return', 'WPDG', $WPDG_params);        
        ?>
        <html>
            <head>
                <title><?php __('Processing...', 'WPDG'); ?></title>
                <?php wp_print_styles('WPDG-iframe'); ?>
                <?php if ($paynow) {  ?>
                    <?php wp_print_scripts('jquery'); ?>
                    <?php wp_print_scripts('WPDG-return'); ?>
                <?php } ?>
                <meta name="viewport" content="width=device-width">
            </head>
            <body>
                <div id="left_frame">
                    <div id="right_frame">
                        <p id="message">
                            <?php if ($paynow) {   ?>
                                <?php _e('Processing payment', 'WPDG'); ?>
                                <?php $location = remove_query_arg(array('WPDG', 'token', 'PayerID')); ?>
                            <?php } else { ?>
                                <?php _e('Cancelling Order', 'WPDG'); ?>
                                <?php $location = html_entity_decode($order->get_cancel_order_url());  ?>
                            <?php } ?>
                        </p>
                        <img src="https://www.paypal.com/en_US/i/icon/icon_animated_prog_42wx42h.gif" alt="Processing..." />
                        <div id="right_bottom">
                            <div id="left_bottom">
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (!$paynow) { ?>
                    <script type="text/javascript">
                        setTimeout('if (window!=top) {top.location.replace("<?php echo $location; ?>");}else{location.replace("<?php echo $location; ?>");}', 1500);
                    </script>
                <?php } ?>
            </body>
        </html>
        <?php
        exit();
    }
    
    public function premiumdev_do_payment_now() {
        $WPDG_OBJ = new Premiumdev_Woo_Paypal_Digital_Goods_Express_Checkout_Gateway();
        $WPDG_OBJ->premiumdev_woo_pdge_do_express_checkout();
    }

    public function premiumdev_wpdg_settings_link($actions, $plugin_file, $plugin_data, $context) {
        		
		$custom_actions = array(
            'configure' => sprintf('<a href="%s">%s</a>', 'admin.php?page=wc-settings&tab=checkout&section=paypal_digital_goods_express_checkout', __('Configure', 'donation-button')),
            'docs' => sprintf('<a href="%s" target="_blank">%s</a>', 'https://www.premiumdev.com/product/paypal-digital-goods-woocommerce/', __('Docs', 'donation-button')),
            'support' => sprintf('<a href="%s" target="_blank">%s</a>', 'https://wordpress.org/support/plugin/pal-digital-goods-express-checkout-for-woo', __('Support', 'donation-button')),
            'review' => sprintf('<a href="%s" target="_blank">%s</a>', 'https://wordpress.org/support/view/plugin-reviews/pal-digital-goods-express-checkout-for-woo', __('Write a Review', 'donation-button')),
        );
		
        return array_merge($custom_actions, $actions);
    }

}
