# Contratos de API

> O que a API promete a quem a consome: formato de resposta, erros, autenticação, e o detalhamento de cada endpoint. Para como a requisição é processada por dentro, ver [arquitetura/](../arquitetura/README.md).

## 1. Documentação viva (OpenAPI/Swagger)

- Todo endpoint **deve** ser documentado via atributos `#[OA\...]` no próprio Controller — este arquivo é um resumo legível, a fonte de verdade executável é o código.
- Metadados globais (título, versão, `SecurityScheme`) centralizados em [OpenApiSpec.php](../../app/Http/Controllers/OpenApi/OpenApiSpec.php).
- UI interativa gerada pelo `l5-swagger`, servida em `/api/documentation`. Regenerar com `php artisan l5-swagger:generate` após mudanças (verificar se `L5_SWAGGER_GENERATE_ALWAYS` está ativo em `.env` antes de assumir que é automático).

## 2. Convenções de contrato (a decidir e fixar aqui)

Estas decisões ainda **não foram tomadas** no projeto — a primeira feature de negócio real vai calibrar o padrão, que deve ser registrado aqui assim que decidido:

- **Envelope de resposta de sucesso**: ex. `{ "data": {...} }` (padrão de API Resource do Laravel) vs. objeto plano.
- **Envelope de erro de validação**: Laravel gera por padrão `{ "message": "...", "errors": { "campo": ["mensagem"] } }` — adotar esse padrão ou customizar via `Handler`?
- **Paginação**: usar o padrão `paginate()` do Laravel (`data`, `links`, `meta`) ou uma resposta simplificada?
- **Versionamento**: `/api/v1/...` desde já, ou só quando houver primeiro consumidor externo?

## 3. Convenções já fixadas

- **Códigos HTTP**:
  - `200` — sucesso em leitura/atualização.
  - `201` — recurso criado.
  - `204` — sucesso sem corpo de resposta (ex.: delete).
  - `422` — erro de validação (padrão Laravel).
  - `401` — não autenticado.
  - `403` — autenticado mas sem permissão.
  - `404` — recurso não encontrado.
  - `500` — erro não tratado (deve ser exceção rara, não fluxo esperado).
- **Autenticação**: Laravel Sanctum, Bearer token (`SecurityScheme` já declarado no OpenAPI como `sanctum`). **Status: declarado, ainda não implementado** — nenhuma rota usa `auth:sanctum` hoje.

## 4. Endpoints implementados

### `GET /api/ping`

- **Controller**: [HealthController::ping()](../../app/Http/Controllers/Api/HealthController.php)
- **Autenticação**: nenhuma.
- **Request**: sem parâmetros.
- **Response 200**:
  ```json
  { "status": "ok" }
  ```
- **Propósito**: healthcheck de infraestrutura — não deve depender de banco de dados ou serviços externos, para servir como sonda confiável (load balancer, monitoramento).

## 5. Endpoints planejados

Checklist a atualizar conforme implementado — mover cada item para a seção 4 com o detalhamento completo quando pronto:

- [ ] **Autenticação**: registro, login, logout/revogação de token (Sanctum).
- [ ] **Recursos do domínio pedagógico**: a definir junto com [entidades/](../entidades/README.md).

## 6. Checklist antes de expor um endpoint novo

- [ ] Documentado com atributos `#[OA\...]`.
- [ ] Segue o envelope de resposta padrão (uma vez decidido na seção 2).
- [ ] Erros de validação/autorização retornam o código HTTP correto.
- [ ] Se autenticado, usa `auth:sanctum` e uma `Policy` se o recurso pertence a um usuário.
- [ ] Testado (ver [engenharia-de-software/#6-testes](../engenharia-de-software/README.md#6-testes)).
