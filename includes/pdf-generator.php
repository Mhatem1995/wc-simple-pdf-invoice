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
        
        // Generate barcode if enabled
        if (isset($invoice_data['enable_barcode']) && $invoice_data['enable_barcode']) {
            $barcode_url = $this->generate_barcode($invoice_data['invoice_number']);
            $invoice_data['barcode_url'] = $barcode_url;
        }
        
        // Generate HTML content
        $html = $this->generate_html($invoice_data);
        
        // Configure Dompdf with better Arabic support
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('fontSubsetting', false);
        $options->set('isFontSubsettingEnabled', false);
        $options->set('chroot', realpath(WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR));
        
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
        
        // Generate barcode if enabled
        if (isset($invoice_data['enable_barcode']) && $invoice_data['enable_barcode']) {
            $barcode_url = $this->generate_barcode($invoice_data['invoice_number']);
            $invoice_data['barcode_url'] = $barcode_url;
        }
        
        $html = $this->generate_html($invoice_data);
        
        // Configure Dompdf with better Arabic support
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('fontSubsetting', false);
        $options->set('isFontSubsettingEnabled', false);
        $options->set('chroot', realpath(WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR));
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->output();
    }
    
    /**
     * Generate a real working barcode using Code128
     */
    public function generate_barcode($data, $width = 300, $height = 60) {
        // Create uploads directory for barcodes if it doesn't exist
        $upload_dir = wp_upload_dir();
        $barcode_dir = $upload_dir['basedir'] . '/wc-pdf-invoices/barcodes/';
        
        if (!file_exists($barcode_dir)) {
            wp_mkdir_p($barcode_dir);
        }
        
        $filename = 'barcode-' . sanitize_file_name($data) . '.png';
        $barcode_file = $barcode_dir . $filename;
        
        // Check if barcode already exists
        if (file_exists($barcode_file)) {
            return $upload_dir['baseurl'] . '/wc-pdf-invoices/barcodes/' . $filename;
        }
        
        // Generate barcode using improved Code128 implementation
        $barcode_image = $this->create_working_barcode($data, $width, $height);
        
        if ($barcode_image && imagepng($barcode_image, $barcode_file)) {
            imagedestroy($barcode_image);
            return $upload_dir['baseurl'] . '/wc-pdf-invoices/barcodes/' . $filename;
        }
        
        return false;
    }
    
    /**
     * Create a working Code128 barcode
     */
    private function create_working_barcode($text, $width = 300, $height = 60) {
        // Code128 encoding tables
        $code128_chars = array(
            ' ', '!', '"', '#', '$', '%', '&', "'", '(', ')', '*', '+', ',', '-', '.', '/',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?',
            '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', '\\', ']', '^', '_'
        );
        
        $code128_patterns = array(
            '11011001100', '11001101100', '11001100110', '10010011000', '10010001100',
            '10001001100', '10011001000', '10011000100', '10001100100', '11001001000',
            '11001000100', '11000100100', '10110011100', '10011011100', '10011001110',
            '10111001100', '10011101100', '10011100110', '11001110010', '11001011100',
            '11001001110', '11011100100', '11001110100', '11101101110', '11101001100',
            '11100101100', '11100100110', '11101100100', '11100110100', '11100110010',
            '11011011000', '11011000110', '11000110110', '10100011000', '10001011000',
            '10001000110', '10110001000', '10001101000', '10001100010', '11010001000',
            '11000101000', '11000100010', '10110111000', '10110001110', '10001101110',
            '10111011000', '10111000110', '10001110110', '11101110110', '11010001110',
            '11000101110', '11011101000', '11011100010', '11011101110', '11101011000',
            '11101000110', '11100010110', '11101101000', '11101100010', '11100011010',
            '11101111010', '11001000010', '11110001010', '10100110000', '10100001100',
            '10010110000', '10010000110', '10000101100', '10000100110', '10110010000',
            '10110000100', '10011010000', '10011000010', '10000110100', '10000110010',
            '11000010010', '11001010000', '11110111010', '11000010100', '10001111010',
            '10100111100', '10010111100', '10010011110', '10111100100', '10011110100',
            '10011110010', '11110100100', '11110010100', '11110010010', '11011011110',
            '11011110110', '11110110110', '10101111000', '10100011110', '10001011110',
            '10111101000', '10111100010', '11110101000', '11110100010', '10111011110',
            '10111101110', '11101011110', '11110101110', '11010000100', '11010010000',
            '11010011100', '1100011101011'
        );
        
        // Create image
        $image = imagecreate($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);
        
        // Prepare barcode data
        $barcode_data = '';
        
        // Start Code B
        $barcode_data .= $code128_patterns[104]; // START B
        
        $checksum = 104;
        $position = 1;
        
        // Add characters
        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            $char_index = array_search($char, $code128_chars);
            
            if ($char_index !== false) {
                $barcode_data .= $code128_patterns[$char_index];
                $checksum += ($char_index * $position);
                $position++;
            }
        }
        
        // Add checksum
        $checksum = $checksum % 103;
        $barcode_data .= $code128_patterns[$checksum];
        
        // Stop pattern
        $barcode_data .= $code128_patterns[106]; // STOP
        $barcode_data .= '11'; // Termination bars
        
        // Draw barcode
        $bar_width = ($width - 20) / strlen($barcode_data);
        $x = 10;
        $bar_height = $height - 20;
        
        for ($i = 0; $i < strlen($barcode_data); $i++) {
            if ($barcode_data[$i] == '1') {
                imagefilledrectangle($image, $x, 5, $x + $bar_width - 1, $bar_height, $black);
            }
            $x += $bar_width;
        }
        
        // Add text below barcode
        $font_size = 3;
        $text_width = imagefontwidth($font_size) * strlen($text);
        $text_x = ($width - $text_width) / 2;
        $text_y = $height - 12;
        
        imagestring($image, $font_size, $text_x, $text_y, $text, $black);
        
        return $image;
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
    
    /**
     * Detect Arabic text in string
     */
    public function detect_arabic($text) {
        return preg_match('/[\x{0600}-\x{06FF}]/u', $text);
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
