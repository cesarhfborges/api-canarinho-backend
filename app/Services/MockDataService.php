<?php

namespace App\Services;

use App\Models\Endpoint;
use Carbon\Carbon;
use Exception;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Support\Str;

class MockDataService
{

    private array $fabricantes = [
        "Toyota",
        "Volkswagen",
        "Ford",
        "Fiat",
        "Chevrolet",
        "Honda",
        "Hyundai",
        "Nissan",
        "Renault",
        "Peugeot",
        "Citroën",
        "BMW",
        "Mercedes-Benz",
        "Audi",
        "Volvo",
        "Jeep",
        "BYD",
        "GWM",
        "Caoa Chery",
        "Kia"
    ];

    private array $modelos = [
        "Onix",
        "Gol",
        "Corolla",
        "Civic",
        "HB20",
        "Argo",
        "Cronos",
        "Compass",
        "Renegade",
        "Kwid",
        "Sandero",
        "T-Cross",
        "Nivus",
        "Polo",
        "Tracker",
        "Creta",
        "Dolphin",
        "Haval H6",
        "Tiggo 5X",
        "Hilux",
        "S10",
        "Ranger",
        "Toro",
        "Strada"
    ];

    private array $estados = [
        ['sigla' => 'AC', 'nome' => 'Acre'],
        ['sigla' => 'AL', 'nome' => 'Alagoas'],
        ['sigla' => 'AP', 'nome' => 'Amapá'],
        ['sigla' => 'AM', 'nome' => 'Amazonas'],
        ['sigla' => 'BA', 'nome' => 'Bahia'],
        ['sigla' => 'CE', 'nome' => 'Ceará'],
        ['sigla' => 'DF', 'nome' => 'Distrito Federal'],
        ['sigla' => 'ES', 'nome' => 'Espirito Santo'],
        ['sigla' => 'GO', 'nome' => 'Goiás'],
        ['sigla' => 'MA', 'nome' => 'Maranhão'],
        ['sigla' => 'MS', 'nome' => 'Mato Grosso do Sul'],
        ['sigla' => 'MT', 'nome' => 'Mato Grosso'],
        ['sigla' => 'MG', 'nome' => 'Minas Gerais'],
        ['sigla' => 'PA', 'nome' => 'Pará'],
        ['sigla' => 'PB', 'nome' => 'Paraíba'],
        ['sigla' => 'PR', 'nome' => 'Paraná'],
        ['sigla' => 'PE', 'nome' => 'Pernambuco'],
        ['sigla' => 'PI', 'nome' => 'Piauí'],
        ['sigla' => 'RJ', 'nome' => 'Rio de Janeiro'],
        ['sigla' => 'RN', 'nome' => 'Rio Grande do Norte'],
        ['sigla' => 'RS', 'nome' => 'Rio Grande do Sul'],
        ['sigla' => 'RO', 'nome' => 'Rondônia'],
        ['sigla' => 'RR', 'nome' => 'Roraima'],
        ['sigla' => 'SC', 'nome' => 'Santa Catarina'],
        ['sigla' => 'SP', 'nome' => 'São Paulo'],
        ['sigla' => 'SE', 'nome' => 'Sergipe'],
        ['sigla' => 'TO', 'nome' => 'Tocantins']
    ];

    private array $departamentos = [
        "Recursos Humanos (RH)",
        "Departamento Pessoal (DP)",
        "Financeiro",
        "Contabilidade",
        "Comercial / Vendas",
        "Marketing",
        "Tecnologia da Informação (TI)",
        "Desenvolvimento de Produto / Engenharia",
        "Atendimento ao Cliente / Customer Success",
        "Operações / Logística",
        "Jurídico",
        "Compras / Suprimentos",
        "Qualidade / Compliance",
        "Administrativo",
        "Comunicação Interna / Endomarketing"
    ];

    private array $bancos = array(
        array('code' => '001', 'name' => 'Banco do Brasil'),
        array('code' => '003', 'name' => 'Banco da Amazônia'),
        array('code' => '004', 'name' => 'Banco do Nordeste'),
        array('code' => '021', 'name' => 'Banestes'),
        array('code' => '025', 'name' => 'Banco Alfa'),
        array('code' => '027', 'name' => 'Besc'),
        array('code' => '029', 'name' => 'Banerj'),
        array('code' => '031', 'name' => 'Banco Beg'),
        array('code' => '033', 'name' => 'Banco Santander Banespa'),
        array('code' => '036', 'name' => 'Banco Bem'),
        array('code' => '037', 'name' => 'Banpará'),
        array('code' => '038', 'name' => 'Banestado'),
        array('code' => '039', 'name' => 'BEP'),
        array('code' => '040', 'name' => 'Banco Cargill'),
        array('code' => '041', 'name' => 'Banrisul'),
        array('code' => '044', 'name' => 'BVA'),
        array('code' => '045', 'name' => 'Banco Opportunity'),
        array('code' => '047', 'name' => 'Banese'),
        array('code' => '062', 'name' => 'Hipercard'),
        array('code' => '063', 'name' => 'Ibibank'),
        array('code' => '065', 'name' => 'Lemon Bank'),
        array('code' => '066', 'name' => 'Banco Morgan Stanley Dean Witter'),
        array('code' => '069', 'name' => 'BPN Brasil'),
        array('code' => '070', 'name' => 'Banco de Brasília – BRB'),
        array('code' => '072', 'name' => 'Banco Rural'),
        array('code' => '073', 'name' => 'Banco Popular'),
        array('code' => '074', 'name' => 'Banco J. Safra'),
        array('code' => '075', 'name' => 'Banco CR2'),
        array('code' => '076', 'name' => 'Banco KDB'),
        array('code' => '096', 'name' => 'Banco BMF'),
        array('code' => '104', 'name' => 'Caixa Econômica Federal'),
        array('code' => '107', 'name' => 'Banco BBM'),
        array('code' => '116', 'name' => 'Banco Único'),
        array('code' => '151', 'name' => 'Nossa Caixa'),
        array('code' => '175', 'name' => 'Banco Finasa'),
        array('code' => '184', 'name' => 'Banco Itaú BBA'),
        array('code' => '204', 'name' => 'American Express Bank'),
        array('code' => '208', 'name' => 'Banco Pactual'),
        array('code' => '212', 'name' => 'Banco Matone'),
        array('code' => '213', 'name' => 'Banco Arbi'),
        array('code' => '214', 'name' => 'Banco Dibens'),
        array('code' => '217', 'name' => 'Banco Joh Deere'),
        array('code' => '218', 'name' => 'Banco Bonsucesso'),
        array('code' => '222', 'name' => 'Banco Calyon Brasil'),
        array('code' => '224', 'name' => 'Banco Fibra'),
        array('code' => '225', 'name' => 'Banco Brascan'),
        array('code' => '229', 'name' => 'Banco Cruzeiro'),
        array('code' => '230', 'name' => 'Unicard'),
        array('code' => '233', 'name' => 'Banco GE Capital'),
        array('code' => '237', 'name' => 'Bradesco'),
        array('code' => '241', 'name' => 'Banco Clássico'),
        array('code' => '243', 'name' => 'Banco Stock Máxima'),
        array('code' => '246', 'name' => 'Banco ABC Brasil'),
        array('code' => '248', 'name' => 'Banco Boavista Interatlântico'),
        array('code' => '249', 'name' => 'Investcred Unibanco'),
        array('code' => '250', 'name' => 'Banco Schahin'),
        array('code' => '252', 'name' => 'Fininvest'),
        array('code' => '254', 'name' => 'Paraná Banco'),
        array('code' => '263', 'name' => 'Banco Cacique'),
        array('code' => '265', 'name' => 'Banco Fator'),
        array('code' => '266', 'name' => 'Banco Cédula'),
        array('code' => '300', 'name' => 'Banco de la Nación Argentina'),
        array('code' => '318', 'name' => 'Banco BMG'),
        array('code' => '320', 'name' => 'Banco Industrial e Comercial'),
        array('code' => '356', 'name' => 'ABN Amro Real'),
        array('code' => '341', 'name' => 'Itau'),
        array('code' => '347', 'name' => 'Sudameris'),
        array('code' => '351', 'name' => 'Banco Santander'),
        array('code' => '353', 'name' => 'Banco Santander Brasil'),
        array('code' => '366', 'name' => 'Banco Societe Generale Brasil'),
        array('code' => '370', 'name' => 'Banco WestLB'),
        array('code' => '376', 'name' => 'JP Morgan'),
        array('code' => '389', 'name' => 'Banco Mercantil do Brasil'),
        array('code' => '394', 'name' => 'Banco Mercantil de Crédito'),
        array('code' => '399', 'name' => 'HSBC'),
        array('code' => '409', 'name' => 'Unibanco'),
        array('code' => '412', 'name' => 'Banco Capital'),
        array('code' => '422', 'name' => 'Banco Safra'),
        array('code' => '453', 'name' => 'Banco Rural'),
        array('code' => '456', 'name' => 'Banco Tokyo Mitsubishi UFJ'),
        array('code' => '464', 'name' => 'Banco Sumitomo Mitsui Brasileiro'),
        array('code' => '477', 'name' => 'Citibank'),
        array('code' => '479', 'name' => 'Itaubank (antigo Bank Boston)'),
        array('code' => '487', 'name' => 'Deutsche Bank'),
        array('code' => '488', 'name' => 'Banco Morgan Guaranty'),
        array('code' => '492', 'name' => 'Banco NMB Postbank'),
        array('code' => '494', 'name' => 'Banco la República Oriental del Uruguay'),
        array('code' => '495', 'name' => 'Banco La Provincia de Buenos Aires'),
        array('code' => '505', 'name' => 'Banco Credit Suisse'),
        array('code' => '600', 'name' => 'Banco Luso Brasileiro'),
        array('code' => '604', 'name' => 'Banco Industrial'),
        array('code' => '610', 'name' => 'Banco VR'),
        array('code' => '611', 'name' => 'Banco Paulista'),
        array('code' => '612', 'name' => 'Banco Guanabara'),
        array('code' => '613', 'name' => 'Banco Pecunia'),
        array('code' => '623', 'name' => 'Banco Panamericano'),
        array('code' => '626', 'name' => 'Banco Ficsa'),
        array('code' => '630', 'name' => 'Banco Intercap'),
        array('code' => '633', 'name' => 'Banco Rendimento'),
        array('code' => '634', 'name' => 'Banco Triângulo'),
        array('code' => '637', 'name' => 'Banco Sofisa'),
        array('code' => '638', 'name' => 'Banco Prosper'),
        array('code' => '643', 'name' => 'Banco Pine'),
        array('code' => '652', 'name' => 'Itaú Holding Financeira'),
        array('code' => '653', 'name' => 'Banco Indusval'),
        array('code' => '654', 'name' => 'Banco A.J. Renner'),
        array('code' => '655', 'name' => 'Banco Votorantim'),
        array('code' => '707', 'name' => 'Banco Daycoval'),
        array('code' => '719', 'name' => 'Banif'),
        array('code' => '721', 'name' => 'Banco Credibel'),
        array('code' => '734', 'name' => 'Banco Gerdau'),
        array('code' => '735', 'name' => 'Banco Pottencial'),
        array('code' => '738', 'name' => 'Banco Morada'),
        array('code' => '739', 'name' => 'Banco Galvão de Negócios'),
        array('code' => '740', 'name' => 'Banco Barclays'),
        array('code' => '741', 'name' => 'BRP'),
        array('code' => '743', 'name' => 'Banco Semear'),
        array('code' => '745', 'name' => 'Banco Citibank'),
        array('code' => '746', 'name' => 'Banco Modal'),
        array('code' => '747', 'name' => 'Banco Rabobank International'),
        array('code' => '748', 'name' => 'Banco Cooperativo Sicredi'),
        array('code' => '749', 'name' => 'Banco Simples'),
        array('code' => '751', 'name' => 'Dresdner Bank'),
        array('code' => '752', 'name' => 'BNP Paribas'),
        array('code' => '753', 'name' => 'Banco Comercial Uruguai'),
        array('code' => '755', 'name' => 'Banco Merrill Lynch'),
        array('code' => '756', 'name' => 'Banco Cooperativo do Brasil'),
        array('code' => '757', 'name' => 'KEB'),
    );

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
            /** @noinspection LaravelEloquentGuardedAttributeAssignmentInspection */
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

    public function generateFieldValue(array $field, Generator $faker)
    {
        $name = $field['name'] ?? null;
        $type = $field['type'] ?? 'String';

        if ($type === 'Object.ID') {
            if ($name !== 'id') {
                return (string)Str::uuid();
            }
            return null; // id is managed by DB
        } elseif ($type === 'Faker.js') {
            $fakerValue = $field['value'] ?? '[word.word]';
            return $this->mapFakerValue($faker, $fakerValue);
        } elseif ($type === 'Array') {
            $valueString = $field['value'] ?? '';
            if ($valueString === '') {
                return null;
            }

            // Transforma a string em array dividindo por vírgula e remove espaços extras do começo e fim de cada item
            $items = array_map('trim', explode(',', $valueString));

            // Remove possíveis itens vazios caso o usuário digite algo como "cesar, , lucas"
            $items = array_filter($items, fn($item) => $item !== '');

            // Pega um item aleatório do array
            return !empty($items) ? $faker->randomElement(array_values($items)) : null;
        } else {
            if (isset($field['value']) && $field['value'] !== '') {
                return $field['value'];
            } else {
                switch ($type) {
                    case 'String':
                        return Str::random(10);
                    case 'Number':
                        return $faker->randomNumber();
                    case 'Boolean':
                        return $faker->boolean();
                    default:
                        return null;
                }
            }
        }
    }

    private function mapFakerValue(Generator $faker, string $value)
    {
        // Remove brackets
        $cleanValue = trim($value, '[]');

        switch ($cleanValue) {
            // Pessoa
            case 'person.firstName':
                return $faker->firstName();
            case 'person.lastName':
                return $faker->lastName();
            case 'person.fullName':
                return $faker->name();
            case 'person.prefix':
                return $faker->title();
            case 'person.suffix':
                return $faker->randomElement([
                    'Filho',
                    'Junior',
                    'Neto',
                    'Sobrinho',
                    'Jr.',
                    'Neto Filho',
                    'Segunda',
                    'Terceiro',
                    'Filha',
                    'Neta',
                    'Sobrinha'
                ]);
            case 'person.gender':
                return $faker->randomElement(['Masculino', 'Feminino', 'Outro']);
            case 'person.birthdate_date':
                return $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d');

            // Internet
            case 'internet.email':
                return $faker->safeEmail();
            case 'internet.username':
                return $faker->userName();
            case 'internet.password':
                return $faker->password();
            case 'internet.url':
                return $faker->url();
            case 'internet.domainName':
                return $faker->domainName();
            case 'internet.ipv4':
                return $faker->ipv4();
            case 'internet.ipv6':
                return $faker->ipv6();

            // Endereço
            case 'location.city':
                return $faker->city();
            case 'location.state':
                return $faker->randomElement(collect($this->estados)->pluck('sigla')->toArray());
            case 'location.country':
                return $faker->country();
            case 'location.zipCode':
                return $faker->postcode();
            case 'location.latitude':
                return $faker->latitude();
            case 'location.longitude':
                return $faker->longitude();

            // Empresa
            case 'company.name':
                return $faker->company();
            case 'company.department':
                return $faker->randomElement($this->departamentos);
            case 'company.companyEmail':
                return $faker->companyEmail();

            // Financeiro
            case 'finance.bank':
                return $faker->randomElement(collect($this->bancos)->pluck('value')->toArray());
            case 'finance.agency':
                return $faker->regexify('[0-9]{4}');
            case 'finance.accountNumber':
                return $faker->regexify('[0-9]{5}[0-9X]{1}');
            case 'finance.amount':
                return $faker->randomFloat(2, 10, 1000);
            case 'finance.currencyCode':
                return $faker->currencyCode();

            // Texto
            case 'word.word':
                return $faker->word();
            case 'word.words':
                return implode(' ', $faker->words(3));
            case 'word.sentence':
                return $faker->sentence();
            case 'word.paragraph':
                return $faker->paragraph();

            // --- APENAS DATA (Y-m-d) ---
            case 'date.date_before':
                // Data anterior a hoje (até ontem)
                return $faker->dateTimeBetween('-2 year', '-1 day')->format('Y-m-d');
            case 'date.date_after':
                // Data posterior a hoje (a partir de amanhã)
                return $faker->dateTimeBetween('+1 day', '+2 year')->format('Y-m-d');
            case 'date.date_now':
                // Data atual (Hoje)
                return date('Y-m-d');

            // --- APENAS HORA (H:i:s) ---
            case 'date.time_before':
                // Hora anterior a agora (dentro do dia de hoje)
                return $faker->dateTimeBetween('00:00:00', 'now')->format('H:i:s');
            case 'date.time_after':
                // Hora posterior a agora (dentro do dia de hoje)
                return $faker->dateTimeBetween('now', '23:59:59')->format('H:i:s');
            case 'date.time_now':
                // Hora atual agora
                return date('H:i:s');

            // --- DATA E HORA (Y-m-dTH:i:s) ---
            case 'date.datetime_before':
                // Data e Hora anterior a agora
                return $faker->dateTimeBetween('-2 year', 'now')->format('Y-m-d\TH:i:s');
            case 'date.datetime_after':
                // Data e Hora posterior a agora
                return $faker->dateTimeBetween('now', '+2 year')->format('Y-m-d\TH:i:s');
            case 'date.datetime_now':
                // Data e Hora atual agora
                return date('Y-m-d\TH:i:s');

            // Identificadores
            case 'string.uuid':
                return $faker->uuid();
            case 'database.mongodbObjectId':
                return $faker->regexify('[a-f0-9]{24}');

            // Cores
            case 'color.human':
                return $faker->colorName();
            case 'color.rgb':
                return $faker->rgbCssColor();
            case 'color.hex':
                return $faker->hexColor();

            // Números
            case 'number.int':
                return $faker->randomNumber();
            case 'number.float':
                return $faker->randomFloat(2, 0, 100);

            // Booleano
            case 'datatype.boolean':
                return $faker->boolean();

            // Mídia
            case 'image.avatar':
                return $faker->imageUrl(100, 100, 'cats');
            case 'image.url':
                return $faker->imageUrl(640, 480);

            // Veículos
            case 'vehicle.model':
                return $faker->randomElement($this->modelos);
            case 'vehicle.manufacturer':
                return $faker->randomElement($this->fabricantes);
            case 'vehicle.plate':
                return $faker->regexify('[A-Z]{3}[0-9]{1}[A-Z]{1}[0-9]{2}');

            // Brasil
            case 'brasil.celular':
                return $faker->regexify('(1[1-9]|2[12478]|3[1-578]|4[1-9]|5[13-5]|6[1-9]|7[13-579]|8[1-9]|9[1-9])9[0-9]{8}');
            case 'brasil.telefone':
                return $faker->regexify('(1[1-9]|2[12478]|3[1-578]|4[1-9]|5[13-5]|6[1-9]|7[13-579]|8[1-9]|9[1-9])[2-5]{1}[0-9]{7}');
            case 'brasil.ie':
                return $faker->regexify('[0-9]{12}');
            case 'brasil.pis':
                return $faker->regexify('[0-9]{11}');
            case 'brasil.rg':
                return $faker->regexify('[0-9]{9}');
            case 'brasil.cnh':
                return $faker->regexify('[0-9]{11}');
            case 'brasil.cpf':
                return $this->gerarCPF();
            case 'brasil.cnpj':
                return $this->gerarCNPJ();

            // Cartão de credito
            case 'credit_card.master_card':
                return $this->gerarMasterCard();
            case 'credit_card.visa':
                return $this->gerarVisa();
            case 'credit_card.amex':
                return $this->gerarAmex();
            case 'credit_card.diners_club':
                return $this->gerarDinersClub();
            case 'credit_card.hiper_card':
                return $this->gerarHiperCard();

            default:
                // Fallback trying to call the method directly if it exists
                $parts = explode('.', $cleanValue);
                $methodName = end($parts);
                try {
                    return $faker->$methodName();
                } catch (Exception $e) {
                    return $faker->word();
                }
        }
    }

    public function gerarCPF(): string
    {
        // Gera os 9 primeiros dígitos aleatórios
        $digits = [];
        for ($i = 0; $i < 9; $i++) {
            $digits[] = mt_rand(0, 9);
        }

        // Calcula o 1º dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $digits[$i] * (10 - $i);
        }
        $rest = $sum % 11;
        $digits[9] = ($rest < 2) ? 0 : 11 - $rest;

        // Calcula o 2º dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $digits[$i] * (11 - $i);
        }
        $rest = $sum % 11;
        $digits[10] = ($rest < 2) ? 0 : 11 - $rest;

        return implode('', $digits);
    }

    public function gerarCNPJ(): string
    {
        // Gera os 8 primeiros dígitos aleatórios (base do CNPJ)
        $digits = [];
        for ($i = 0; $i < 8; $i++) {
            $digits[] = mt_rand(0, 9);
        }

        // Adiciona o sufixo padrão de matriz (0001) para os dígitos 9, 10, 11 e 12
        $digits[] = 0;
        $digits[] = 0;
        $digits[] = 0;
        $digits[] = 1;

        // Pesos oficiais para o cálculo dos dígitos verificadores
        $weight1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weight2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        // Calcula o 1º dígito verificador
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $digits[$i] * $weight1[$i];
        }
        $rest = $sum % 11;
        $digits[12] = ($rest < 2) ? 0 : 11 - $rest;

        // Calcula o 2º dígito verificador
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += $digits[$i] * $weight2[$i];
        }
        $rest = $sum % 11;
        $digits[13] = ($rest < 2) ? 0 : 11 - $rest;

        return implode('', $digits);
    }

    public function gerarMasterCard(): string
    {
        // Inicia com 51 a 55 (tamanho 16)
        $number = '5' . mt_rand(1, 5);
        while (strlen($number) < 15) {
            $number .= mt_rand(0, 9);
        }
        return $number . $this->calcularDigitoLuhn($number);
    }

    /**
     * Função auxiliar que calcula o dígito verificador usando o algoritmo de Luhn
     */
    private function calcularDigitoLuhn(string $number): int
    {
        $sum = 0;
        $shouldDouble = true; // Como estamos calculando o próximo dígito vindo da direita, o inverso começa dobrando

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = (int)$number[$i];

            if ($shouldDouble) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $shouldDouble = !$shouldDouble;
        }

        return (10 - ($sum % 10)) % 10;
    }

    public function gerarVisa(): string
    {
        // Inicia com 4 (tamanho 16)
        $number = '4';
        while (strlen($number) < 15) {
            $number .= mt_rand(0, 9);
        }
        return $number . $this->calcularDigitoLuhn($number);
    }

    public function gerarAmex(): string
    {
        // Inicia com 34 ou 37 (tamanho 15)
        $number = '3' . (mt_rand(0, 1) === 0 ? '4' : '7');
        while (strlen($number) < 14) {
            $number .= mt_rand(0, 9);
        }
        return $number . $this->calcularDigitoLuhn($number);
    }

    public function gerarDinersClub(): string
    {
        // Inicia com 36 (tamanho 14)
        $number = '36';
        while (strlen($number) < 13) {
            $number .= mt_rand(0, 9);
        }
        return $number . $this->calcularDigitoLuhn($number);
    }

    public function gerarHiperCard(): string
    {
        // Inicia com 606282 (tamanho 16)
        $number = '606282';
        while (strlen($number) < 15) {
            $number .= mt_rand(0, 9);
        }
        return $number . $this->calcularDigitoLuhn($number);
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
}
