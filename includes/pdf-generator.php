<?php
// includes/pdf-generator.php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WC_PDF_Generator {
    
    public function generate_and_download($order) {
        // Check if Dompdf is available
        if (!$this->check_dompdf()) {
            wp_die('Dompdf library not found. Please install it first.');
        }
        
        require_once WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'vendor/dompdf/autoload.inc.php';
        
        // Get invoice data
        $invoice_data = WC_PDF_Order_Data::get_invoice_data($order);
        if (!$invoice_data) {
            wp_die('Could not generate invoice data');
        }
        
        // Generate HTML content
        $html = $this->generate_html($invoice_data);
        
        // Configure Dompdf with Arabic support
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans'); // Better Unicode support
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('fontSubsetting', false);
        $options->set('isFontSubsettingEnabled', false);
        
        $dompdf = new \Dompdf\Dompdf($options);
        
        // Set the HTML with UTF-8 encoding
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Output PDF
        $filename = 'invoice-' . $invoice_data['invoice_number'] . '.pdf';
        $dompdf->stream($filename, array('Attachment' => true));
        exit;
    }
    
    public function generate_pdf_content($order) {
        if (!$this->check_dompdf()) {
            return false;
        }
        
        require_once WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'vendor/dompdf/autoload.inc.php';
        
        $invoice_data = WC_PDF_Order_Data::get_invoice_data($order);
        if (!$invoice_data) {
            return false;
        }
        
        $html = $this->generate_html($invoice_data);
        
        // Configure Dompdf with Arabic support
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans'); // Better Unicode support
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('fontSubsetting', false);
        $options->set('isFontSubsettingEnabled', false);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->output();
    }
    
    /**
     * Generate QR code for product links
     */
    public function generate_qr_code($data, $filename) {
        // Check if phpqrcode is available
        if (!$this->check_phpqrcode()) {
            return false;
        }
        
        require_once WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'vendor/phpqrcode/qrlib.php';
        
        // Create uploads directory for QR codes if it doesn't exist
        $upload_dir = wp_upload_dir();
        $qr_dir = $upload_dir['basedir'] . '/wc-pdf-invoices/qr-codes/';
        
        if (!file_exists($qr_dir)) {
            wp_mkdir_p($qr_dir);
        }
        
        $qr_file = $qr_dir . $filename . '.png';
        
        // Generate QR code
        QRcode::png($data, $qr_file, QR_ECLEVEL_L, 4, 2);
        
        return file_exists($qr_file) ? $upload_dir['baseurl'] . '/wc-pdf-invoices/qr-codes/' . $filename . '.png' : false;
    }
    
    /**
     * Generate product links QR code data
     */
    public function generate_product_links_qr($items) {
        if (empty($items)) {
            return false;
        }
        
        // Create a JSON structure with product links
        $product_links = array();
        foreach ($items as $item) {
            if (!empty($item['product_url'])) {
                $product_links[] = array(
                    'name' => $item['name'],
                    'url' => $item['product_url']
                );
            }
        }
        
        if (empty($product_links)) {
            return false;
        }
        
        // Create a simple text format for QR code
        $qr_data = "Products from this invoice:\n";
        foreach ($product_links as $link) {
            $qr_data .= $link['name'] . ": " . $link['url'] . "\n";
        }
        
        return $qr_data;
    }
    
    private function generate_html($data) {
        ob_start();
        include WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'templates/invoice-template.php';
        return ob_get_clean();
    }
    
    private function check_dompdf() {
        return file_exists(WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'vendor/dompdf/autoload.inc.php');
    }
    
    private function check_phpqrcode() {
        return file_exists(WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'vendor/phpqrcode/qrlib.php');
    }
}
