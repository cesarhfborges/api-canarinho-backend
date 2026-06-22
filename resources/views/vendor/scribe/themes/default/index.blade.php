@php
    use Knuckles\Scribe\Tools\WritingUtils as u;
@endphp
<!doctype html>
<!--suppress ALL -->
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{!! $metadata['title'] !!}</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{!! $assetPathPrefix !!}css/theme-default.style.css" media="screen">
    <link rel="stylesheet" href="{!! $assetPathPrefix !!}css/theme-default.print.css" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

@if(isset($metadata['example_languages']))
    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
        @foreach($metadata['example_languages'] as $lang)
            body .content .{{ $lang }}-example code { display: none; }
        @endforeach
    </style>
@endif

@if($tryItOut['enabled'] ?? true)
    <script>
        var tryItOutBaseUrl = "{!! $tryItOut['base_url'] ?? config('app.url') !!}";
        var useCsrf = Boolean({!! $tryItOut['use_csrf'] ?? null !!});
        var csrfUrl = "{!! $tryItOut['csrf_url'] ?? null !!}";
    </script>
    <script src="{{ u::getVersionedAsset($assetPathPrefix.'js/tryitout.js') }}"></script>
@endif

    <script src="{{ u::getVersionedAsset($assetPathPrefix.'js/theme-default.js') }}"></script>

    <style>
        /* Estilo do Botão Flutuante */
        #scrollTopBtn {
            position: fixed; /* Fixed prende o botão na tela inteira */
            bottom: 20px;
            right: 20px;
            z-index: 999; /* Garante que fica por cima de tudo */
            background-color: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
            padding: 12px 18px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);

            /* Efeito suave para aparecer e sumir */
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }

        #scrollTopBtn:hover {
            background-color: #1d4ed8;
        }

        /* Classe que o JS vai colocar para mostrar o botão */
        #scrollTopBtn.show {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>

<body data-languages="{{ json_encode($metadata['example_languages'] ?? []) }}">

@include("scribe::themes.default.sidebar")

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <div>
            <h1>Bem-vindo à {{ config('app.name') }}</h1>
            <p>
                Este sistema atua como um <strong>Mock Server Inteligente</strong>. Ele é dividido em duas partes principais:
            </p>
            <ul>
                <li><strong>Rotas de Administração:</strong> Permitem gerenciar Projetos, Endpoints permitidos, Tokens de acesso e Regras de Mock (condições dinâmicas baseadas no payload ou headers).</li>
                <li><strong>Rota Dinâmica (Catch-All):</strong> Uma única rota que simula qualquer endpoint configurado (<code>/api/{username}/{projectSlug}/{endpointPath}</code>). Ela aceita todos os verbos HTTP e armazena os envios no banco de dados, se comportando como uma API real.</li>
            </ul>
            <p>Use o painel lateral para explorar os métodos.</p>
        </div>
        {!! $metadata['description'] ?? '' !!}
        {!! $intro !!}

        {!! $auth !!}

        @include("scribe::themes.default.groups")

        {!! $append !!}
        <button id="scrollTopBtn">⬆️ Topo</button>
    </div>
    <div class="dark-box">
        @if(isset($metadata['example_languages']))
            <div class="lang-selector">
                @foreach($metadata['example_languages'] as $name => $lang)
                    @php if (is_numeric($name)) $name = $lang; @endphp
                    <button type="button" class="lang-button" data-language-name="{{$lang}}">{{$name}}</button>
                @endforeach
            </div>
        @endif
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const button = document.getElementById("scrollTopBtn");

        if (button) {
            // 1. Escuta a rolagem da janela global (window)
            window.addEventListener("scroll", function() {
                // Verifica se a página rodou mais de 300px para baixo
                if (window.scrollY > 300) {
                    button.classList.add("show");
                } else {
                    button.classList.remove("show");
                }
            });

            // 2. Faz a janela inteira subir de forma suave
            button.addEventListener("click", function() {
                window.scrollTo({
                    top: 0,
                    behavior: "smooth" // Rolagem suave nativa do navegador
                });
            });
        }
    });
</script>
</body>
</html>
