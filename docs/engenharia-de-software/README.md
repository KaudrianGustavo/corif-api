# Engenharia de Software

> Como escrevemos código neste projeto: princípios, convenções e processo. Não é sobre a arquitetura do sistema (ver [arquitetura/](../arquitetura/README.md)) — é sobre a qualidade e o processo do código em si.

## 1. Papel da IA neste projeto

Este projeto tem um propósito duplo: entregar a API do CORIF e ser espaço de desenvolvimento pessoal do desenvolvedor. Isso define como a IA deve atuar:

- Para **lógica de negócio e decisões de arquitetura**: a IA explica conceitos, revisa código, questiona escolhas e aponta trade-offs — não entrega a implementação pronta. O objetivo é o desenvolvedor construir o raciocínio.
- Para **boilerplate/infra repetitiva** (configuração de pacote, scaffolding, docker): tudo bem pedir geração direta.
- **Documentação** (`docs/*.md`) é a exceção — é responsabilidade da IA escrever e manter atualizada.

## 2. Princípios de código

- **YAGNI**: não construir abstrações (Services, Repositories, interfaces) antes de existir uma necessidade real e concreta. Um Controller chamando o Model diretamente é aceitável para um CRUD trivial.
- **Responsabilidade única**: se uma classe está fazendo validação + regra de negócio + formatação de resposta, é sinal de que faltam camadas (ver [arquitetura/#3-camadas-da-aplicação](../arquitetura/README.md#3-camadas-da-aplicação)).
- **Tipagem forte sempre**: PHP 8.3 suporta tipos em parâmetros, retornos e propriedades — declare todos. Evita uma classe inteira de bugs bobos e serve como documentação viva.
- **Sem "magia" implícita**: preferir código explícito a comportamento escondido (ex.: evitar mutators/accessors que fazem side-effects não óbvios).

## 3. Nomenclatura

| Elemento | Convenção | Exemplo |
|---|---|---|
| Classes | `PascalCase` | `EnrollmentService`, `VoiceAssessment` |
| Métodos/variáveis | `camelCase` | `calculateScore()`, `$enrolledAt` |
| Tabelas | `snake_case`, plural | `voice_assessments` |
| Colunas de FK | `singular_id` | `user_id`, `course_id` |
| Rotas de API | `kebab-case` | `/api/voice-assessments` |
| Rotas nomeadas | `dot.case` | `voice-assessments.store` |

## 4. Estilo de código

- **PSR-12** via `laravel/pint` (já instalado). Rodar `./vendor/bin/pint` antes de todo commit.
- Sem lógica de negócio em Controllers — ver detalhamento de camadas em [arquitetura/](../arquitetura/README.md).
- Form Requests para qualquer validação com mais de 2-3 regras (nada de `$request->validate()` inline crescendo sem controle).
- API Resources para formatar toda saída JSON — nunca devolver um Model/Collection cru.

## 5. Convenções de Git

Padrão já em uso no histórico do projeto:

- **Branches**: `tipo/usuario/descricao-curta` — ex. `feat/gustavo.croda/ajustando-certificados`, `chore/gustavo.croda/setup-swagger`. Tipos: `feat`, `fix`, `chore`.
- **Commits**: [Conventional Commits](https://www.conventionalcommits.org/) em português — `tipo: descrição` (ex. `feat: implementação inicial do swagger`).
- **Fluxo**: PR no GitHub → merge em `development` → `development` promovido para `main` quando estável.
- `.env` nunca é commitado com segredos reais (já garantido pelo `.gitignore`).

## 6. Testes

- Stack: PHPUnit/Pest (`phpunit.xml` configurado).
- Todo endpoint de negócio novo precisa de, no mínimo: 1 teste de caminho feliz + 1 teste de erro (validação ou autorização).
- Rodar `composer test` / `php artisan test` antes de abrir PR.
- Testes de infraestrutura pura (ex.: `/api/ping`) podem ser mais simples — só garantir status code e shape da resposta.

## 7. Segurança no código

- Nunca `create()`/`fill()` direto de `$request->all()` — usar `$fillable`/`$guarded` no Model e whitelisting via Form Request.
- Toda entrada de usuário passa por validação explícita antes de tocar o banco.
- Autorização (`Policies`/`Gates`) obrigatória para qualquer recurso vinculado a um usuário específico.
- Segredos sempre via `.env`, nunca hardcoded no código.

## 8. Checklist antes de abrir PR

- [ ] `./vendor/bin/pint` rodado, sem alterações pendentes.
- [ ] `php artisan test` passando.
- [ ] Endpoint novo documentado com atributos OpenAPI (ver [contratos-api/](../contratos-api/README.md)).
- [ ] Migration nova segue as regras de [migrations/](../migrations/README.md), se aplicável.
- [ ] Entidade nova ou alterada documentada em [entidades/](../entidades/README.md), se aplicável.
- [ ] Sem segredos ou dados sensíveis commitados.
