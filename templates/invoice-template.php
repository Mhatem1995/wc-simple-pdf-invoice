<?php
// templates/invoice-template.php

// Remove duplicate function declarations - use the ones from includes/arabic-text-helper.php
?>
<!DOCTYPE html>
<html dir="auto">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Invoice #<?php echo $data['invoice_number']; ?></title>
    <style>
        @font-face {
            font-family: 'ArabicFont';
            src: local('Tahoma'), local('Arial Unicode MS'), local('DejaVu Sans');
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 15mm;
            size: A4;
        }

        body {
            font-family: 'Tahoma', 'DejaVu Sans', 'Arial Unicode MS', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            background: #fff;
        }

        .invoice-container {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #4A90E2;
            margin-bottom: 8px;
        }

        .logo {
            margin-bottom: 10px;
        }

        .logo img {
            max-height: 60px;
            max-width: 150px;
        }

        .company-info {
            background: #8B5A96;
            color: white;
            padding: 12px;
            text-align: center;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .company-tagline {
            font-size: 11px;
            margin-bottom: 8px;
        }

        .company-address {
            font-size: 10px;
            line-height: 1.2;
        }

        .invoice-details {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .billing-info, .shipping-info, .invoice-info {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            padding-right: 10px;
        }

        .invoice-info {
            padding-right: 0;
        }

        .section-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
            color: #333;
            border-bottom: 1px solid #8B5A96;
            padding-bottom: 2px;
        }

        .info-block {
            background: #f9f9f9;
            padding: 8px;
            border-radius: 3px;
            font-size: 10px;
            line-height: 1.3;
            min-height: 80px;
        }

        /* FIXED Arabic text support - Force RTL for Arabic content */
        .arabic-text {
            direction: rtl !important;
            text-align: right !important;
            font-family: 'Tahoma', 'Arial Unicode MS', 'DejaVu Sans', sans-serif !important;
            unicode-bidi: bidi-override !important;
        }

        .ltr-text {
            direction: ltr !important;
            text-align: left !important;
            unicode-bidi: normal !important;
        }

        /* Mixed content - let browser decide but prefer RTL for Arabic */
        .mixed-content {
            direction: rtl !important;
            text-align: right !important;
            unicode-bidi: plaintext !important;
        }

        /* Force RTL container for Arabic addresses */
        .arabic-address {
            direction: rtl !important;
            text-align: right !important;
            font-family: 'Tahoma', 'Arial Unicode MS', sans-serif !important;
        }

        /* Force RTL for Arabic names */
        .arabic-name {
            direction: rtl !important;
            text-align: right !important;
            font-family: 'Tahoma', 'Arial Unicode MS', sans-serif !important;
            font-weight: bold !important;
        }

        .codes-section {
            margin-bottom: 15px;
            text-align: center;
        }

        .codes-container {
            display: inline-block;
        }

        .barcode-qr-wrapper {
            display: table;
            margin: 0 auto;
        }

        .barcode-container, .qr-container {
            display: table-cell;
            vertical-align: middle;
            padding: 10px;
            text-align: center;
        }

        .barcode img {
            height: 50px;
            max-width: 250px;
        }

        .qr-code img {
            max-width: 80px;
            height: 80px;
        }

        .code-label {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }

        .items-table thead {
            background: #f5f5f5;
        }

        .items-table th {
            padding: 6px 4px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
            font-size: 10px;
        }

        .items-table td {
            padding: 5px 4px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 10px;
            vertical-align: middle;
        }

        /* Arabic product names in table */
        .items-table .arabic-product {
            direction: rtl !important;
            text-align: right !important;
            font-family: 'Tahoma', 'Arial Unicode MS', sans-serif !important;
        }

        .items-table .text-center {
            text-align: center;
        }

        .items-table .text-right {
            text-align: right;
        }

        .product-image {
            width: 30px;
            height: 30px;
            object-fit: contain;
            border-radius: 2px;
        }

        .totals-section {
            width: 250px;
            float: right;
            margin-top: 10px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 4px 8px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }

        .totals-table .total-label {
            text-align: right;
            font-weight: bold;
        }

        .totals-table .total-value {
            text-align: right;
            width: 80px;
        }

        .totals-table .final-total {
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #333;
            border-bottom: 3px double #333;
        }

        .payment-info {
            clear: both;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .customer-number {
            font-weight: bold;
            color: #4A90E2;
        }

        /* Phone number styling */
        .phone-number {
            color: #4A90E2;
            font-weight: bold;
        }

        /* Force proper Arabic rendering */
        .force-arabic {
            font-family: 'Tahoma', 'Arial Unicode MS', 'DejaVu Sans', sans-serif !important;
            direction: rtl !important;
            text-align: right !important;
            unicode-bidi: bidi-override !important;
        }

        /* Additional CSS fixes for Arabic text - Maximum specificity */
        .force-arabic,
        .arabic-text,
        div[class*="arabic"],
        span[class*="arabic"] {
            direction: rtl !important;
            text-align: right !important;
            font-family: 'Tahoma', 'Arial Unicode MS', 'DejaVu Sans', sans-serif !important;
            unicode-bidi: bidi-override !important;
        }

        /* Specific fixes for common Arabic text containers */
        .info-block .force-arabic,
        .info-block .arabic-text {
            display: block !important;
            width: 100% !important;
            direction: rtl !important;
            text-align: right !important;
        }

        /* Fix for Arabic names in bold */
        .info-block strong.force-arabic,
        .info-block strong.arabic-text {
            direction: rtl !important;
            text-align: right !important;
            display: block !important;
        }

        /* Fix for address blocks */
        .info-block div:has(.force-arabic),
        .info-block div.force-arabic {
            direction: rtl !important;
            text-align: right !important;
            unicode-bidi: bidi-override !important;
        }

        /* Fix for table cells with Arabic content */
        td.force-arabic,
        td.arabic-text,
        .items-table td.force-arabic {
            direction: rtl !important;
            text-align: right !important;
            unicode-bidi: bidi-override !important;
        }

        /* Company info section Arabic fix */
        .company-info .force-arabic,
        .company-info .arabic-text {
            direction: rtl !important;
            text-align: center !important; /* Keep center alignment for company info */
        }

        /* Ensure Arabic text in mixed content is handled correctly */
        .mixed-arabic {
            direction: rtl !important;
            text-align: right !important;
            unicode-bidi: plaintext !important;
        }

        /* Override any conflicting styles */
        [dir="ltr"] .force-arabic,
        [dir="ltr"] .arabic-text {
            direction: rtl !important;
            text-align: right !important;
        }

        /* Specific fix for customer names like "حسام مصطفى" */
        .customer-name-arabic {
            direction: rtl !important;
            text-align: right !important;
            font-family: 'Tahoma', 'Arial Unicode MS' !important;
            font-weight: bold !important;
            unicode-bidi: bidi-override !important;
        }

        @media print {
            body { 
                font-size: 10px; 
            }
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
            <?php endif; ?>
        </div>

        <!-- Company Info -->
        <div class="company-info">
            <div class="company-name <?php echo WC_PDF_Arabic_Helper::detect_arabic($data['from_company_name']) ? 'force-arabic' : ''; ?>">
                <?php echo esc_html(WC_PDF_Arabic_Helper::fix_arabic_text($data['from_company_name'])); ?>
            </div>
            <div class="company-tagline <?php echo WC_PDF_Arabic_Helper::detect_arabic($data['from_tagline']) ? 'force-arabic' : ''; ?>">
                <?php echo esc_html(WC_PDF_Arabic_Helper::fix_arabic_text($data['from_tagline'])); ?>
            </div>
            <div class="company-address <?php echo WC_PDF_Arabic_Helper::detect_arabic($data['from_address']) ? 'force-arabic' : ''; ?>">
                <?php echo nl2br(esc_html(WC_PDF_Arabic_Helper::fix_arabic_text($data['from_address']))); ?>
            </div>
        </div>

        <!-- Invoice Details Section -->
        <div class="invoice-details">
            <!-- Billing Address -->
            <div class="billing-info">
                <div class="section-title">Billing Address:</div>
                <div class="info-block">
                    <div class="<?php echo WC_PDF_Arabic_Helper::detect_arabic($data['billing_name']) ? 'force-arabic' : 'ltr-text'; ?>">
                        <strong><?php echo esc_html(WC_PDF_Arabic_Helper::fix_arabic_text($data['billing_name'])); ?></strong>
                    </div>
                    <div class="<?php echo WC_PDF_Arabic_Helper::detect_arabic($data['billing_address']) ? 'force-arabic' : 'ltr-text'; ?>">
                        <?php echo nl2br(esc_html(WC_PDF_Arabic_Helper::fix_arabic_text($data['billing_address']))); ?>
                    </div>
                    <?php if ($data['billing_email']): ?>
                        <div class="ltr-text">
                            Email: <?php echo esc_html($data['billing_email']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($data['billing_phone']): ?>
                        <div class="ltr-text">
                            Phone: <span class="phone-number"><?php echo esc_html($data['billing_phone']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Shipping Address -->
            <div class="shipping-info">
                <div class="section-title">Shipping Address:</div>
                <div class="info-block">
                    <?php if ($data['shipping_name']): ?>
                        <div class="<?php echo WC_PDF_Arabic_Helper::detect_arabic($data['shipping_name']) ? 'force-arabic' : 'ltr-text'; ?>">
                            <strong><?php echo esc_html(WC_PDF_Arabic_Helper::fix_arabic_text($data['shipping_name'])); ?></strong>
                        </div>
                        <div class="<?php echo WC_PDF_Arabic_Helper::detect_arabic($data['shipping_address']) ? 'force-arabic' : 'ltr-text'; ?>">
                            <?php echo nl2br(esc_html(WC_PDF_Arabic_Helper::fix_arabic_text($data['shipping_address']))); ?>
                        </div>
                        <?php if ($data['shipping_phone']): ?>
                            <div class="ltr-text">
                                Phone: <span class="phone-number"><?php echo esc_html($data['shipping_phone']); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <em>Same as billing address</em>
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

        <!-- Barcode and QR Code Section -->
        <?php 
        $show_barcode = isset($data['enable_barcode']) && $data['enable_barcode'] && !empty($data['barcode_url']);
        $show_qr = isset($data['enable_qr_code']) && $data['enable_qr_code'] && !empty($data['qr_code_url']);
        ?>
        
        <?php if ($show_barcode || $show_qr): ?>
        <div class="codes-section">
            <div class="codes-container">
                <?php if ($show_barcode && $show_qr): ?>
                    <!-- Both codes side by side -->
                    <div class="barcode-qr-wrapper">
                        <div class="barcode-container">
                            <div class="barcode">
                                <img src="<?php echo esc_url($data['barcode_url']); ?>" alt="Barcode">
                                <div class="code-label">Invoice: <?php echo esc_html($data['invoice_number']); ?></div>
                            </div>
                        </div>
                        <div class="qr-container">
                            <div class="qr-code">
                                <img src="<?php echo esc_url($data['qr_code_url']); ?>" alt="QR Code">
                                <div class="code-label">Scan for products</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($show_barcode): ?>
                    <!-- Only barcode -->
                    <div class="barcode">
                        <img src="<?php echo esc_url($data['barcode_url']); ?>" alt="Barcode">
                        <div class="code-label">Invoice: <?php echo esc_html($data['invoice_number']); ?></div>
                    </div>
                <?php elseif ($show_qr): ?>
                    <!-- Only QR code -->
                    <div class="qr-code">
                        <img src="<?php echo esc_url($data['qr_code_url']); ?>" alt="QR Code">
                        <div class="code-label">Scan for products</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th style="width: 40px;">Image</th>
                    <th>Product</th>
                    <th style="width: 50px;" class="text-center">Qty</th>
                    <th style="width: 70px;" class="text-right">Price</th>
                    <th style="width: 70px;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 1; ?>
                <?php foreach ($data['items'] as $item): ?>
                <tr>
                    <td class="text-center"><?php echo $counter; ?></td>
                    <td class="text-center">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?php echo esc_url($item['image']); ?>" alt="Product" class="product-image">
                        <?php else: ?>
                            <div style="width: 30px; height: 30px; background: #f0f0f0; border-radius: 2px; display: inline-block;"></div>
                        <?php endif; ?>
                    </td>
                    <td class="<?php echo WC_PDF_Arabic_Helper::detect_arabic($item['name']) ? 'force-arabic' : 'ltr-text'; ?>">
                        <?php echo esc_html(WC_PDF_Arabic_Helper::fix_arabic_text($item['name'])); ?>
                    </td>
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
                    <td class="total-label">Subtotal:</td>
                    <td class="total-value"><?php echo wp_kses_post($data['subtotal']); ?></td>
                </tr>
                <tr>
                    <td class="total-label">Shipping:</td>
                    <td class="total-value"><?php echo wp_kses_post($data['shipping_total']); ?></td>
                </tr>
                <?php if (isset($data['tax_total']) && $data['tax_total']): ?>
                <tr>
                    <td class="total-label">Tax:</td>
                    <td class="total-value"><?php echo wp_kses_post($data['tax_total']); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="final-total">
                    <td class="total-label">Total:</td>
                    <td class="total-value"><?php echo wp_kses_post($data['total']); ?></td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>

        <!-- Payment Info -->
        <div class="payment-info">
            <strong>Payment method:</strong> 
            <span class="<?php echo WC_PDF_Arabic_Helper::detect_arabic($data['payment_method']) ? 'force-arabic' : 'ltr-text'; ?>">
                <?php echo esc_html(WC_PDF_Arabic_Helper::fix_arabic_text($data['payment_method'])); ?>
            </span>
        </div>
    </div>
</body>
</html>
