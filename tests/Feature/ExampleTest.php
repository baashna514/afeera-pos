<?php

test('login screen can be rendered for guest', function () {
    auth()->logout();
    $response = $this->get('/login');
    $response->assertOk();
});
