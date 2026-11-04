<?php

$content = file_get_contents(__DIR__ . '/../tests/Feature/WebRoutesTest.php');
$content = str_replace("\r\n", "\n", $content);

$tests = <<<PHP

    public function test_countries_index_renders()
    {
        \$response = \$this->actingAs(\$this->user)->get('/countries');
        \$response->assertStatus(200);
        \$response->assertSee('Countries');
        \$response->assertSee('Albania');
        \$response->assertSee('Algeria');
    }

    public function test_country_create_and_store()
    {
        \$response = \$this->actingAs(\$this->user)->get('/countries/new');
        \$response->assertStatus(200);
        \$response->assertSee('New Country');
        \$response->assertSee('Country Detail');
        \$response->assertSee('Basic Info');

        \$postResponse = \$this->actingAs(\$this->user)->post('/countries', [
            'name' => 'Testland',
            'alpha2' => 'TL',
            'alpha3' => 'TLS',
            'region' => 'Europe',
            'vat' => 20,
        ]);
        \$postResponse->assertRedirect();
        \$this->assertDatabaseHas('countries', ['name' => 'Testland']);
    }

    public function test_company_types_index_and_create()
    {
        \$response = \$this->actingAs(\$this->user)->get('/company-types');
        \$response->assertStatus(200);
        \$response->assertSee('Company Types');
        \$response->assertSee('Buyer');
        \$response->assertSee('Seller');

        \$createResponse = \$this->actingAs(\$this->user)->get('/company-types/new');
        \$createResponse->assertStatus(200);
        \$createResponse->assertSee('New CompanyType');
        \$createResponse->assertSee('Company Type Detail');
        \$createResponse->assertSee('Basic Info');

        \$postResponse = \$this->actingAs(\$this->user)->post('/company-types', [
            'name' => 'Custom Broker',
        ]);
        \$postResponse->assertRedirect();
        \$this->assertDatabaseHas('types', ['description' => 'Custom Broker']);
    }
}
PHP;

$content = preg_replace('/}\s*$/', $tests, $content);
file_put_contents(__DIR__ . '/../tests/Feature/WebRoutesTest.php', $content);
echo "Tests appended successfully!\n";
