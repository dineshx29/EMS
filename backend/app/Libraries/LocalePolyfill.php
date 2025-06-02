<?php

if (!class_exists('Locale')) {
    /**
     * Simple Locale class polyfill for when intl extension is not available
     */
    class Locale
    {
        const DEFAULT_LOCALE = 'en';
        
        private static $defaultLocale = 'en';
        
        /**
         * Set the default locale
         */
        public static function setDefault($locale)
        {
            self::$defaultLocale = $locale ?: self::DEFAULT_LOCALE;
            return true;
        }
        
        /**
         * Get the default locale
         */
        public static function getDefault()
        {
            return self::$defaultLocale;
        }
        
        /**
         * Accept locale string
         */
        public static function acceptFromHttp($header)
        {
            // Simple implementation - just return default locale
            return self::$defaultLocale;
        }
        
        /**
         * Get display language
         */
        public static function getDisplayLanguage($locale, $in_locale = null)
        {
            return $locale;
        }
        
        /**
         * Get display name
         */
        public static function getDisplayName($locale, $in_locale = null)
        {
            return $locale;
        }
        
        /**
         * Get primary language
         */
        public static function getPrimaryLanguage($locale)
        {
            $parts = explode('_', $locale);
            return $parts[0];
        }
        
        /**
         * Get region
         */
        public static function getRegion($locale)
        {
            $parts = explode('_', $locale);
            return isset($parts[1]) ? $parts[1] : '';
        }
    }
}
