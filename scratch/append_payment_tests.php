<?php

$content = file_get_contents(__DIR__ . '/../tests/Feature/WebRoutesTest.php');
$content = str_replace("\r\n", "\n", $content);

$tests = <<<PHP

    public function test_payment_types_index_and_create()
    {
        \$response = \$this->actingAs(\$this->user)->get('/paymentTypes');
        \$response->assertStatus(200);
        \$response->assertSee('PaymentTypes');
        \$response->assertSee('Sight LC');
        \$response->assertSee('Deferred LC');

        \$createResponse = \$this->actingAs(\$this->user)->get('/paymentTypes/new');
        \$createResponse->assertStatus(200);
        \$createResponse->assertSee('New PaymentType');
        \$createResponse->assertSee('PaymentType Detail');
        \$createResponse->assertSee('Description *');

        \$postResponse = \$this->actingAs(\$this->user)->post('/paymentTypes', [
            'description' => 'Escrow Payment',
        ]);
        \$postResponse->assertRedirect();
        \$this->assertDatabaseHas('paymenttype', ['description' => 'Escrow Payment']);
    }

    public function test_payment_terms_index_and_create()
    {
        \$response = \$this->actingAs(\$this->user)->get('/paymentTermsTypes');
        \$response->assertStatus(200);
        \$response->assertSee('PaymentTermsTypes');
        \$response->assertSee('Days');
        \$response->assertSee('Months');

        \$createResponse = \$this->actingAs(\$this->user)->get('/paymentTermsTypes/new');
        \$createResponse->assertStatus(200);
        \$createResponse->assertSee('New PaymentTermsType');
        \$createResponse->assertSee('PaymentTermsType Detail');
        \$createResponse->assertSee('Description *');

        \$postResponse = \$this->actingAs(\$this->user)->post('/paymentTermsTypes', [
            'description' => 'Fortnightly',
        ]);
        \$postResponse->assertRedirect();
        \$this->assertDatabaseHas('paymenttermstype', ['description' => 'Fortnightly']);
    }
}
PHP;

$content = preg_replace('/}\s*$/', $tests, $content);
file_put_contents(__DIR__ . '/../tests/Feature/WebRoutesTest.php', $content);
echo "PaymentTypes and PaymentTermsTypes tests appended successfully!\n";
