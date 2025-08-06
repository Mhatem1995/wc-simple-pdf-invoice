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
        
        // Configure Dompdf
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
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
        
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->output();
    }
    
    private function generate_html($data) {
        ob_start();
        include WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'templates/invoice-template.php';
        return ob_get_clean();
    }
    
    private function check_dompdf() {
        return file_exists(WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'vendor/dompdf/autoload.inc.php');
    }
}