<?php

namespace App\Services;

class ContentFilterService
{
    private array $ngWords = [
        "http",
        "www.",
        "<script",
        "<php",
        "</script>",
        "eval(",
        "document.cookie",
        "onerror=",
        "img onerror=",
        "<iframe",
        "<a href=",
        "SELECT *",
        "DROP TABLE",
        "INSERT INTO",
        "' OR 1=1",
        "まんこ",
        "マンコ",
    ];

    public function containsNgWord(string $text): bool
    {
        foreach ($this->ngWords as $word) {
            if (str_contains($text, $word)) {
                return true;
            }
        }

        return false;
    }
}
