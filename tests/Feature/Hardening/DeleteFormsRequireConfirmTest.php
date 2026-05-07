<?php

/**
 * Garante que toda view com @method('DELETE') também declara data-confirm
 * para que o usuário tenha um diálogo de confirmação antes de excluir.
 *
 * Esta proteção evita que um novo formulário de exclusão seja adicionado
 * no futuro sem confirmação acidentalmente.
 */

it('every blade view with method DELETE has a data-confirm attribute', function () {
    $viewsPath = base_path('resources/views');
    $offenders = [];

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') continue;
        $content = file_get_contents($file->getPathname());
        if (! str_contains($content, "@method('DELETE')")) continue;

        // Conta quantos forms abertos com data-confirm vs total de @method('DELETE')
        // Heurística: a presença de 'data-confirm=' no arquivo cobre o caso pra cada DELETE.
        // Para ser mais rigoroso, valida que o número de data-confirm >= número de @method('DELETE').
        $deleteCount = substr_count($content, "@method('DELETE')");
        $confirmCount = substr_count($content, 'data-confirm=');
        if ($confirmCount < $deleteCount) {
            $offenders[] = sprintf(
                '%s: %d form(s) DELETE / %d data-confirm',
                str_replace($viewsPath . '/', '', $file->getPathname()),
                $deleteCount,
                $confirmCount,
            );
        }
    }

    expect($offenders)->toBe(
        [],
        "Forms DELETE sem data-confirm encontrados:\n  " . implode("\n  ", $offenders)
    );
});
