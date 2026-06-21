<?php

return [
    "labels" => [
        "search" => "Pesquisar",
        "base_url" => "URL Base",
    ],

    "auth" => [
        "none" => "Esta API não exige autenticação.",
        "instruction" => [
            "query" => <<<TEXT
                Para autenticar requisições, inclua um parâmetro de query **`:parameterName`** na requisição.
                TEXT,
            "body" => <<<TEXT
                Para autenticar requisições, inclua um parâmetro **`:parameterName`** no corpo (body) da requisição.
                TEXT,
            "query_or_body" => <<<TEXT
                Para autenticar requisições, inclua um parâmetro **`:parameterName`** na query string ou no corpo da requisição.
                TEXT,
            "bearer" => <<<TEXT
                Para autenticar requisições, inclua um header **`Authorization`** com o valor **`"Bearer :placeholder"`**.
                TEXT,
            "basic" => <<<TEXT
                Para autenticar requisições, inclua um header **`Authorization`** no formato **`"Basic {credentials}"`**. 
                O valor de `{credentials}` deve ser seu usuário e senha, separados por dois pontos (:), 
                e encodados em base64.
                TEXT,
            "header" => <<<TEXT
                Para autenticar requisições, inclua um header **`:parameterName`** com o valor **`":placeholder"`**.
                TEXT,
        ],
        "details" => <<<TEXT
            Todos os endpoints autenticados possuem um selo `requires authentication` na documentação abaixo.
            TEXT,
    ],

    "headings" => [
        "introduction" => "Introdução",
        "auth" => "Autenticando requisições",
    ],

    "endpoint" => [
        "request" => "Requisição",
        "headers" => "Cabeçalhos (Headers)",
        "url_parameters" => "Parâmetros de URL",
        "body_parameters" => "Parâmetros do Corpo (Body)",
        "query_parameters" => "Parâmetros de Query",
        "response" => "Resposta",
        "response_fields" => "Campos da Resposta",
        "example_request" => "Exemplo de Requisição",
        "example_response" => "Exemplo de Resposta",
        "responses" => [
            "binary" => "Dados binários",
            "empty" => "Resposta vazia",
        ],
    ],

    "try_it_out" => [
        "open" => "Testar Rota ⚡",
        "cancel" => "Cancelar 🛑",
        "send" => "Enviar Requisição 💥",
        "loading" => "⏱ Enviando...",
        "received_response" => "Resposta recebida",
        "request_failed" => "A requisição falhou com erro",
        "error_help" => <<<TEXT
            Dica: Verifique se você está conectado à internet.
            Se você for o administrador desta API, verifique se ela está rodando e se o CORS está habilitado.
            Você pode checar o console de desenvolvedor do navegador para mais informações.
            TEXT,
    ],

    "links" => [
        "postman" => "Ver collection do Postman",
        "openapi" => "Ver especificação OpenAPI",
    ],
];
