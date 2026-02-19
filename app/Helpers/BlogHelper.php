<?php

if (!function_exists('calculateReadTime')) {
    /**
     * Calculate read time from HTML content.
     *
     * @param string $html
     * @return int
     */
    function calculateReadTime(string $html): int
    {
        $text      = strip_tags($html);
        $wordCount = str_word_count($text);
        return max(1, (int) ceil($wordCount / 200));
    }
}
