<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Endpoint;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class DevSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('username', 'admin')->first();
        if (!$admin) {
            $this->command->error('Admin user not found. Run SystemSeeder first.');
            return;
        }

        // Create 1 Project
        $project = $admin->projects()->firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Projeto Demo (E-commerce)'
            ]
        );

        // Project Token for auth
        $project->tokens()->firstOrCreate(
            ['name' => 'Token de Desenvolvimento'],
            ['token' => 'dev-token-123']
        );

        $faker = Faker::create('pt_BR');

        $mockDataService = new \App\Services\MockDataService();

        // Define 5 endpoints schemas
        $endpointsData = [
            [
                'name' => 'usuarios',
                'schema' => [
                    ['name' => 'id', 'type' => 'Object.ID'],
                    ['name' => 'nome', 'type' => 'Faker.js', 'value' => '[person.fullName]'],
                    ['name' => 'email', 'type' => 'Faker.js', 'value' => '[internet.email]'],
                    ['name' => 'cidade', 'type' => 'Faker.js', 'value' => '[location.city]'],
                    ['name' => 'ativo', 'type' => 'Boolean']
                ]
            ],
            [
                'name' => 'produtos',
                'schema' => [
                    ['name' => 'id', 'type' => 'Object.ID'],
                    ['name' => 'titulo', 'type' => 'Faker.js', 'value' => '[word.words]'],
                    ['name' => 'preco', 'type' => 'Number'],
                    ['name' => 'descricao', 'type' => 'Faker.js', 'value' => '[word.sentence]']
                ]
            ],
            [
                'name' => 'pedidos',
                'schema' => [
                    ['name' => 'id', 'type' => 'Object.ID'],
                    ['name' => 'id_usuario', 'type' => 'Object.ID'], // Simulate relation
                    ['name' => 'total', 'type' => 'Number'],
                    ['name' => 'data', 'type' => 'Faker.js', 'value' => '[date.recent]']
                ]
            ],
            [
                'name' => 'categorias',
                'schema' => [
                    ['name' => 'id', 'type' => 'Object.ID'],
                    ['name' => 'nome', 'type' => 'Faker.js', 'value' => '[word.word]'],
                    ['name' => 'cor_hex', 'type' => 'Faker.js', 'value' => '[color.rgb]']
                ]
            ],
            [
                'name' => 'pagamentos',
                'schema' => [
                    ['name' => 'id', 'type' => 'Object.ID'],
                    ['name' => 'id_pedido', 'type' => 'Object.ID'],
                    ['name' => 'metodo', 'type' => 'Faker.js', 'value' => '[word.word]'],
                    ['name' => 'status', 'type' => 'String', 'value' => 'Aprovado']
                ]
            ]
        ];

        foreach ($endpointsData as $data) {
            $name = $data['name'];
            $schema = $data['schema'];

            $endpointConfig = [
                [
                    'url' => '/' . $name,
                    'method' => 'GET',
                    'enabled' => true,
                    'paginate' => true,
                    'per_page_default' => 10,
                    'response' => '$mockData'
                ],
                [
                    'url' => '/' . $name . '/:id',
                    'method' => 'GET',
                    'enabled' => true,
                    'response' => '$mockData'
                ],
                [
                    'url' => '/' . $name,
                    'method' => 'POST',
                    'enabled' => true,
                    'response' => '$mockData'
                ],
                [
                    'url' => '/' . $name . '/:id',
                    'method' => 'PUT',
                    'enabled' => true,
                    'response' => '$mockData'
                ],
                [
                    'url' => '/' . $name . '/:id',
                    'method' => 'DELETE',
                    'enabled' => true,
                    'response' => '$mockData'
                ]
            ];

            // Create Endpoint
            $endpoint = $project->endpoints()->firstOrCreate(
                ['name' => $name],
                [
                    'endpoints_config' => $endpointConfig,
                    'resource_schema' => $schema
                ]
            );

            // Generate 15 Mock Data records
            if ($endpoint->mockData()->count() === 0) {
                $mockDataService->generateForEndpoint($endpoint, 15);
            }
        }
    }
}
