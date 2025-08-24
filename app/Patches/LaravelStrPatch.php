<?php

namespace App\Patches;

/**
 * This class provides patches for Laravel's Str class to fix issues
 * with mb_split function in different environments
 */
class LaravelStrPatch
{
    /**
     * Apply all necessary patches for Laravel Str class
     *
     * @return void
     */
    public static function apply()
    {
        // Ensure mb_split function exists in global namespace
        if (!function_exists('mb_split')) {
            /**
             * Split a string using a regular expression with multibyte support
             *
             * @param string $pattern
             * @param string $string
             * @return array
             */
            function mb_split($pattern, $string)
            {
                if (function_exists('\\mb_split')) {
                    return \mb_split($pattern, $string);
                }
                
                // Fallback implementation if mb_split is not available
                // This is a simplified version and may not work exactly like mb_split
                return preg_split("/$pattern/u", $string);
            }
        }
        
        // Ensure mb_split function exists in Illuminate\Support namespace
        if (!function_exists('Illuminate\Support\mb_split')) {
            /**
             * Split a string using a regular expression with multibyte support
             *
             * @param string $pattern
             * @param string $string
             * @return array
             */
            eval('
                namespace Illuminate\Support;
                if (!function_exists("Illuminate\Support\mb_split")) {
                    function mb_split($pattern, $string) {
                        if (function_exists("\\mb_split")) {
                            return \mb_split($pattern, $string);
                        }
                        
                        // Fallback implementation
                        return preg_split("/$pattern/u", $string);
                    }
                }
            ');
        }
    }
}