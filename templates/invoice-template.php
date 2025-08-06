<?php
// templates/invoice-template.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $data['invoice_number']; ?></title>
    <style>
        <?php 
        // Include the CSS file content for PDF generation with UTF-8 support
        $css_file = WC_SIMPLE_PDF_INVOICE_PLUGIN_DIR . 'assets/css/invoice.css';
        if (file_exists($css_file)) {
            echo file_get_contents($css_file);
        }
        ?>
        
        /* Additional Arabic and Unicode support */
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
        }
        
        body, * {
            font-family: 'DejaVu Sans', Arial, sans-serif !important;
        }
        
        /* RTL support for Arabic text */
        .rtl-support {
            direction: rtl;
            text-align: right;
        }
        
        .ltr-support {
            direction: ltr;
            text-align: left;
        }
        
        /* QR Code styling */
        .qr-code {
            text-align: center;
            margin: 15px 0;
        }
        
        .qr-code img {
            max-width: 100px;
            height: auto;
        }
        
        .qr-code-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Customer number styling */
        .customer-number {
            font-weight: bold;
            color: #4A90E2;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <h1 class="invoice-title">INVOICE</h1>
            
            <?php if (!empty($data['logo_url'])): ?>
            <div class="logo">
                <img src="<?php echo esc_url($data['logo_url']); ?>" alt="Company Logo">
            </div>
        
        <!-- Company Info -->
        <div class="company-info">
            <div class="company-name"><?php echo esc_html($data['from_company_name']); ?></div>
            <div class="company-tagline"><?php echo esc_html($data['from_tagline']); ?></div>
            <div class="company-address"><?php echo nl2br(esc_html($data['from_address'])); ?></div>
        </div>
        
        <!-- Invoice Details Section -->
        <div class="invoice-details">
            <!-- Billing Address -->
            <div class="billing-info">
                <div class="section-title">Billing Address:</div>
                <div class="info-block">
                    <?php echo esc_html($data['billing_name']); ?><br>
                    <?php echo nl2br(esc_html($data['billing_address'])); ?><br>
                    <?php if ($data['billing_email']): ?>
                        Email: <?php echo esc_html($data['billing_email']); ?><br>
                    <?php endif; ?>
                    <?php if ($data['billing_phone']): ?>
                        Phone: <?php echo esc_html($data['billing_phone']); ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Shipping Address -->
            <div class="shipping-info">
                <div class="section-title">Shipping Address:</div>
                <div class="info-block">
                    <?php if ($data['shipping_name']): ?>
                        <?php echo esc_html($data['shipping_name']); ?><br>
                        <?php echo nl2br(esc_html($data['shipping_address'])); ?>
                    <?php else: ?>
                        Same as billing address
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Invoice Info -->
            <div class="invoice-info">
                <div class="section-title">Invoice Details:</div>
                <div class="info-block">
                    <strong>Invoice Date:</strong> <?php echo esc_html($data['invoice_date']); ?><br>
                    <strong>Invoice No.:</strong> <?php echo esc_html($data['invoice_number']); ?><br>
                    <strong>Order No.:</strong> <?php echo esc_html($data['order_number']); ?><br>
                    <strong>Order Date:</strong> <?php echo esc_html($data['order_date']); ?><br>
                    <strong>Customer No.:</strong> <span class="customer-number"><?php echo esc_html($data['customer_number']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- QR Code (if enabled) -->
        <?php if (isset($data['enable_qr_code']) && $data['enable_qr_code'] && !empty($data['qr_code_url'])): ?>
        <div class="qr-code">
            <img src="<?php echo esc_url($data['qr_code_url']); ?>" alt="QR Code for Products">
            <div class="qr-code-label">Scan to view products online</div>
        </div>
        <?php endif; ?>
        
        <!-- Barcode (if enabled and QR not enabled) -->
        <?php if (isset($data['enable_barcode']) && $data['enable_barcode'] && (!isset($data['enable_qr_code']) || !$data['enable_qr_code'] || empty($data['qr_code_url']))): ?>
        <div class="barcode">
            <div class="barcode-image">|||||||||||||||||||</div>
        </div>
        <?php endif; ?>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px;">S.No</th>
                    <th style="width: 60px;">Image</th>
                    <th>Product</th>
                    <th style="width: 80px;" class="text-center">Quantity</th>
                    <th style="width: 100px;" class="text-right">Price</th>
                    <th style="width: 100px;" class="text-right">Total price</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 1; ?>
                <?php foreach ($data['items'] as $item): ?>
                <tr>
                    <td class="text-center"><?php echo $counter; ?></td>
                    <td class="text-center">
                        <?php if ($item['image']): ?>
                            <img src="<?php echo esc_url($item['image']); ?>" alt="Product" class="product-image">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; background: #f0f0f0; border-radius: 3px;"></div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($item['name']); ?></td>
                    <td class="text-center"><?php echo esc_html($item['quantity']); ?></td>
                    <td class="text-right"><?php echo wp_kses_post($item['price']); ?></td>
                    <td class="text-right"><?php echo wp_kses_post($item['total']); ?></td>
                </tr>
                <?php $counter++; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Totals Section -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="total-label">Subtotal</td>
                    <td class="total-value"><?php echo wp_kses_post($data['subtotal']); ?></td>
                </tr>
                <tr>
                    <td class="total-label">Shipping</td>
                    <td class="total-value"><?php echo $data['shipping_total'] ? wp_kses_post($data['shipping_total']) : 'Free shipping'; ?></td>
                </tr>
                <?php if (isset($data['tax_total']) && $data['tax_total']): ?>
                <tr>
                    <td class="total-label">Tax</td>
                    <td class="total-value"><?php echo wp_kses_post($data['tax_total']); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="final-total">
                    <td class="total-label">Total</td>
                    <td class="total-value"><?php echo wp_kses_post($data['total']); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="clearfix"></div>
        
        <!-- Payment Info -->
        <div class="payment-info">
            <div class="payment-method">
                <strong>Payment method:</strong> <?php echo esc_html($data['payment_method']); ?>
            </div>
        </div>
    </div>
</body>
</html>    <?php endif; ?>
        </div>
