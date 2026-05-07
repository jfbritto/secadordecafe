<?php

use App\Support\NameNormalizer;

it('capitaliza nome simples', function () {
    expect(NameNormalizer::normalize('joão'))->toBe('João');
    expect(NameNormalizer::normalize('MARIA'))->toBe('Maria');
});

it('capitaliza cada palavra de nomes compostos', function () {
    expect(NameNormalizer::normalize('joão silva'))->toBe('João Silva');
    expect(NameNormalizer::normalize('MARIA APARECIDA'))->toBe('Maria Aparecida');
});

it('preserva conectivos PT-BR em minúsculo (da, de, do, dos, das, e)', function () {
    expect(NameNormalizer::normalize('joão da silva'))->toBe('João da Silva');
    expect(NameNormalizer::normalize('maria dos santos'))->toBe('Maria dos Santos');
    expect(NameNormalizer::normalize('pedro de souza e silva'))->toBe('Pedro de Souza e Silva');
});

it('capitaliza o conectivo se ele for o PRIMEIRO token', function () {
    // Edge case: alguém com nome iniciando em "Da", "De", etc.
    expect(NameNormalizer::normalize('da silva'))->toBe('Da Silva');
});

it('colapsa espaços extras e aplica trim', function () {
    expect(NameNormalizer::normalize('  joão   da    silva  '))->toBe('João da Silva');
});

it('lida com null e string vazia', function () {
    expect(NameNormalizer::normalize(null))->toBeNull();
    expect(NameNormalizer::normalize(''))->toBe('');
    expect(NameNormalizer::normalize('   '))->toBe('');
});

it('preserva acentos e ç', function () {
    expect(NameNormalizer::normalize('joão são josé'))->toBe('João São José');
    expect(NameNormalizer::normalize('CONCEIÇÃO'))->toBe('Conceição');
});
