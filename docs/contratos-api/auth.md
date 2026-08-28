# Auth — Autenticação

> Contrato dos endpoints de login/logout. Visão geral de convenções (códigos HTTP, envelope de resposta) em [contratos-api/README.md](README.md).

## Visão geral

Autenticação via **Laravel Sanctum**, com tokens do tipo *personal access token* (não sessão/cookie). O cliente faz login, recebe um token em texto puro, e passa a enviá-lo em toda requisição autenticada:

```
Authorization: Bearer {token}
```

- **Controller**: [AuthController.php](../../app/Http/Controllers/Api/AuthController.php)
- **Tabela**: `personal_access_tokens` (migration do próprio pacote Sanctum).
- **Guard usado no login**: `Auth::attempt()` com o guard padrão (`web`), que valida as credenciais contra o provider Eloquent (`App\Models\User`, tabela `usuarios`) — o guard em si não gera sessão relevante pra API, só serve pra checar a senha.

## `POST /api/login`

- **Autenticação**: nenhuma (rota pública).
- **Request body**:
  ```json
  {
    "email": "usuario@exemplo.com",
    "password": "senha-do-usuario"
  }
  ```
  Validação: `email` obrigatório e formato de e-mail; `password` obrigatório.
- **Response 200** (credenciais válidas):
  ```json
  {
    "token": "1|abcdef123456...",
    "user": {
      "id": 1,
      "name": "Test User",
      "email": "test@example.com",
      "tipo_user": 3,
      "created_at": "...",
      "updated_at": "..."
    }
  }
  ```
  (`password` e `remember_token` não aparecem — ficam ocultos pelo `$hidden` do Model `User`.)
- **Response 401** (credenciais inválidas):
  ```json
  { "message": "Credenciais inválidas" }
  ```
- **Response 422** (`email`/`password` ausentes ou `email` malformado): formato padrão de validação do Laravel (`message` + `errors` por campo).

## `POST /api/logout`

- **Autenticação**: obrigatória — middleware `auth:sanctum`. Requer o header `Authorization: Bearer {token}` de um login válido.
- **Request**: sem corpo.
- **Efeito**: revoga (deleta) o token usado na requisição (`$request->user()->currentAccessToken()->delete()`) — só aquele token específico é invalidado, não todos os tokens do usuário.
- **Response 200**:
  ```json
  { "message": "loggout realizado com sucesso" }
  ```
- **Response 401**: sem token válido no header.

## Pendências observadas

Registrando aqui pra não perder de vista — não é bloqueio, só o que falta pra fechar o padrão do projeto:

- **Sem documentação OpenAPI ainda**: `AuthController` não tem os atributos `#[OA\...]` que os outros endpoints usam (ver [contratos-api/#1](README.md#1-documentação-viva-openapiswagger)) — os endpoints de login/logout não aparecem em `/api/documentation` por enquanto.
- **Typo no texto de resposta do logout**: a mensagem retornada é literalmente `"loggout realizado com sucesso"` (com dois "g") — documentado aqui como está hoje, pra refletir o contrato real.
- **Sem endpoint de registro**: login pressupõe que o usuário já existe na tabela `usuarios` (hoje, só via seeder/tinker). O fluxo de criação de conta (convites, voucher — ver [entidades/#3](../entidades/README.md#3-cadastro-e-convites)) ainda não foi implementado.
