<?php

$content = file_get_contents(__DIR__ . '/../tests/Feature/WebRoutesTest.php');
$content = str_replace("\r\n", "\n", $content);

$tests = <<<PHP

    public function test_credit_debit_types_index_and_create()
    {
        \$response = \$this->actingAs(\$this->user)->get('/credit-debit-types');
        \$response->assertStatus(200);
        \$response->assertSee('CreditDebitTypes');
        \$response->assertSee('Delay');
        \$response->assertSee('Container Damage');

        \$createResponse = \$this->actingAs(\$this->user)->get('/credit-debit-types/new');
        \$createResponse->assertStatus(200);
        \$createResponse->assertSee('New CreditDebitType');
        \$createResponse->assertSee('CreditDebitType Detail');
        \$createResponse->assertSee('Description *');

        \$postResponse = \$this->actingAs(\$this->user)->post('/credit-debit-types', [
            'description' => 'Test Fee Discrepancy',
        ]);
        \$postResponse->assertRedirect();
        \$this->assertDatabaseHas('creditdebittype', ['description' => 'Test Fee Discrepancy']);
    }

    public function test_currencies_index_and_create()
    {
        \$response = \$this->actingAs(\$this->user)->get('/currencies');
        \$response->assertStatus(200);
        \$response->assertSee('Currencies');
        \$response->assertSee('United States Dollar');
        \$response->assertSee('USD');

        \$createResponse = \$this->actingAs(\$this->user)->get('/currencies/new');
        \$createResponse->assertStatus(200);
        \$createResponse->assertSee('New Currency');
        \$createResponse->assertSee('Currency Detail');
        \$createResponse->assertSee('HTML encode Symbol *');

        \$postResponse = \$this->actingAs(\$this->user)->post('/currencies', [
            'name' => 'Japanese Yen',
            'abbreviation' => 'JPY',
            'symbol' => '¥',
            'htmlEncodeSymbol' => '&yen;',
        ]);
        \$postResponse->assertRedirect();
        \$this->assertDatabaseHas('currency', ['abbreviation' => 'JPY']);
    }
}
PHP;

$content = preg_replace('/}\s*$/', $tests, $content);
file_put_contents(__DIR__ . '/../tests/Feature/WebRoutesTest.php', $content);
echo "Currency and CreditDebitType tests appended successfully!\n";
