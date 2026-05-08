<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ReadingService
{
    public function generate(string $text): ?string
    {
       return Cache::remember("reading:$text", 86400, function () use ($text) {

    $response = Http::get('https://api.excelapi.org/language/kanji2kana', [
    'text' => $text
]);

return $response->body();
         });
    }
}