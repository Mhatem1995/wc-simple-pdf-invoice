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
        
        // Check phpqrcode installation
        $phpqrcode_installed = file_exists(WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'vendor/phpqrcode/qrlib.php');
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
            
            <?php if (!$phpqrcode_installed): ?>
            <div class="notice notice-warning">
                <p><strong>phpqrcode Library Missing!</strong></p>
                <p>To enable QR codes linking to products, please install phpqrcode:</p>
                <ol>
                    <li>Download phpqrcode from: <a href="https://sourceforge.net/projects/phpqrcode/" target="_blank">https://sourceforge.net/projects/phpqrcode/</a></li>
                    <li>Extract the files</li>
                    <li>Copy the entire <code>phpqrcode</code> folder to: <code><?php echo WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR; ?>vendor/phpqrcode/</code></li>
                    <li>Make sure <code>qrlib.php</code> exists in the phpqrcode folder</li>
                    <li>Refresh this page</li>
                </ol>
            </div>
            <?php else: ?>
            <div class="notice notice-success">
                <p><strong>✅ phpqrcode Library Installed Successfully!</strong></p>
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
                            <p class="description">Simple barcode representation</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Enable QR Code</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_qr_code" value="1" <?php checked(isset($settings['enable_qr_code']) ? $settings['enable_qr_code'] : 0, 1); ?> <?php disabled(!$phpqrcode_installed); ?>>
                                Show QR code linking to products on invoice
                            </label>
                            <p class="description">
                                <?php if ($phpqrcode_installed): ?>
                                    QR code will contain links to all products in the order
                                <?php else: ?>
                                    <span style="color: #d63638;">Requires phpqrcode library installation</span>
                                <?php endif; ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Auto-send Invoice</th>
                        <td>
                            <p>Send invoice PDF automatically when order status changes to:</p>
                            <?php 
                            $wc_statuses = wc_get_order_statuses();
                            $auto_send_statuses = isset($settings['auto_send_statuses']) ? $settings['auto_send_statuses'] : array();
                            ?>
                            <?php foreach ($wc_statuses as $status_key => $status_label): 
                                $status_key = str_replace('wc-', '', $status_key);
                            ?>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="checkbox" name="auto_send_statuses[]" value="<?php echo esc_attr($status_key); ?>" <?php checked(in_array($status_key, $auto_send_statuses)); ?>>
                                    <?php echo esc_html($status_label); ?>
                                </label>
                            <?php endforeach; ?>
                            <p class="description">Auto-send is disabled by default until all required libraries are installed</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <div class="notice notice-info">
                <h3>Arabic Text Support</h3>
                <p>This plugin supports Arabic and other Unicode text. The PDF generator uses DejaVu Sans font which includes support for Arabic characters.</p>
                
                <h3>Installation Guide</h3>
                <h4>For Dompdf:</h4>
                <ol>
                    <li>Create folder: <code><?php echo WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR; ?>vendor/dompdf/</code></li>
                    <li>Download and extract Dompdf files into this folder</li>
                    <li>Ensure <code>autoload.inc.php</code> exists in the dompdf folder</li>
                </ol>
                
                <h4>For phpqrcode (QR Code support):</h4>
                <ol>
                    <li>Create folder: <code><?php echo WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR; ?>vendor/phpqrcode/</code></li>
                    <li>Download and extract phpqrcode files into this folder</li>
                    <li>Ensure <code>qrlib.php</code> exists in the phpqrcode folder</li>
                </ol>
            </div>
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
                    $('.logo-upload-section img').remove();
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
            'enable_qr_code' => isset($_POST['enable_qr_code']) ? 1 : 0,
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
            'enable_barcode' => 0,
            'enable_qr_code' => 0,
            'auto_send_statuses' => array() // Disabled by default until libraries are installed
        );
    }
}

new WC_PDF_Invoice_Settings();
