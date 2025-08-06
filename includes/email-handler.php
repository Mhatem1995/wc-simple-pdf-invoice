<?php
// includes/email-handler.php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WC_PDF_Email_Handler {
    
    public function send_invoice_email($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }
        
        $pdf_generator = new WC_PDF_Generator();
        $pdf_content = $pdf_generator->generate_pdf_content($order);
        
        if (!$pdf_content) {
            return false;
        }
        
        // Prepare email
        $to = $order->get_billing_email();
        $subject = sprintf('Invoice for Order #%s', $order->get_order_number());
        $message = $this->get_email_template($order);
        
        // Create temporary file for attachment
        $upload_dir = wp_upload_dir();
        $temp_file = $upload_dir['basedir'] . '/invoice-' . $order_id . '-' . time() . '.pdf';
        file_put_contents($temp_file, $pdf_content);
        
        // Email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        // Send email with attachment
        $sent = wp_mail($to, $subject, $message, $headers, array($temp_file));
        
        // Clean up temporary file
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
        
        return $sent;
    }
    
    private function get_email_template($order) {
        $template = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Invoice for Your Order</h2>
            </div>
            
            <div class="content">
                <p>Dear ' . $order->get_formatted_billing_full_name() . ',</p>
                
                <p>Thank you for your order! Please find your invoice attached as a PDF.</p>
                
                <p><strong>Order Details:</strong></p>
                <ul>
                    <li>Order Number: #' . $order->get_order_number() . '</li>
                    <li>Order Date: ' . $order->get_date_created()->format('F j, Y') . '</li>
                    <li>Order Total: ' . $order->get_formatted_order_total() . '</li>
                </ul>
                
                <p>If you have any questions about your order, please don\'t hesitate to contact us.</p>
                
                <p>Best regards,<br>' . get_bloginfo('name') . '</p>
            </div>
            
            <div class="footer">
                <p>This is an automated email. Please do not reply to this message.</p>
            </div>
        </body>
        </html>';
        
        return $template;
    }
}