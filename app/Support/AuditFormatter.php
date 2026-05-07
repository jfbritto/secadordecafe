<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\Dryer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Farm;
use App\Models\Secagem;
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
            'icon' => 'M9 17v-2a4 4 0 014-4h6m-4-4l4 4-4 4M3 7v10a2 2 0 002 2h6a2 2 0 002-2v-2',
        ],
        Secagem::class => [
            'label' => 'Secagem', 'name_field' => 'numero', 'name_prefix' => '#',
            'icon' => 'M5 8h14M5 12h14M5 16h14M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z',
        ],
        Expense::class => [
            'label' => 'Despesa', 'name_field' => 'descricao', 'name_prefix' => '',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        ExpenseCategory::class => [
            'label' => 'Categoria de despesa', 'name_field' => 'nome', 'name_prefix' => '',
            'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
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
        if ($field === 'expense_category_id' && is_numeric($value)) {
            $name = ExpenseCategory::query()->withoutGlobalScopes()->find($value)?->nome;
            return $name ?? "#{$value}";
        }

        if ($field === 'data' && is_string($value)) {
            try { return Carbon::parse($value)->format('d/m/Y'); } catch (\Throwable $e) {}
        }
        if ($field === 'concluida_at' && is_string($value)) {
            try { return Carbon::parse($value)->format('d/m/Y H:i'); } catch (\Throwable $e) {}
        }
        if (in_array($field, ['valor_total', 'valor_unitario'], true) && is_numeric($value)) {
            return 'R$ ' . number_format((float) $value, 2, ',', '.');
        }
        if (in_array($field, ['saldo_cafe_kg', 'capacidade_kg', 'quantidade'], true) && is_numeric($value)) {
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
