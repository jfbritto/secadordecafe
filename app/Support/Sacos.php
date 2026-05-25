<?php

namespace App\Support;

/**
 * Conversão entre kg e sacos pra café côco brasileiro.
 * 1 saco = 60kg (padrão da cultura — não configurável por ora).
 */
class Sacos
{
    public const KG_POR_SACO = 60.0;

    public static function deKg(float $kg): float
    {
        return round($kg / self::KG_POR_SACO, 2);
    }

    public static function paraKg(float $sacos): float
    {
        return round($sacos * self::KG_POR_SACO, 2);
    }

    /** Ex: "1.234,56 kg (20,58 sc)" */
    public static function format(float $kg): string
    {
        $sacos = self::deKg($kg);
        return number_format($kg, 2, ',', '.') . ' kg (' . number_format($sacos, 2, ',', '.') . ' sc)';
    }

    public static function formatSacos(float $kg): string
    {
        return number_format(self::deKg($kg), 2, ',', '.') . ' sc';
    }
}
