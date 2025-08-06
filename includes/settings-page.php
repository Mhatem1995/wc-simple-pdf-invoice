<?php
// includes/settings-page.php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WC_PDF_Invoice_Settings {
    
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function register_settings() {
        register_setting('wc_pdf_invoice_settings', 'wc_pdf_invoice_settings');
    }
    
    public function display_page() {
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        $settings = get_option('wc_pdf_invoice_settings', $this->get_default_settings());
        
        // Check Dompdf installation
        $dompdf_installed = file_exists(WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'vendor/dompdf/autoload.inc.php');
        ?>
        <div class="wrap">
            <h1>PDF Invoice Settings</h1>
            
            <?php if (!$dompdf_installed): ?>
            <div class="notice notice-error">
                <p><strong>Dompdf Library Missing!</strong></p>
                <p>The PDF generation library is not installed. Please follow these steps:</p>
                <ol>
                    <li>Download Dompdf from: <a href="https://github.com/dompdf/dompdf/releases" target="_blank">https://github.com/dompdf/dompdf/releases</a></li>
                    <li>Extract the zip file</li>
                    <li>Copy the entire <code>dompdf</code> folder to: <code><?php echo WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR; ?>vendor/dompdf/</code></li>
                    <li>Refresh this page</li>
                </ol>
            </div>
            <?php else: ?>
            <div class="notice notice-success">
                <p><strong>✅ Dompdf Library Installed Successfully!</strong></p>
            </div>
            <?php endif; ?>
            
            <form method="post" action="" enctype="multipart/form-data">
                <?php wp_nonce_field('wc_pdf_invoice_settings', 'wc_pdf_invoice_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Company Logo</th>
                        <td>
                            <div class="logo-upload-section">
                                <?php if (!empty($settings['logo_url'])): ?>
                                    <img src="<?php echo esc_url($settings['logo_url']); ?>" alt="Logo" style="max-width: 200px; margin-bottom: 10px; display: block;">
                                <?php endif; ?>
                                <input type="button" class="button" id="upload_logo_button" value="Upload Logo">
                                <input type="hidden" name="logo_url" id="logo_url" value="<?php echo esc_attr($settings['logo_url']); ?>">
                                <?php if (!empty($settings['logo_url'])): ?>
                                    <br><button type="button" class="button" id="remove_logo">Remove Logo</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">From Address</th>
                        <td>
                            <p><strong>Company Name</strong></p>
                            <input type="text" name="from_company_name" value="<?php echo esc_attr($settings['from_company_name']); ?>" class="regular-text">
                            
                            <p><strong>Tagline</strong></p>
                            <input type="text" name="from_tagline" value="<?php echo esc_attr($settings['from_tagline']); ?>" class="regular-text">
                            
                            <p><strong>Address</strong></p>
                            <textarea name="from_address" rows="4" class="large-text"><?php echo esc_textarea($settings['from_address']); ?></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Enable Barcode</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_barcode" value="1" <?php checked($settings['enable_barcode'], 1); ?>>
                                Show barcode on invoice
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Auto-send Invoice</th>
                        <td>
                            <p>Send invoice PDF automatically when order status changes to:</p>
                            <?php 
                            $wc_statuses = wc_get_order_statuses();
                            $auto_send_statuses = isset($settings['auto_send_statuses']) ? $settings['auto_send_statuses'] : array('completed');
                            ?>
                            <?php foreach ($wc_statuses as $status_key => $status_label): 
                                $status_key = str_replace('wc-', '', $status_key);
                            ?>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="checkbox" name="auto_send_statuses[]" value="<?php echo esc_attr($status_key); ?>" <?php checked(in_array($status_key, $auto_send_statuses)); ?>>
                                    <?php echo esc_html($status_label); ?>
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Logo upload
            $('#upload_logo_button').click(function(e) {
                e.preventDefault();
                var image = wp.media({
                    title: 'Upload Logo',
                    multiple: false
                }).open().on('select', function(e) {
                    var uploaded_image = image.state().get('selection').first();
                    var image_url = uploaded_image.toJSON().url;
                    $('#logo_url').val(image_url);
                    $('.logo-upload-section').prepend('<img src="' + image_url + '" alt="Logo" style="max-width: 200px; margin-bottom: 10px; display: block;">');
                    $('#remove_logo').show();
                });
            });
            
            // Remove logo
            $('#remove_logo').click(function() {
                $('#logo_url').val('');
                $('.logo-upload-section img').remove();
                $(this).hide();
            });
        });
        </script>
        <?php
    }
    
    private function save_settings() {
        if (!wp_verify_nonce($_POST['wc_pdf_invoice_nonce'], 'wc_pdf_invoice_settings')) {
            return;
        }
        
        $settings = array(
            'logo_url' => sanitize_url($_POST['logo_url']),
            'from_company_name' => sanitize_text_field($_POST['from_company_name']),
            'from_tagline' => sanitize_text_field($_POST['from_tagline']),
            'from_address' => sanitize_textarea_field($_POST['from_address']),
            'enable_barcode' => isset($_POST['enable_barcode']) ? 1 : 0,
            'auto_send_statuses' => isset($_POST['auto_send_statuses']) ? array_map('sanitize_text_field', $_POST['auto_send_statuses']) : array()
        );
        
        update_option('wc_pdf_invoice_settings', $settings);
        
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    
    private function get_default_settings() {
        return array(
            'logo_url' => '',
            'from_company_name' => get_bloginfo('name'),
            'from_tagline' => 'YOUR TAGLINE GOES HERE',
            'from_address' => "Eshoppe - Online Shopping\nShoppe Hub\nMaple Avenue\nCalifornia, CA 90260",
            'enable_barcode' => 1,
            'auto_send_statuses' => array() // Disabled by default until Dompdf is installed
        );
    }
}

new WC_PDF_Invoice_Settings();