<?php

test('example', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});
