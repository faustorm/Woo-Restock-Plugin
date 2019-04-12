<?php
/**
 * Plugin Name: Back-order with Restock Date Plugin
 * Description: Add Restock Date field when products have 0 or less quantity or Stock Status is  'On back-order'
 * Version:     1.0.0
 * Author:      justin15
 */

if (!defined('ABSPATH'))
    exit;
add_action('load-plugins.php',
    function () {
        add_action('in_admin_header',
            function () {
                back_order_with_restock_date::get_instance()->back_order_with_restock_date_is_activated() && add_filter('gettext', array(back_order_with_restock_date::get_instance(), 'back_order_with_restock_date_gettext'), 99, 3);
            }
        );
    }
);
add_action('template_redirect', array(back_order_with_restock_date::get_instance(), 'startFrontend'));
add_action('admin_init', array(back_order_with_restock_date::get_instance(), 'startBackend'));
add_action('admin_init', array(back_order_with_restock_date::get_instance(), 'stop'));


class back_order_with_restock_date
{
    protected static $instance = NULL;
    public $plugin_url = '';
    public $plugin_path = '';

    public function __construct()
    {
    }

    public static function get_instance()
    {
        NULL === self::$instance and self::$instance = new self;
        return self::$instance;
    }

    public function plugin_setup()
    {
        $this->plugin_url = plugins_url('/', __FILE__);
        $this->plugin_path = plugin_dir_path(__FILE__);
    }

    public function startBackend()
    {
        if (!$this->jbig_woocommerce_is_active()) {
            return;
        }
        //Add  restock.js
        add_action('admin_enqueue_scripts', array($this, 'jbig_enqueue_scripts'));
        // Display _product_restock_date_field
        add_action('woocommerce_product_options_inventory_product_data', array($this, 'jbig_woocommerce_product_restockdate_fields'));
        //Save _product_restock_date_field
        add_action('woocommerce_process_product_meta', array($this, 'jbig_woocommerce_product_restockdate_fields_save'));
    }

    private function jbig_woocommerce_is_active()
    {
        return is_plugin_active('woocommerce/woocommerce.php');
    }

    public function startFrontend()
    {
        //Add styles
        add_action('wp_enqueue_scripts', array($this, 'jbig_enqueue_styles'));
        //Display stock status
        add_action('woocommerce_after_shop_loop_item_title', array($this, 'jbig_stock_text_shop_page'), 25);
    }

    public function stop()
    {
        if (!$this->jbig_woocommerce_is_active()) {
            deactivate_plugins(plugin_basename(__FILE__));
            unset($_GET['activate']);
        }
    }

    public function back_order_with_restock_date_gettext($translated_text, $untranslated_text, $domain)
    {
        $old = array(
            "Plugin <strong>activated</strong>.",
            "Selected plugins <strong>activated</strong>."
        );

        $new = "The Back-order with Restock Date Plugin has been <strong>activated</strong>!";

        if (in_array($untranslated_text, $old, true)) {
            $translated_text = $new;
            remove_filter(current_filter(), __FUNCTION__, 99);
        }
        return $translated_text;
    }

    public function back_order_with_restock_date_is_activated()
    {
        $return = FALSE;
        $activate = filter_input(INPUT_GET, 'activate', FILTER_SANITIZE_STRING);
        $activate_multi = filter_input(INPUT_GET, 'activate-multi', FILTER_SANITIZE_STRING);

        if (!empty($activate) || !empty($activate_multi))
            $return = TRUE;

        return $return;
    }

    public function jbig_enqueue_scripts($hook)
    {
        if ('post.php' != $hook) {
            return;
        }
        wp_enqueue_script('restock_script', plugins_url('/restock.js', __FILE__));
    }
    public function jbig_enqueue_styles()
    {
        wp_enqueue_style('restock_style', plugins_url('/back-order-with-restock-date.css', __FILE__));
    }

    public function jbig_stock_text_shop_page()
    {
        global $product, $post;

        $availability = $product->get_availability();
        $stockBackOrder = ["Available on backorder", "onbackorder"];
        $availabile = 'Available on backorder';
        $availabileClass = 'available-on-backorder';
        $restockDate = (empty(get_post_meta($post->ID, '_product_restock_date_field', true)) ? 0 : date('m/d/Y', strtotime(get_post_meta($post->ID, '_product_restock_date_field', true))));

        if ((in_array($availability['availability'], $stockBackOrder) ||
            in_array(get_post_meta($post->ID, '_stock_status', true), $stockBackOrder))) {
            echo apply_filters('woocommerce_stock_html', '<p class="stock ' . $availabileClass . '">' .
                $availabile .
                (!empty(get_post_meta($post->ID, '_product_restock_date_field', true)) ? '<br/><span>Restock date: ' . $restockDate . ' </span>' : '') .
                '</p>', $availability['availability']);
        } else if(! $availability['class'] === 'in-stock') {
            echo apply_filters('woocommerce_stock_html', '<p class="stock ' . esc_attr($availability['class']) . '">' .
                esc_html($availability['availability']) . ($restockDate > 0 ? '<br/><span>Restock date: ' . $restockDate . ' </span>' : '') . '</p>', $availability['availability']);
        }
    }

    public function jbig_woocommerce_product_restockdate_fields()
    {
        echo '<div class="restock_date">';
        woocommerce_wp_text_input(
            array(
                'id' => '_product_restock_date_field',
                'placeholder' => 'Custom Product Number Field',
                'label' => __('Estimated Restock Date', 'woocommerce'),
                'type' => 'date'
            )
        );
        echo '</div>';
    }

    public function jbig_woocommerce_product_restockdate_fields_save($post_id)
    {
        $woocommerce_product_restock_date_field = $_POST['_product_restock_date_field'];

        if (!empty($woocommerce_product_restock_date_field)) {
            update_post_meta($post_id, '_product_restock_date_field', esc_attr($woocommerce_product_restock_date_field));
        } else {
            delete_post_meta($post_id, '_product_restock_date_field');
        }
    }
}
