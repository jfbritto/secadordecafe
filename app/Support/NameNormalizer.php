<?php

namespace App\Support;

class NameNormalizer
{
    /** Conectivos que ficam em minúsculo no meio do nome (PT-BR). */
    private const LOWERCASE_PARTICLES = ['da', 'de', 'di', 'do', 'du', 'das', 'des', 'dos', 'e'];

    /**
     * Padroniza nomes pra título: primeira letra de cada palavra em maiúscula,
     * preservando conectivos ("João da Silva", "Maria dos Santos").
     * O primeiro token sempre é capitalizado, mesmo se for conectivo.
     */
    public static function normalize(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $trimmed = trim(preg_replace('/\s+/', ' ', $name) ?? '');
        if ($trimmed === '') {
            return $trimmed;
        }

        $words = explode(' ', mb_strtolower($trimmed, 'UTF-8'));
        foreach ($words as $i => $word) {
            if ($i > 0 && in_array($word, self::LOWERCASE_PARTICLES, true)) {
                continue;
            }
            $words[$i] = mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8')
                . mb_substr($word, 1, null, 'UTF-8');
        }

        return implode(' ', $words);
    }
}
