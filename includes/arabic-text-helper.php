<?php
// includes/arabic-text-helper.php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WC_PDF_Arabic_Helper {
    
    /**
     * Detect if text contains Arabic characters
     */
    public static function detect_arabic($text) {
        return preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }
    
    /**
     * Fix Arabic text display issues in PDFs
     * This is a simple approach - for complex Arabic text, you might need a more sophisticated library
     */
    public static function fix_arabic_text($text) {
        if (empty($text)) {
            return $text;
        }
        
        // If text contains Arabic characters, apply basic fixes
        if (self::detect_arabic($text)) {
            // Remove any problematic characters that might cause display issues
            $text = preg_replace('/[\x{200C}\x{200D}\x{200E}\x{200F}]/u', '', $text);
            
            // Ensure proper encoding
            if (!mb_check_encoding($text, 'UTF-8')) {
                $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            }
            
            return $text;
        }
        
        return $text;
    }
    
    /**
     * Determine text direction based on content
     */
    public static function get_text_direction($text) {
        if (self::detect_arabic($text)) {
            return 'rtl';
        }
        return 'ltr';
    }
    
    /**
     * Get appropriate CSS class for text direction
     */
    public static function get_text_class($text) {
        return self::detect_arabic($text) ? 'arabic-text' : 'ltr-text';
    }
}