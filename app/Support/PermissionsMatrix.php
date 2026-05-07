<?php

namespace App\Support;

/**
 * Matriz centralizada de permissões por papel (role).
 *
 * Esta é a fonte ÚNICA de verdade sobre o que cada papel pode fazer em
 * cada módulo. As Policies (CustomerPolicy, SecagemPolicy, etc.) consultam
 * essa matriz indiretamente através das mesmas regras (hasRole/hasAnyRole).
 *
 * Há um teste que percorre cada combinação role × módulo × ação e
 * confronta com a Policy correspondente para garantir que a matriz reflete
 * o comportamento real do sistema.
 */
class PermissionsMatrix
{
    /** Módulos do sistema (chave => label) */
    public const MODULES = [
        'clientes' => 'Clientes',
        'secagens' => 'Secagens',
        'secadores' => 'Secadores',
        'despesas' => 'Despesas',
        'categorias' => 'Categorias de despesa',
        'usuarios' => 'Usuários da fazenda',
        'fazenda' => 'Configurações da fazenda',
        'auditoria' => 'Auditoria',
        'assinatura' => 'Assinatura',
    ];

    /** Ações possíveis em cada módulo (chave => label) */
    public const ACTIONS = [
        'view'     => 'Ver',
        'create'   => 'Criar',
        'edit'     => 'Editar',
        'delete'   => 'Excluir',
        'conclude' => 'Concluir',
    ];

    /** Papéis disponíveis (chave => [label, descrição curta]) */
    public const ROLES = [
        'admin' => [
            'label' => 'Administrador',
            'desc' => 'Acesso total. Cria, edita, exclui qualquer coisa e gerencia usuários.',
        ],
        'operador' => [
            'label' => 'Operador',
            'desc' => 'Cuida do dia a dia: cadastra clientes, secadores, registra secagens.',
        ],
        'financeiro' => [
            'label' => 'Financeiro',
            'desc' => 'Cuida das despesas e categorias. Vê o resto sem editar.',
        ],
        'visualizador' => [
            'label' => 'Visualizador',
            'desc' => 'Só consulta. Não cria, não edita, não exclui nada.',
        ],
    ];

    /**
     * Permissões por papel e módulo.
     * Estrutura: [role => [module => [action, action, ...]]]
     * 'admin' usa o caractere '*' como atalho para "tudo em tudo".
     */
    public const ROLE_PERMISSIONS = [
        'admin' => '*',

        'operador' => [
            'clientes'  => ['view', 'create', 'edit'],
            'secagens'  => ['view', 'create', 'edit', 'conclude'],
            'secadores' => ['view', 'create', 'edit'],
        ],

        'financeiro' => [
            'clientes'   => ['view'],
            'secagens'   => ['view'],
            'secadores'  => ['view'],
            'despesas'   => ['view', 'create', 'edit'],
            'categorias' => ['view', 'create', 'edit'],
        ],

        'visualizador' => [
            'clientes'   => ['view'],
            'secagens'   => ['view'],
            'secadores'  => ['view'],
            'despesas'   => ['view'],
            'categorias' => ['view'],
        ],
    ];

    /** Verifica se a role pode realizar a ação no módulo. */
    public static function can(string $role, string $module, string $action): bool
    {
        $perms = self::ROLE_PERMISSIONS[$role] ?? [];
        if ($perms === '*') {
            return true; // admin
        }
        return in_array($action, $perms[$module] ?? [], true);
    }

    /** Retorna todas as ações que a role pode fazer no módulo. */
    public static function actionsFor(string $role, string $module): array
    {
        $perms = self::ROLE_PERMISSIONS[$role] ?? [];
        if ($perms === '*') {
            return array_keys(self::ACTIONS);
        }
        return $perms[$module] ?? [];
    }

    /** Retorna a matriz completa (estrutura pronta pra renderizar tabela). */
    public static function matrixFor(string $role): array
    {
        $matrix = [];
        foreach (self::MODULES as $module => $label) {
            $matrix[$module] = [];
            foreach (self::ACTIONS as $action => $aLabel) {
                $matrix[$module][$action] = self::can($role, $module, $action);
            }
        }
        return $matrix;
    }
}
