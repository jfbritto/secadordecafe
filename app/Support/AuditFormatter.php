<?php

namespace App\Support;

use App\Models\Area;
use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Farm;
use App\Models\Invitation;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\SecagemItem;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * Transforma um Activity (Spatie Activitylog) numa estrutura humana e
 * apresentavel: descricao em português + diff antes/depois pra edicoes.
 */
class AuditFormatter
{
    /** Configuração por subject_type. */
    public const SUBJECTS = [
        Farm::class => [
            'label' => 'Fazenda', 'name_field' => 'nome', 'name_prefix' => '',
            'icon' => 'M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01',
        ],
        Customer::class => [
            'label' => 'Cliente', 'name_field' => 'nome', 'name_prefix' => '',
            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        ],
        Dryer::class => [
            'label' => 'Secador', 'name_field' => 'nome', 'name_prefix' => '',
            'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
        ],
        Area::class => [
            'label' => 'Área', 'name_field' => 'nome', 'name_prefix' => '',
            'icon' => 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
        ],
        Secagem::class => [
            'label' => 'Secagem', 'name_field' => 'numero', 'name_prefix' => '#',
            'icon' => 'M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z',
        ],
        Expense::class => [
            'label' => 'Despesa', 'name_field' => 'descricao', 'name_prefix' => '',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        ExpenseCategory::class => [
            'label' => 'Categoria de despesa', 'name_field' => 'nome', 'name_prefix' => '',
            'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
        ],
        User::class => [
            'label' => 'Usuário', 'name_field' => 'name', 'name_prefix' => '',
            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        ],
        Movement::class => [
            'label' => 'Movimentação', 'name_field' => 'observacao', 'name_prefix' => '',
            'icon' => 'M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4',
        ],
        SecagemItem::class => [
            'label' => 'Item de secagem', 'name_field' => 'id', 'name_prefix' => '#',
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        ],
        Subscription::class => [
            'label' => 'Assinatura', 'name_field' => 'status', 'name_prefix' => '',
            'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        ],
        Invitation::class => [
            'label' => 'Convite', 'name_field' => 'email', 'name_prefix' => '',
            'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        ],
    ];

    /** Verbo amigável por evento. */
    public const ACTIONS = [
        'created' => 'cadastrou',
        'updated' => 'editou',
        'deleted' => 'excluiu',
    ];

    /** Tradução de nomes de campo para PT-BR. */
    public const FIELDS = [
        'nome' => 'Nome',
        'name' => 'Nome',
        'farm_name' => 'Nome da fazenda',
        'email' => 'E-mail',
        'telefone' => 'Telefone',
        'cpf_cnpj' => 'CPF/CNPJ',
        'cidade' => 'Cidade',
        'estado' => 'UF',
        'status' => 'Status',
        'numero' => 'Número',
        'data' => 'Data',
        'secador' => 'Secador',
        'dryer_id' => 'Secador',
        'area_id' => 'Área',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'descricao' => 'Descrição',
        'categoria' => 'Categoria',
        'expense_category_id' => 'Categoria',
        'valor_total' => 'Valor total',
        'valor_unitario' => 'Valor unitário',
        'unidade' => 'Unidade',
        'quantidade' => 'Quantidade',
        'capacidade_kg' => 'Capacidade (kg)',
        'modelo' => 'Modelo',
        'ativo' => 'Ativo',
        'saldo_cafe_kg' => 'Saldo de café (kg)',
        'observacoes' => 'Observações',
        'observacao' => 'Observação',
        'concluida_at' => 'Concluída em',
        // user
        'is_root' => 'Super-admin',
        'role' => 'Permissão',
        'email_verified_at' => 'E-mail verificado em',
        // movement / secagemItem
        'tipo' => 'Tipo',
        'quantidade_kg' => 'Quantidade (kg)',
        'customer_id' => 'Cliente',
        'secagem_id' => 'Secagem',
        'source_type' => 'Origem',
        'source_id' => 'ID da origem',
        'occurred_at' => 'Ocorrida em',
        'quantidade_recebida_kg' => 'Recebida (kg)',
        'quantidade_seca_kg' => 'Seca (kg)',
        'comissao_percentual' => 'Comissão (%)',
        'comissao_kg' => 'Comissão (kg)',
        'saldo_liquido_kg' => 'Saldo líquido (kg)',
        // subscription
        'trial_ends_at' => 'Fim do trial',
        'current_period_end' => 'Fim do período atual',
        'asaas_customer_id' => 'ID Asaas (cliente)',
        'asaas_subscription_id' => 'ID Asaas (assinatura)',
        // invitation
        'expires_at' => 'Expira em',
        'accepted_at' => 'Aceito em',
        'invited_by' => 'Convidado por',
    ];

    /**
     * Estrutura amigável da atividade.
     */
    public static function describe(Activity $activity): array
    {
        $type = $activity->subject_type;
        $cfg = self::SUBJECTS[$type] ?? null;

        $event = $activity->event ?? self::extractEventFromDescription($activity->description);
        $actionVerb = self::ACTIONS[$event] ?? $event;

        return [
            'when' => $activity->created_at instanceof Carbon ? $activity->created_at : Carbon::parse($activity->created_at),
            'actor' => $activity->causer?->name ?? 'sistema',
            'action' => $actionVerb,
            'event' => $event,
            'entity_label' => $cfg['label'] ?? class_basename((string) $type),
            'icon' => $cfg['icon'] ?? null,
            'subject_name' => self::resolveSubjectName($activity, $cfg),
            'subject_id' => $activity->subject_id,
            'has_diff' => $event === 'updated' && self::hasUsefulDiff($activity),
        ];
    }

    /**
     * Tabela "antes / depois" só com campos que mudaram.
     * Cada item: ['field' => 'Nome', 'old' => '...', 'new' => '...']
     */
    public static function diff(Activity $activity): array
    {
        $props = $activity->properties ?? collect();
        $old = (array) ($props['old'] ?? []);
        $new = (array) ($props['attributes'] ?? []);

        $rows = [];
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        foreach ($keys as $key) {
            if (in_array($key, ['farm_id', 'updated_at', 'created_at'], true)) continue;
            $oldV = $old[$key] ?? null;
            $newV = $new[$key] ?? null;
            if ($oldV === $newV) continue;

            $rows[] = [
                'field' => self::fieldLabel($key),
                'old' => self::formatValue($oldV, $key, $activity->subject_type),
                'new' => self::formatValue($newV, $key, $activity->subject_type),
            ];
        }
        return $rows;
    }

    public static function fieldLabel(string $field): string
    {
        return self::FIELDS[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    public static function formatValue(mixed $value, ?string $field = null, ?string $subjectType = null): string
    {
        if (is_null($value) || $value === '') return '—';
        if (is_bool($value)) return $value ? 'Sim' : 'Não';

        // Resolver IDs em nomes legíveis
        if ($field === 'dryer_id' && is_numeric($value)) {
            $name = Dryer::query()->withoutGlobalScopes()->find($value)?->nome;
            return $name ?? "#{$value}";
        }
        if ($field === 'area_id' && is_numeric($value)) {
            $name = Area::query()->withoutGlobalScopes()->find($value)?->nome;
            return $name ?? "#{$value}";
        }
        if ($field === 'expense_category_id' && is_numeric($value)) {
            $name = ExpenseCategory::query()->withoutGlobalScopes()->find($value)?->nome;
            return $name ?? "#{$value}";
        }
        if ($field === 'customer_id' && is_numeric($value)) {
            $name = Customer::query()->withoutGlobalScopes()->find($value)?->nome;
            return $name ?? "#{$value}";
        }
        if ($field === 'secagem_id' && is_numeric($value)) {
            $sec = Secagem::query()->withoutGlobalScopes()->find($value);
            return $sec ? "Secagem #{$sec->numero}" : "#{$value}";
        }
        if ($field === 'invited_by' && is_numeric($value)) {
            $name = User::query()->find($value)?->name;
            return $name ?? "#{$value}";
        }
        if ($field === 'source_type' && is_string($value) && class_exists($value)) {
            return self::SUBJECTS[$value]['label'] ?? class_basename($value);
        }

        if ($field === 'data' && is_string($value)) {
            try { return Carbon::parse($value)->format('d/m/Y'); } catch (\Throwable $e) {}
        }
        if (in_array($field, ['concluida_at', 'occurred_at', 'email_verified_at', 'expires_at', 'accepted_at', 'trial_ends_at', 'current_period_end'], true) && is_string($value)) {
            try { return Carbon::parse($value)->format('d/m/Y H:i'); } catch (\Throwable $e) {}
        }
        if (in_array($field, ['valor_total', 'valor_unitario'], true) && is_numeric($value)) {
            return 'R$ ' . number_format((float) $value, 2, ',', '.');
        }
        if (in_array($field, ['saldo_cafe_kg', 'capacidade_kg', 'quantidade', 'quantidade_kg', 'quantidade_recebida_kg', 'quantidade_seca_kg', 'comissao_kg', 'saldo_liquido_kg'], true) && is_numeric($value)) {
            return number_format((float) $value, 3, ',', '.') . ' kg';
        }

        return (string) $value;
    }

    private static function resolveSubjectName(Activity $activity, ?array $cfg): string
    {
        $id = $activity->subject_id;
        $type = $activity->subject_type;

        if (! $cfg || ! $type) return "#{$id}";

        $field = $cfg['name_field'] ?? 'nome';
        $prefix = $cfg['name_prefix'] ?? '';

        // Tenta carregar o subject vivo primeiro (without scope para ver de outras farms se root)
        if (class_exists($type)) {
            $subject = $type::query()->withoutGlobalScopes()->find($id);
            if ($subject) {
                $val = $subject->{$field} ?? null;
                if ($val !== null) return $prefix . $val;
            }
        }

        // Fallback: pega do properties (útil pra deleted)
        $props = $activity->properties ?? collect();
        $val = $props['attributes'][$field] ?? $props['old'][$field] ?? null;
        if ($val !== null) return $prefix . $val;

        return "#{$id}";
    }

    private static function hasUsefulDiff(Activity $activity): bool
    {
        $props = $activity->properties ?? collect();
        return ! empty($props['old']) && ! empty($props['attributes']);
    }

    private static function extractEventFromDescription(?string $description): string
    {
        if (! $description) return 'updated';
        if (str_ends_with($description, 'created')) return 'created';
        if (str_ends_with($description, 'updated')) return 'updated';
        if (str_ends_with($description, 'deleted')) return 'deleted';
        return 'updated';
    }
}
