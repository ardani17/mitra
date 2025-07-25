<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format nilai rupiah dengan format yang mudah dibaca
     * 400000 -> Rp 400rb
     * 4000000 -> Rp 4jt
     * 10000000 -> Rp 10jt
     * 100000000 -> Rp 100jt
     * 1000000000 -> Rp 1M
     */
    public static function formatRupiah($value, $showRp = true)
    {
        if (!$value || $value == 0) {
            return $showRp ? 'Rp 0' : '0';
        }

        $prefix = $showRp ? 'Rp ' : '';
        
        if ($value >= 1000000000) {
            // Milyar (1M+)
            $formatted = number_format($value / 1000000000, 1);
            $formatted = rtrim(rtrim($formatted, '0'), '.');
            return $prefix . $formatted . 'M';
        } elseif ($value >= 1000000) {
            // Juta (1jt+)
            $formatted = number_format($value / 1000000, 0);
            return $prefix . $formatted . 'jt';
        } elseif ($value >= 1000) {
            // Ribu (1rb+)
            $formatted = number_format($value / 1000, 0);
            return $prefix . $formatted . 'rb';
        } else {
            // Kurang dari 1000
            return $prefix . number_format($value, 0);
        }
    }

    /**
     * Format nilai rupiah pendek untuk tabel
     */
    public static function formatRupiahShort($value)
    {
        return self::formatRupiah($value, false);
    }

    /**
     * Format nilai rupiah lengkap dengan separator
     * 1000000 -> Rp 1.000.000
     */
    public static function formatRupiahFull($value, $showRp = true)
    {
        if (!$value || $value == 0) {
            return $showRp ? 'Rp 0' : '0';
        }

        $prefix = $showRp ? 'Rp ' : '';
        return $prefix . number_format($value, 0, ',', '.');
    }
}

// Fungsi helper global untuk backward compatibility
if (!function_exists('formatRupiah')) {
    function formatRupiah($value, $showRp = true)
    {
        return \App\Helpers\FormatHelper::formatRupiah($value, $showRp);
    }
}

if (!function_exists('formatRupiahShort')) {
    function formatRupiahShort($value)
    {
        return \App\Helpers\FormatHelper::formatRupiahShort($value);
    }
}

if (!function_exists('formatRupiahFull')) {
    function formatRupiahFull($value, $showRp = true)
    {
        return \App\Helpers\FormatHelper::formatRupiahFull($value, $showRp);
    }
}
