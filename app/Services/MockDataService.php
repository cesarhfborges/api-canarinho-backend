<?php

namespace App\Services;

use Faker\Factory as Faker;
use Illuminate\Support\Str;
use App\Models\Endpoint;

class MockDataService
{
    public function generateForEndpoint(Endpoint $endpoint, int $count = 10): array
    {
        $schema = $endpoint->resource_schema;
        if (!$schema || !is_array($schema)) {
            return [];
        }

        $faker = Faker::create('pt_BR');
        $generatedData = [];

        for ($i = 0; $i < $count; $i++) {
            $record = $this->generateRecord($schema, $faker);

            // Save to DB
            $mockData = $endpoint->mockData()->create([
                'json_data' => $record
            ]);
            
            // In DB, mockData->id is our primary key.
            $record['id'] = $mockData->id;
            
            $generatedData[] = $record;
        }

        return $generatedData;
    }

    public function generateRecord(array $schema, $faker = null): array
    {
        if (!$faker) {
            $faker = Faker::create('pt_BR');
        }

        $record = [];

        foreach ($schema as $field) {
            $name = $field['name'] ?? null;
            if (!$name) continue;

            $record[$name] = $this->generateFieldValue($field, $faker);
        }

        return $record;
    }

    public function generateFieldValue(array $field, $faker)
    {
        $name = $field['name'] ?? null;
        $type = $field['type'] ?? 'String';

        if ($type === 'Object.ID') {
            if ($name !== 'id') {
                return (string) Str::uuid();
            }
            return null; // id is managed by DB
        } elseif ($type === 'Faker.js') {
            $fakerValue = $field['value'] ?? '[word.word]';
            return $this->mapFakerValue($faker, $fakerValue);
        } else {
            if (isset($field['value']) && $field['value'] !== '') {
                return $field['value'];
            } else {
                if ($type === 'String') {
                    return Str::random(10);
                } elseif ($type === 'Number') {
                    return $faker->randomNumber();
                } elseif ($type === 'Boolean') {
                    return $faker->boolean();
                } elseif ($type === 'Date') {
                    return now()->toDateTimeString();
                } else {
                    return null;
                }
            }
        }
    }

    public function syncMockDataWithSchema(Endpoint $endpoint)
    {
        $schema = $endpoint->resource_schema;
        if (!$schema || !is_array($schema)) {
            return;
        }

        $faker = Faker::create('pt_BR');
        $mockRecords = $endpoint->mockData()->get();

        foreach ($mockRecords as $mock) {
            $oldData = $mock->json_data;
            if (!is_array($oldData)) {
                $oldData = [];
            }

            $newData = [];

            foreach ($schema as $field) {
                $name = $field['name'] ?? null;
                if (!$name) continue;

                if ($name === 'id') {
                    $newData['id'] = $mock->id;
                    continue;
                }

                // If the field existed in the old data, we keep it to preserve existing records
                // unless we want to do strict type checking, but keeping old data is safer
                if (array_key_exists($name, $oldData)) {
                    $newData[$name] = $oldData[$name];
                } else {
                    // Field was added
                    $newData[$name] = $this->generateFieldValue($field, $faker);
                }
            }

            $mock->update(['json_data' => $newData]);
        }
    }

    private function mapFakerValue($faker, string $value)
    {
        // Remove brackets
        $cleanValue = trim($value, '[]');
        
        switch ($cleanValue) {
            // Pessoa
            case 'person.firstName': return $faker->firstName();
            case 'person.lastName': return $faker->lastName();
            case 'person.fullName': return $faker->name();
            case 'person.middleName': return $faker->lastName();
            case 'person.prefix': return $faker->title();
            case 'person.suffix': return $faker->suffix();
            case 'person.gender': return $faker->randomElement(['Masculino', 'Feminino', 'Outro']);

            // Internet
            case 'internet.email': return $faker->safeEmail();
            case 'internet.username': return $faker->userName();
            case 'internet.password': return $faker->password();
            case 'internet.url': return $faker->url();
            case 'internet.domainName': return $faker->domainName();
            case 'internet.ipv4': return $faker->ipv4();
            case 'internet.ipv6': return $faker->ipv6();

            // Endereço
            case 'location.city': return $faker->city();
            case 'location.state': return $faker->state();
            case 'location.country': return $faker->country();
            case 'location.zipCode': return $faker->postcode();
            case 'location.latitude': return $faker->latitude();
            case 'location.longitude': return $faker->longitude();

            // Empresa
            case 'company.name': return $faker->company();
            case 'company.department': return $faker->word();
            case 'company.buzzPhrase': return $faker->catchPhrase();

            // Financeiro
            case 'finance.accountNumber': return $faker->bankAccountNumber();
            case 'finance.amount': return $faker->randomFloat(2, 10, 1000);
            case 'finance.currencyCode': return $faker->currencyCode();

            // Texto
            case 'word.word': return $faker->word();
            case 'word.words': return implode(' ', $faker->words(3));
            case 'word.sentence': return $faker->sentence();
            case 'word.paragraph': return $faker->paragraph();

            // Datas
            case 'date.past': return $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d');
            case 'date.future': return $faker->dateTimeBetween('now', '+1 year')->format('Y-m-d');
            case 'date.recent': return $faker->dateTimeThisMonth()->format('Y-m-d');
            case 'date.birthdate': return $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d');

            // Identificadores
            case 'string.uuid': return $faker->uuid();
            case 'database.mongodbObjectId': return $faker->regexify('[a-f0-9]{24}');

            // Cores
            case 'color.human': return $faker->colorName();
            case 'color.rgb': return $faker->hexColor(); // RGB is complex, fallback to hex or rgbCssColor
            
            // Números
            case 'number.int': return $faker->randomNumber();
            case 'number.float': return $faker->randomFloat(2, 0, 100);
            
            // Booleano
            case 'datatype.boolean': return $faker->boolean();

            // Mídia
            case 'image.avatar': return $faker->imageUrl(100, 100, 'cats');
            case 'image.url': return $faker->imageUrl(640, 480);

            // Veículos
            case 'vehicle.vehicle': return $faker->word();
            case 'vehicle.model': return $faker->word();
            case 'vehicle.manufacturer': return $faker->company();

            default:
                // Fallback trying to call the method directly if it exists
                $parts = explode('.', $cleanValue);
                $methodName = end($parts);
                try {
                    return $faker->$methodName();
                } catch (\Exception $e) {
                    return $faker->word();
                }
        }
    }
}
