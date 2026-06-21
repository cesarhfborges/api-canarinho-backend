# API Canarinho - Mock Server Dinâmico

## Descrição do Projeto
A **API Canarinho** é um Mock Server Dinâmico e Inteligente construído utilizando o micro-framework **Lumen 10**. Ele foi projetado para facilitar o desenvolvimento front-end e mobile simulando respostas de API reais, permitindo gerenciar múltiplos projetos, rotas personalizadas e regras condicionais de mock (responses dinâmicas baseadas em headers ou payloads).

### Funcionalidades Principais
- **Rotas de Administração Seguras:** Protegidas por cookies. Crie, liste, edite e delete Projetos, Endpoints e Regras.
- **Rota Catch-All Inteligente:** Simule qualquer método HTTP (GET, POST, PUT, DELETE, PATCH) em uma única rota de captura dinâmica.
- **Documentação Automática:** Integração nativa com o **Scribe**, oferecendo uma documentação web sempre atualizada (`/docs`) das rotas de admin e do mock dinâmico, totalmente em português.
- **Configuração de CORS:** Flexível e integrada via `.env` (múltiplos domínios suportados).
- **Proteção por Rate Limit:** Sistema anti-abuso global via banco de dados/cache (padrão de 2000 requests/hora configurável).
- **Processamento em Fila (Queue):** Desempenho mantido por processamento de tarefas pesadas de forma assíncrona com `QUEUE_CONNECTION=database`.

---

## Como Instalar

Siga os passos abaixo para preparar o ambiente:

1. **Clone o repositório:**
   ```bash
   git clone https://seu-repositorio.com/api-canarinho.git
   cd api-canarinho/backend
   ```

2. **Instale as dependências pelo Composer:**
   ```bash
   composer install
   ```

3. **Configure as variáveis de ambiente:**
   Copie o arquivo de exemplo para gerar o seu `.env`:
   ```bash
   cp .env.example .env
   ```
   Edite o `.env` configurando sua conexão com o MySQL, domínios permitidos no CORS e fuso horário.

4. **Rode as Migrations:**
   Crie as tabelas necessárias do sistema, projetos, logs e filas:
   ```bash
   php artisan migrate
   ```

---

## Como Rodar

Para o ambiente de **desenvolvimento local**, você pode utilizar o servidor embutido do PHP:
```bash
php -S localhost:8000 -t public
```

Após iniciar, o sistema estará rodando em:
- **API Base:** `http://localhost:8000/api`
- **Documentação Scribe:** `http://localhost:8000/docs`
- **Health Check (Monitoramento):** `http://localhost:8000/api/health`

---

## Exemplos de Queue e Demais Ferramentas

### Fila (Queue)
A aplicação está configurada para utilizar o banco de dados como motor de fila (`QUEUE_CONNECTION=database`). Isso impede que requisições longas ou pesadas (como envios de e-mails de alerta ou geração assíncrona) afetem o tempo de resposta da API para o usuário final.

Para que a fila seja processada, você deve iniciar um Queue Worker rodando o seguinte comando no terminal:
```bash
php artisan queue:work
```
*Dica para Produção:* Sempre mantenha o `queue:work` rodando via Docker (Worker container) ou utilize o `Supervisor` (Linux) para mantê-lo rodando de forma persistente. Tarefas que falharem irão automaticamente para a tabela `failed_jobs`.

### Scribe (Documentação)
Sempre que você criar novas rotas no sistema, atualize a documentação executando:
```bash
php artisan scribe:generate
```

---

## Problemas Comuns e Como Resolver

### 1. Erro de CORS ("Blocked by CORS policy") no Front-End
**Solução:** Certifique-se de que a origem do seu front-end (ex: `http://localhost:4200`) está incluída corretamente na variável `CORS_ALLOWED_ORIGINS` dentro do seu `.env`. Lembre-se que as origens devem ser separadas por vírgula e **não devem conter a barra final (/)**. 

### 2. Rotas não estão sendo atualizadas na Documentação (Scribe)
**Solução:** Sempre que adicionar uma rota, você precisa rodar `php artisan scribe:generate` e limpar o cache do navegador. Se uma rota não aparece, certifique-se de não ter apagado os comentários `@group` no respectivo Controller.

### 3. Fila de Tarefas travada ou não processando
**Solução:** Verifique se o comando `php artisan queue:work` está rodando no terminal. Se as tarefas estiverem caindo direto na tabela `failed_jobs`, verifique seus logs em `storage/logs/lumen.log` para identificar erros na lógica da tarefa despachada.

### 4. Resposta 429 "Too Many Requests"
**Solução:** Isso significa que o Rate Limit nativo bloqueou a requisição para não sobrecarregar o servidor. O padrão é de 2000 requisições a cada 60 minutos. Caso seja um teste de estresse de desenvolvimento, aumente as variáveis `RATE_LIMIT_REQUESTS` e `RATE_LIMIT_TIME` no `.env`.

---

## Dados Pessoais e Informações

**Desenvolvedor:** Cesar  
**E-mail / Contato:** *[Insira seu e-mail aqui]*  
**LinkedIn:** *[Insira seu LinkedIn aqui]*  
**GitHub:** *[Insira seu GitHub aqui]*  

*Projeto criado e orquestrado para facilitar ambientes de testes escaláveis e ágeis. Em caso de dúvidas sobre regras de negócio específicas ou novas integrações, entre em contato através dos meios acima.*
