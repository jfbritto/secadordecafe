<?php

namespace App\Support;

/**
 * Tradução de status técnicos (enums) pra rótulos em PT-BR amigáveis.
 * Fonte ÚNICA da verdade — qualquer view que precise mostrar status pro
 * usuário final deve usar estes helpers, nunca strtoupper($status).
 */
class StatusLabels
{
    /** Status do Farm/Subscription (mesmos valores, mesmas labels). */
    public const FARM = [
        'trial'    => 'Em teste',
        'active'   => 'Ativa',
        'partner'  => 'Parceira',
        'past_due' => 'Em atraso',
        'blocked'  => 'Bloqueada',
        'canceled' => 'Cancelada',
    ];

    /** Status da Secagem. */
    public const SECAGEM = [
        'rascunho'  => 'Rascunho',
        'concluida' => 'Concluída',
    ];

    /** Papéis (roles) dos usuários. */
    public const ROLE = [
        'admin'        => 'Administrador',
        'operador'     => 'Operador',
        'financeiro'   => 'Financeiro',
        'visualizador' => 'Visualizador',
        'root'         => 'Super-admin',
    ];

    /** Tipos de movimentação no extrato. */
    public const MOVEMENT_TIPO = [
        'entrada'  => 'Entrada',
        'saida'    => 'Saída',
        'ajuste'   => 'Ajuste',
        'secagem'  => 'Secagem',
        'colheita' => 'Colheita',
        'producao' => 'Produção',
        'comissao' => 'Comissão',
        'compra'   => 'Compra de café',
    ];

    /** Produto da movimentação. */
    public const PRODUTO = [
        'coco' => 'Café côco',
        'seco' => 'Café seco',
    ];

    public static function farm(?string $status): string
    {
        return self::FARM[$status] ?? ucfirst((string) $status);
    }

    public static function secagem(?string $status): string
    {
        return self::SECAGEM[$status] ?? ucfirst((string) $status);
    }

    public static function role(?string $name): string
    {
        return self::ROLE[$name] ?? ucfirst((string) $name);
    }

    public static function movementTipo(?string $tipo): string
    {
        return self::MOVEMENT_TIPO[$tipo] ?? ucfirst((string) $tipo);
    }

    public static function produto(?string $produto): string
    {
        return self::PRODUTO[$produto] ?? ucfirst((string) $produto);
    }
}
