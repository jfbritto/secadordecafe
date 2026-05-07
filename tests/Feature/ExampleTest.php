<?php

it('redirects root to dashboard which redirects guests to login', function () {
    $this->get('/')->assertRedirect(route('dashboard'));
    $this->get('/dashboard')->assertRedirect(route('login'));
});
