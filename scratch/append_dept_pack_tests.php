<?php

$content = file_get_contents(__DIR__ . '/../tests/Feature/WebRoutesTest.php');
$content = str_replace("\r\n", "\n", $content);

$tests = <<<PHP

    public function test_departments_index_and_create()
    {
        \$response = \$this->actingAs(\$this->user)->get('/departments');
        \$response->assertStatus(200);
        \$response->assertSee('Departments');
        \$response->assertSee('Sales');
        \$response->assertSee('Marketing');

        \$createResponse = \$this->actingAs(\$this->user)->get('/departments/new');
        \$createResponse->assertStatus(200);
        \$createResponse->assertSee('New Department');
        \$createResponse->assertSee('Department Detail');
        \$createResponse->assertSee('Description *');

        \$postResponse = \$this->actingAs(\$this->user)->post('/departments', [
            'description' => 'Human Resources',
        ]);
        \$postResponse->assertRedirect();
        \$this->assertDatabaseHas('department', ['description' => 'Human Resources']);
    }

    public function test_pack_types_index_and_create()
    {
        \$response = \$this->actingAs(\$this->user)->get('/packTypes');
        \$response->assertStatus(200);
        \$response->assertSee('PackTypes');
        \$response->assertSee('Big Bags');
        \$response->assertSee('Pallets');

        \$createResponse = \$this->actingAs(\$this->user)->get('/packTypes/new');
        \$createResponse->assertStatus(200);
        \$createResponse->assertSee('New PackType');
        \$createResponse->assertSee('PackType Detail');
        \$createResponse->assertSee('Description *');

        \$postResponse = \$this->actingAs(\$this->user)->post('/packTypes', [
            'description' => 'Wooden Crates',
        ]);
        \$postResponse->assertRedirect();
        \$this->assertDatabaseHas('packtype', ['description' => 'Wooden Crates']);
    }
}
PHP;

$content = preg_replace('/}\s*$/', $tests, $content);
file_put_contents(__DIR__ . '/../tests/Feature/WebRoutesTest.php', $content);
echo "Department and PackType tests appended successfully!\n";
