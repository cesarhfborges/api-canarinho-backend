<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Endpoint;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\MockDataService;

class LoadTestSeeder extends Seeder
{
    public function run(): void
    {
        $usersCount = (int) config('load_test_users_count');
        $this->command->info("Creating {$usersCount} users for load testing...");

        $faker = Faker::create('pt_BR');
        $mockDataService = new MockDataService();
        $password = Hash::make('password123');

        $endpointsData = [
            [
                'name' => 'clientes',
                'schema' => [
                    ['name' => 'id', 'type' => 'Object.ID'],
                    ['name' => 'nome', 'type' => 'Faker.js', 'value' => '[person.fullName]'],
                    ['name' => 'email', 'type' => 'Faker.js', 'value' => '[internet.email]'],
                    ['name' => 'status', 'type' => 'Boolean', 'value' => true],
                ]
            ],
            [
                'name' => 'produtos',
                'schema' => [
                    ['name' => 'id', 'type' => 'Object.ID'],
                    ['name' => 'titulo', 'type' => 'Faker.js', 'value' => '[word.words]'],
                    ['name' => 'preco', 'type' => 'Number', 'value' => 0]
                ]
            ]
        ];

        for ($i = 1; $i <= $usersCount; $i++) {
            $email = "loadtest_{$i}@test.com";

            // Create User
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'username' => "loaduser_{$i}",
                    'name' => "Load User {$i}",
                    'password' => $password,
                    'is_admin' => false,
                ]
            );

            // Create Project
            $project = $user->projects()->firstOrCreate(
                ['slug' => "load-project-{$i}"],
                [
                    'name' => "Load Test Project {$i}"
                ]
            );

            // Create Project Token
            $project->tokens()->firstOrCreate(
                ['name' => 'Load Test Token'],
                ['token' => "load-token-{$i}"]
            );

            // Create Endpoints and Mock Data
            foreach ($endpointsData as $data) {
                $name = $data['name'];
                $schema = $data['schema'];

                $endpointConfig = [
                    ['url' => '/' . $name, 'method' => 'GET', 'enabled' => true, 'paginate' => true, 'per_page_default' => 10, 'response' => '$mockData'],
                    ['url' => '/' . $name . '/:id', 'method' => 'GET', 'enabled' => true, 'response' => '$mockData'],
                    ['url' => '/' . $name, 'method' => 'POST', 'enabled' => true, 'response' => '$mockData'],
                    ['url' => '/' . $name . '/:id', 'method' => 'PUT', 'enabled' => true, 'response' => '$mockData'],
                    ['url' => '/' . $name . '/:id', 'method' => 'DELETE', 'enabled' => true, 'response' => '$mockData']
                ];

                $endpoint = $project->endpoints()->firstOrCreate(
                    ['name' => $name],
                    [
                        'endpoints_config' => $endpointConfig,
                        'resource_schema' => $schema
                    ]
                );

                if ($endpoint->mockData()->count() === 0) {
                    $mockDataService->generateForEndpoint($endpoint, 10);
                }
            }
        }

        $this->command->info("Load test data generated successfully.");
    }
}
