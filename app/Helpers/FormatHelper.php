<?php

if (!function_exists('formatRupiah')) {
    /**
     * Format nilai rupiah dengan format yang mudah dibaca
     * 400000 -> 400rb
     * 4000000 -> 4jt
     * 10000000 -> 10jt
     * 100000000 -> 100jt
     * 1000000000 -> 1M
     */
    function formatRupiah($value, $showRp = true)
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
}

if (!function_exists('formatRupiahShort')) {
    /**
     * Format nilai rupiah pendek untuk tabel
     */
    function formatRupiahShort($value)
    {
        return formatRupiah($value, false);
    }
}
