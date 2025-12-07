<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoProfanity implements Rule
{
    protected $badWords = [];

    public function __construct()
    {
        $this->badWords = config('profanities.words', ['puta','carajo','mierda','idiota','pendejo']);
    }

    public function passes($attribute, $value)
    {
        $text = mb_strtolower($value, 'UTF-8');
        // remove accents
        if (function_exists('\Illuminate\Support\Str::ascii')) {
            $text = \Illuminate\Support\Str::ascii($text);
        }
        // remove non-alphanumeric
        $norm = preg_replace('/[^a-z0-9\s]/', ' ', $text);

        foreach ($this->badWords as $word) {
            $w = preg_quote(mb_strtolower($word, 'UTF-8'), '/');
            if (preg_match('/\b' . $w . '\b/', $norm)) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'ÑO,ÑO,ÑO, ese lenguaje aqui no >:(';
    }
}
