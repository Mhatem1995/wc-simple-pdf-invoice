<?php
// includes/order-data.php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WC_PDF_Order_Data {
    
    public static function get_invoice_data($order) {
        if (!$order) {
            return false;
        }
        
        $settings = get_option('wc_pdf_invoice_settings', array());
        
        // Generate invoice number (you can customize this logic)
        $invoice_number = $order->get_id();
        
        // Get order items with product images - HPOS compatible
        $items = array();
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $product_image = '';
            
            if ($product) {
                $image_id = $product->get_image_id();
                if ($image_id) {
                    $product_image = wp_get_attachment_image_url($image_id, 'thumbnail');
                }
            }
            
            $items[] = array(
                'image' => $product_image,
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'price' => $order->get_formatted_line_subtotal($item),
                'total' => $order->get_formatted_line_total($item)
            );
        }
        
        // Get order date - HPOS compatible
        $order_date_created = $order->get_date_created();
        $order_date = $order_date_created ? $order_date_created->format('m-d-Y') : current_time('m-d-Y');
        
        // Get billing and shipping addresses - HPOS compatible
        $billing_address_1 = $order->get_billing_address_1();
        $billing_address_2 = $order->get_billing_address_2();
        $billing_city = $order->get_billing_city();
        $billing_state = $order->get_billing_state();
        $billing_postcode = $order->get_billing_postcode();
        $billing_country = $order->get_billing_country();
        
        $shipping_address_1 = $order->get_shipping_address_1();
        $shipping_address_2 = $order->get_shipping_address_2();
        $shipping_city = $order->get_shipping_city();
        $shipping_state = $order->get_shipping_state();
        $shipping_postcode = $order->get_shipping_postcode();
        $shipping_country = $order->get_shipping_country();
        
        // Prepare invoice data
        $invoice_data = array(
            'invoice_number' => $invoice_number,
            'order_number' => $order->get_order_number(),
            'invoice_date' => current_time('m-d-Y'),
            'order_date' => $order_date,
            'order_status' => ucfirst($order->get_status()),
            
            // From address (company info)
            'from_company_name' => isset($settings['from_company_name']) ? $settings['from_company_name'] : get_bloginfo('name'),
            'from_tagline' => isset($settings['from_tagline']) ? $settings['from_tagline'] : 'Your tagline goes here',
            'from_address' => isset($settings['from_address']) ? $settings['from_address'] : '',
            
            // Company logo
            'logo_url' => isset($settings['logo_url']) ? $settings['logo_url'] : '',
            
            // Billing address - HPOS compatible
            'billing_name' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            'billing_address' => self::format_address(array(
                'address_1' => $billing_address_1,
                'address_2' => $billing_address_2,
                'city' => $billing_city,
                'state' => $billing_state,
                'postcode' => $billing_postcode,
                'country' => $billing_country
            )),
            'billing_email' => $order->get_billing_email(),
            'billing_phone' => $order->get_billing_phone(),
            
            // Shipping address - HPOS compatible
            'shipping_name' => trim($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name()),
            'shipping_address' => self::format_address(array(
                'address_1' => $shipping_address_1,
                'address_2' => $shipping_address_2,
                'city' => $shipping_city,
                'state' => $shipping_state,
                'postcode' => $shipping_postcode,
                'country' => $shipping_country
            )),
            
            // Order items
            'items' => $items,
            
            // Totals - HPOS compatible
            'subtotal' => wc_price($order->get_subtotal()),
            'shipping_total' => $order->get_shipping_total() > 0 ? wc_price($order->get_shipping_total()) : 'Free shipping',
            'total' => wc_price($order->get_total()),
            
            // Payment method
            'payment_method' => $order->get_payment_method_title(),
            
            // Settings
            'enable_barcode' => isset($settings['enable_barcode']) ? $settings['enable_barcode'] : 0,
        );
        
        return $invoice_data;
    }
    
    /**
     * Format address array into string - HPOS compatible
     */
    private static function format_address($address) {
        $formatted = '';
        
        if (!empty($address['address_1'])) {
            $formatted .= $address['address_1'] . "\n";
        }
        
        if (!empty($address['address_2'])) {
            $formatted .= $address['address_2'] . "\n";
        }
        
        $city_line = '';
        if (!empty($address['city'])) {
            $city_line .= $address['city'];
        }
        
        if (!empty($address['state'])) {
            $city_line .= (!empty($city_line) ? ', ' : '') . $address['state'];
        }
        
        if (!empty($address['postcode'])) {
            $city_line .= ' ' . $address['postcode'];
        }
        
        if (!empty($city_line)) {
            $formatted .= $city_line . "\n";
        }
        
        if (!empty($address['country'])) {
            $countries = WC()->countries->get_countries();
            $country_name = isset($countries[$address['country']]) ? $countries[$address['country']] : $address['country'];
            $formatted .= $country_name;
        }
        
        return trim($formatted);
    }
    
    public static function generate_barcode_data($invoice_number) {
        // Simple barcode representation - you can integrate with a real barcode library
        return str_repeat('|', 20) . ' ' . $invoice_number;
    }
}