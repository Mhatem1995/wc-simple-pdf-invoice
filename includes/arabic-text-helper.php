<?php
// includes/arabic-text-helper.php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WC_PDF_Arabic_Helper {
    
    /**
     * Enhanced Arabic detection - covers more Unicode ranges
     */
    public static function detect_arabic($text) {
        // More comprehensive Arabic Unicode ranges
        return preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
    }
    
    /**
     * Detect if text contains Hebrew characters
     */
    public static function detect_hebrew($text) {
        return preg_match('/[\x{0590}-\x{05FF}]/u', $text);
    }
    
    /**
     * Detect if text is RTL (Right-to-Left)
     */
    public static function detect_rtl($text) {
        return self::detect_arabic($text) || self::detect_hebrew($text);
    }
    
    /**
     * Fix Arabic text display issues in PDFs
     * Enhanced to handle better Arabic text rendering
     */
    public static function fix_arabic_text($text) {
        if (empty($text)) {
            return $text;
        }
        
        // If text contains RTL characters, apply fixes
        if (self::detect_rtl($text)) {
            // Remove zero-width characters and direction marks that might cause issues
            $text = preg_replace('/[\x{200C}\x{200D}\x{200E}\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', '', $text);
            
            // Remove any HTML entities that might interfere
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            
            // Ensure proper UTF-8 encoding
            if (!mb_check_encoding($text, 'UTF-8')) {
                $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            }
            
            // Normalize the text if Normalizer class is available
            if (class_exists('Normalizer')) {
                $text = Normalizer::normalize($text, Normalizer::FORM_C);
            }
            
            // Specific fix for names like "حسام مصطفى"
            // Ensure there are proper spaces between Arabic words
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
            
            return $text;
        }
        
        return $text;
    }
    
    /**
     * Determine text direction based on content
     */
    public static function get_text_direction($text) {
        if (self::detect_rtl($text)) {
            return 'rtl';
        }
        return 'ltr';
    }
    
    /**
     * Get appropriate CSS class for text direction with enhanced detection
     */
    public static function get_text_class($text) {
        if (self::detect_rtl($text)) {
            // Use force-arabic for stronger CSS application
            return 'force-arabic';
        }
        return 'ltr-text';
    }
    
    /**
     * Clean and prepare text for PDF rendering
     */
    public static function prepare_for_pdf($text) {
        if (empty($text)) {
            return $text;
        }
        
        // Fix Arabic/RTL text
        $text = self::fix_arabic_text($text);
        
        // Handle mixed content (Arabic + English)
        if (self::detect_rtl($text) && preg_match('/[a-zA-Z0-9]/', $text)) {
            // Text contains both RTL and LTR characters
            // For mixed content, we still prefer RTL direction for Arabic-dominant text
            return $text;
        }
        
        return $text;
    }
    
    /**
     * Get font family for text based on language
     */
    public static function get_font_family($text) {
        if (self::detect_rtl($text)) {
            return "'Tahoma', 'Arial Unicode MS', 'DejaVu Sans', sans-serif";
        }
        return "'DejaVu Sans', 'Arial', sans-serif";
    }
    
    /**
     * Format address with proper RTL handling
     */
    public static function format_address($address_parts) {
        if (empty($address_parts)) {
            return '';
        }
        
        $formatted = '';
        $has_rtl = false;
        
        // Check if any part contains RTL text
        foreach ($address_parts as $part) {
            if (self::detect_rtl($part)) {
                $has_rtl = true;
                break;
            }
        }
        
        // Join address parts
        $formatted = implode("\n", array_filter($address_parts));
        
        return self::prepare_for_pdf($formatted);
    }
    
    /**
     * Debug function to check text encoding and Arabic detection
     */
    public static function debug_arabic_text($text) {
        return array(
            'original' => $text,
            'is_arabic' => self::detect_arabic($text),
            'is_rtl' => self::detect_rtl($text),
            'direction' => self::get_text_direction($text),
            'css_class' => self::get_text_class($text),
            'fixed_text' => self::fix_arabic_text($text),
            'utf8_valid' => mb_check_encoding($text, 'UTF-8'),
            'char_count' => mb_strlen($text),
            'font_family' => self::get_font_family($text)
        );
    }
}
