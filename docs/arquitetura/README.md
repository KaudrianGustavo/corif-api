# Arquitetura

> Como o sistema é montado: infraestrutura, camadas internas da aplicação e o caminho de uma requisição. Para convenções de como o código é escrito, ver [engenharia-de-software/](../engenharia-de-software/README.md).

## 1. Infraestrutura (Docker)

```
Cliente (browser/app)
      │  HTTPS
      ▼
  nginx (corif_nginx)   ← TLS local, certs em docker/certs, expõe 127.0.0.10:80/443
      │  proxy_pass / fastcgi
      ▼
  api (corif_api)       ← PHP-FPM, Laravel 13, PHP 8.3
      │
      ▼
  mysql (corif_mysql)   ← MySQL 8.0, volume corif_mysql_data
```

- Orquestrado por `docker-compose.yml`, 3 serviços na rede `corif_network`.
- `api` só sobe depois que `mysql` está saudável (`depends_on: condition: service_healthy`, via `mysqladmin ping`).
- Configuração do nginx em `docker/nginx/default.conf`; certificados locais em `docker/certs/`.
- `mysql` publica a porta `3306` em `127.0.0.1` (`docker-compose.yml`), para permitir acesso via cliente de banco externo (ex.: HeidiSQL) durante o desenvolvimento local. Credenciais em `.env` (`DB_USERNAME`/`DB_PASSWORD` para o usuário de aplicação, `DB_ROOT_PASSWORD` para root).

**Ponto em aberto**: `.env.example` (o template versionado) ainda aponta `DB_CONNECTION=sqlite` por padrão, herdado do skeleton do Laravel — o `.env` real de desenvolvimento já usa MySQL corretamente. Vale atualizar o `.env.example` para refletir MySQL como padrão do projeto, já que é o ambiente que o `docker-compose.yml` espera.

## 2. Estrutura de pastas

Estrutura padrão do Laravel, com o que já está em uso destacado:

```
app/
  Http/
    Controllers/
      Api/          ← controllers de endpoints de negócio (ex.: HealthController)
      OpenApi/       ← metadados globais do Swagger (OpenApiSpec.php)
    Requests/        ← Form Requests (validação) — a criar conforme necessário
    Resources/       ← API Resources (formatação de saída) — a criar conforme necessário
  Models/            ← Eloquent Models (hoje só o User padrão)
  Services/          ← regra de negócio complexa — a criar sob demanda, não antecipar
  Providers/
routes/
  api.php            ← todas as rotas de API
database/
  migrations/
  factories/
  seeders/
config/
  l5-swagger.php     ← configuração da documentação OpenAPI
docker/
  nginx/, certs/
```

## 3. Camadas da aplicação

Fluxo esperado para uma requisição de negócio:

```
Route (routes/api.php)
  → Middleware (ex.: auth:sanctum, quando existir)
  → Controller           (orquestra, não decide regra de negócio)
    → Form Request        (valida e autoriza a entrada)
    → Service              (regra de negócio, SE houver complexidade real)
    → Model / Eloquent      (persistência e regras de dados)
  → API Resource          (formata a saída)
  → Response JSON
```

Regra prática: **não crie uma camada antes dela ser necessária.** Um CRUD trivial pode ir direto de Controller para Model. Uma camada de Service só se justifica quando há regra de negócio que não é responsabilidade nem do Controller (orquestração) nem do Model (dados).

## 4. Ciclo de vida de uma requisição — hoje

Único fluxo real implementado (`GET /api/ping`):

1. Cliente → `nginx` (HTTPS) → PHP-FPM do container `api`.
2. Laravel resolve a rota em `routes/api.php`.
3. `HealthController::ping()` responde diretamente, sem tocar banco — por design, um healthcheck não deve depender de infraestrutura externa.
4. Resposta `{ "status": "ok" }`, HTTP 200.

Conforme fluxos de negócio reais forem implementados (autenticação, domínio pedagógico), o ciclo passa a incluir Form Request → Service/Model → Resource, como descrito acima.

## 5. Decisões de arquitetura em aberto

Registrar aqui decisões pendentes até serem resolvidas (e mover para "decididas" quando fechadas):

- **Formato de resposta padrão da API** (envelope de sucesso/erro) — ver [contratos-api/](../contratos-api/README.md).
- **Versionamento de rotas** (`/api/v1/...` ou não) — ainda não decidido; avaliar antes do primeiro consumidor externo.
- **`.env.example` desatualizado** (aponta sqlite, projeto usa MySQL) — ver seção 1.
- **Camada de Services**: ainda não existe nenhuma instância — critério de quando criar está descrito na seção 3, mas o primeiro caso real vai calibrar o padrão do projeto.
