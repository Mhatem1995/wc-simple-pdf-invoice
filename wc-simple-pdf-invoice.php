<?php
/**
 * Plugin Name: WC Simple PDF Invoice
 * Description: Simple, modern PDF invoice generator for WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_SIMPLE_PDF_INVOICE_VERSION', '1.0.0');
define('WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_SIMPLE_PDF_INVOICE_PLUGIN_URL', plugin_dir_url(__FILE__));

class WC_Simple_PDF_Invoice {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        
        // Declare HPOS compatibility
        add_action('before_woocommerce_init', array($this, 'declare_hpos_compatibility'));
    }
    
    /**
     * Declare compatibility with WooCommerce High-Performance Order Storage
     */
    public function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                __FILE__,
                true
            );
        }
    }
    
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Include required files
        $this->includes();
        
        // Initialize hooks
        $this->init_hooks();
    }
    
    private function includes() {
        require_once WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'includes/settings-page.php';
        require_once WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'includes/order-data.php';
        require_once WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'includes/pdf-generator.php';
        require_once WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'includes/email-handler.php';
    }
    
    private function init_hooks() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        
        // Order page hooks
        add_action('woocommerce_order_details_after_order_table', array($this, 'add_customer_download_button'));
        add_action('woocommerce_admin_order_data_after_order_details', array($this, 'add_admin_download_button'));
        
        // AJAX hooks
        add_action('wp_ajax_download_invoice_pdf', array($this, 'handle_pdf_download'));
        add_action('wp_ajax_nopriv_download_invoice_pdf', array($this, 'handle_pdf_download'));
        
        // Email hooks
        add_action('woocommerce_order_status_changed', array($this, 'maybe_send_invoice_email'), 10, 3);
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'PDF Invoice Settings',
            'PDF Invoice',
            'manage_woocommerce',
            'wc-pdf-invoice-settings',
            array($this, 'settings_page')
        );
    }
    
    public function settings_page() {
        $settings = new WC_PDF_Invoice_Settings();
        $settings->display_page();
    }
    
    public function admin_scripts($hook) {
        if ($hook === 'woocommerce_page_wc-pdf-invoice-settings') {
            wp_enqueue_media();
            wp_enqueue_style('wc-pdf-invoice-admin', WC_SIMPLE_PDF_INVOICE_PLUGIN_URL . 'assets/css/admin.css', array(), WC_SIMPLE_PDF_INVOICE_VERSION);
        }
    }
    
    public function add_customer_download_button($order) {
        if (!$order || !is_user_logged_in()) return;
        
        $current_user = wp_get_current_user();
        if ($order->get_customer_id() !== $current_user->ID) return;
        
        echo '<div class="wc-pdf-invoice-download" style="margin-top: 20px;">';
        echo '<a href="' . wp_nonce_url(admin_url('admin-ajax.php?action=download_invoice_pdf&order_id=' . $order->get_id()), 'download_invoice_' . $order->get_id()) . '" class="button" target="_blank">Download Invoice PDF</a>';
        echo '</div>';
    }
    
    public function add_admin_download_button($order) {
        echo '<div class="wc-pdf-invoice-admin-download" style="margin-top: 15px;">';
        echo '<a href="' . wp_nonce_url(admin_url('admin-ajax.php?action=download_invoice_pdf&order_id=' . $order->get_id()), 'download_invoice_' . $order->get_id()) . '" class="button button-primary" target="_blank">Download Invoice PDF</a>';
        echo '</div>';
    }
    
    public function handle_pdf_download() {
        if (!isset($_GET['order_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'download_invoice_' . $_GET['order_id'])) {
            wp_die('Security check failed');
        }
        
        $order_id = intval($_GET['order_id']);
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_die('Order not found');
        }
        
        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            if (!is_user_logged_in() || $order->get_customer_id() !== get_current_user_id()) {
                wp_die('Permission denied');
            }
        }
        
        // Check if Dompdf is installed
        if (!file_exists(WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'vendor/dompdf/autoload.inc.php')) {
            wp_die('PDF library not installed. Please install Dompdf first. <a href="' . admin_url('admin.php?page=wc-pdf-invoice-settings') . '">Go to Settings</a>');
        }
        
        try {
            $pdf_generator = new WC_PDF_Generator();
            $pdf_generator->generate_and_download($order);
        } catch (Exception $e) {
            wp_die('Error generating PDF: ' . $e->getMessage());
        }
    }
    
    public function maybe_send_invoice_email($order_id, $old_status, $new_status) {
        // Temporarily disable auto-email if there are issues
        $settings = get_option('wc_pdf_invoice_settings', array());
        $auto_send_statuses = isset($settings['auto_send_statuses']) ? $settings['auto_send_statuses'] : array();
        
        // Only proceed if auto-send is enabled and Dompdf is available
        if (empty($auto_send_statuses) || !in_array($new_status, $auto_send_statuses)) {
            return;
        }
        
        // Check if required files exist before attempting to send
        if (!file_exists(WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'vendor/dompdf/autoload.inc.php')) {
            error_log('WC Simple PDF Invoice: Dompdf library not found, skipping email');
            return;
        }
        
        if (!file_exists(WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'includes/email-handler.php')) {
            error_log('WC Simple PDF Invoice: Email handler not found');
            return;
        }
        
        try {
            $email_handler = new WC_PDF_Email_Handler();
            $email_handler->send_invoice_email($order_id);
        } catch (Exception $e) {
            error_log('WC Simple PDF Invoice Error: ' . $e->getMessage());
        }
    }
    
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p><strong>WC Simple PDF Invoice</strong> requires WooCommerce to be installed and active.</p></div>';
    }
}

// Initialize the plugin
WC_Simple_PDF_Invoice::get_instance();