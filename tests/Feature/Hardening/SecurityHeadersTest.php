<?php

it('sets security headers on web responses', function () {
    $r = $this->get('/login')->assertOk();
    $r->assertHeader('X-Content-Type-Options', 'nosniff');
    $r->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $r->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});
