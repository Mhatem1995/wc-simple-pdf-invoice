# WC Simple PDF Invoice

A comprehensive, modern PDF invoice generator plugin for WooCommerce with full Arabic and RTL language support.

## 🚀 Features

### Core Features
- **Professional PDF Invoices**: Generate beautiful, professional-looking PDF invoices
- **Modern Design**: Clean, contemporary invoice template with customizable branding
- **Multi-language Support**: Full support for Arabic, Hebrew, and other RTL languages
- **HPOS Compatible**: Fully compatible with WooCommerce High-Performance Order Storage
- **Product Images**: Include product thumbnails in invoices
- **Barcode Support**: Generate Code128 barcodes for invoice tracking
- **QR Code Integration**: Add QR codes linking to ordered products
- **Email Integration**: Automatically send invoices via email
- **Admin & Customer Access**: Both admins and customers can download invoices

### Arabic & RTL Language Features
- **Arabic Text Detection**: Automatically detects and properly formats Arabic text
- **RTL Text Direction**: Correct right-to-left text alignment for Arabic content
- **Mixed Content Support**: Handles mixed Arabic/English text seamlessly
- **Unicode Normalization**: Proper handling of Arabic characters and diacritics
- **Font Optimization**: Uses Tahoma and DejaVu Sans fonts for optimal Arabic rendering

### Customization Options
- **Company Branding**: Add your logo, company name, and tagline
- **Address Configuration**: Customize your business address information
- **Invoice Numbering**: Configurable invoice numbering system
- **Conditional Features**: Enable/disable barcodes and QR codes as needed
- **Auto-send Settings**: Configure automatic email sending based on order status

## 📋 Requirements

- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher
- **External Libraries Required**:
  - Dompdf (for PDF generation)
  - phpqrcode (optional, for QR code functionality)

## 🛠 Installation

### Step 1: Plugin Installation
1. Download the plugin files
2. Upload to `/wp-content/plugins/wc-simple-pdf-invoice/`
3. Activate the plugin through the WordPress admin panel
4. Navigate to **WooCommerce > PDF Invoice** in your admin menu

### Step 2: Install Required Libraries

#### Install Dompdf (Required)
1. Download Dompdf from: https://github.com/dompdf/dompdf/releases
2. Extract the downloaded file
3. Create folder: `wp-content/plugins/wc-simple-pdf-invoice/vendor/dompdf/`
4. Copy all Dompdf files into this folder
5. Ensure `autoload.inc.php` exists in the dompdf folder

#### Install phpqrcode (Optional - for QR codes)
1. Download phpqrcode from: https://sourceforge.net/projects/phpqrcode/
2. Extract the downloaded file
3. Create folder: `wp-content/plugins/wc-simple-pdf-invoice/vendor/phpqrcode/`
4. Copy all phpqrcode files into this folder
5. Ensure `qrlib.php` exists in the phpqrcode folder

### Step 3: Configuration
1. Go to **WooCommerce > PDF Invoice** in your WordPress admin
2. Configure your company details, logo, and preferences
3. Test the plugin by generating an invoice from any order

## ⚙️ Configuration

### Basic Settings

#### Company Information
- **Company Logo**: Upload your business logo (PNG, JPG formats supported)
- **Company Name**: Your business name (supports Arabic text)
- **Tagline**: Your business tagline or slogan (supports Arabic text)
- **Business Address**: Full business address (supports Arabic addresses)

#### Features
- **Enable Barcode**: Show Code128 barcode on invoices for tracking
- **Enable QR Code**: Include QR codes linking to ordered products
- **Auto-send Invoice**: Automatically email invoices when order status changes

#### Email Settings
Configure which order statuses trigger automatic invoice emails:
- Processing
- Completed
- On-hold
- Custom statuses

### Arabic Language Configuration

The plugin automatically detects and handles Arabic text. No additional configuration needed for:
- Arabic product names
- Arabic customer information
- Arabic addresses
- Mixed Arabic/English content

## 📄 Usage

### For Administrators

#### Manual Invoice Generation
1. Go to **WooCommerce > Orders**
2. Open any order
3. Click **"Download Invoice PDF"** button
4. PDF will be generated and downloaded automatically

#### Bulk Operations
- Download multiple invoices from the orders list
- Send invoices via email manually
- Regenerate invoices if needed

### For Customers

#### Customer Account
1. Customers can access their invoices from **My Account > Orders**
2. **"Download Invoice PDF"** button appears for each completed order
3. Invoices are generated on-demand

#### Email Delivery
- Automatic email delivery based on configured order statuses
- Invoices attached as PDF files
- Professional HTML email template

### Invoice Content

Each invoice includes:
- **Header**: Company logo and invoice title
- **Company Information**: Name, tagline, and address
- **Customer Details**: Billing and shipping addresses with phone numbers
- **Invoice Information**: Invoice number, date, order number, customer number
- **Product Table**: Items with images, quantities, prices
- **Totals**: Subtotal, shipping, tax, and final total
- **Payment Method**: How the order was paid
- **Optional Elements**: Barcode and QR code (if enabled)

## 🌐 Multi-language Support

### Arabic Language Features
- **Automatic Detection**: Recognizes Arabic text automatically
- **Proper Rendering**: Uses appropriate fonts (Tahoma, DejaVu Sans)
- **RTL Layout**: Right-to-left text direction for Arabic content
- **Mixed Content**: Handles Arabic + English text seamlessly

### Supported Languages
- ✅ Arabic (العربية)
- ✅ Hebrew (עברית)
- ✅ English
- ✅ Any UTF-8 encoded language
- ✅ Mixed language content

### Font Support
The plugin uses optimized fonts for multi-language support:
- **Primary**: Tahoma (excellent Arabic support)
- **Fallback**: DejaVu Sans, Arial Unicode MS
- **Character Coverage**: Arabic, Hebrew, Latin, Numbers, Symbols

## 🔧 Technical Details

### File Structure
```
wc-simple-pdf-invoice/
├── wc-simple-pdf-invoice.php     # Main plugin file
├── assets/
│   └── css/
│       ├── admin.css             # Admin panel styles
│       └── invoice.css           # Invoice template styles
├── includes/
│   ├── arabic-text-helper.php    # Arabic/RTL text processing
│   ├── email-handler.php         # Email functionality
│   ├── order-data.php           # Order data extraction
│   ├── pdf-generator.php        # PDF generation logic
│   └── settings-page.php        # Admin settings page
├── templates/
│   └── invoice-template.php      # Invoice HTML template
└── vendor/                      # External libraries folder
    ├── dompdf/                  # PDF generation library
    └── phpqrcode/               # QR code generation library
```

### Database Storage
- Settings stored in `wp_options` as `wc_pdf_invoice_settings`
- No additional database tables required
- Compatible with WordPress multisite

### Performance
- **On-demand Generation**: PDFs generated only when requested
- **Caching**: Generated barcodes and QR codes cached for reuse
- **Memory Efficient**: Optimized for large orders with many products
- **Fast Processing**: Minimal server resources required

## 🔒 Security Features

- **Nonce Verification**: All admin actions protected with WordPress nonces
- **Permission Checks**: Proper capability checking for admin functions
- **Customer Validation**: Customers can only access their own invoices
- **Sanitization**: All input properly sanitized and validated
- **File Security**: Generated files stored in protected directories

## 🐛 Troubleshooting

### Common Issues

#### "Dompdf library not found"
**Solution**: Install Dompdf library as described in installation steps.

#### "PDF generation failed"
**Possible Causes**:
- Missing Dompdf library
- PHP memory limit too low
- File permission issues

**Solutions**:
1. Verify Dompdf installation
2. Increase PHP memory limit to 256MB or higher
3. Check folder permissions (755 for folders, 644 for files)

#### Arabic text not displaying correctly
**Solutions**:
1. Ensure UTF-8 encoding in database
2. Verify Tahoma font availability
3. Check Arabic text helper is loaded
4. Use the Arabic test script to debug

#### QR codes not generating
**Cause**: phpqrcode library not installed
**Solution**: Install phpqrcode as described in installation steps

### Debug Mode
Add this to your `wp-config.php` for debugging:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check `/wp-content/debug.log` for error messages.

## 🔄 Updates & Maintenance

### Version History
- **v1.0.1**: Enhanced Arabic support, fixed function redeclaration error
- **v1.0.0**: Initial release with basic PDF generation

### Backup Recommendations
Before updating:
1. Backup your website
2. Export plugin settings
3. Test on staging environment first

### Performance Optimization
- Regularly clean generated barcode/QR code cache
- Monitor invoice file sizes for large product catalogs
- Consider CDN for product images if performance issues occur

## 🤝 Support & Contributing

### Getting Help
1. Check this README first
2. Review troubleshooting section
3. Use the Arabic test script for language issues
4. Check WordPress error logs

### Feature Requests
This plugin is designed to be simple yet comprehensive. Feature requests should align with the core purpose of PDF invoice generation.

### Bug Reports
When reporting bugs, please include:
- WordPress version
- WooCommerce version
- PHP version
- Plugin version
- Error messages
- Steps to reproduce

## 📝 License

This plugin is released under the GPL v2 or later license.

## 🙏 Credits

- **Dompdf**: PDF generation library
- **phpqrcode**: QR code generation library
- **WooCommerce**: E-commerce platform integration
- **WordPress**: Content management system

---

**Made with ❤️ for the WordPress community** by Marwan Hatem

*Supporting businesses worldwide with professional invoice generation*
