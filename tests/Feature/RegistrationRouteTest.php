<?php

test('registration route resolves the controller', function () {   $response = $this->postJson('/api/registrations', []);
    $response->assertStatus(422);
});
