# Migrations

> Como o schema do banco evolui: convenções, regras de alteração e o estado atual das tabelas. Para o significado de negócio de cada tabela (o que ela representa), ver [entidades/](../entidades/README.md) — aqui é sobre a estrutura técnica do schema.

## 1. Convenções

- **Nome do arquivo**: `YYYY_MM_DD_HHMMSS_acao_tabela.php`, gerado automaticamente por `php artisan make:migration`. Descrever a ação: `create_courses_table`, `add_status_to_enrollments_table`.
- **Nome de tabelas**: `snake_case`, plural (`voice_assessments`, não `voiceAssessment` nem `voice_assessment`).
- **Chaves primárias**: `$table->id()` (bigint auto-increment) por padrão, salvo necessidade explícita de outro tipo (ex.: UUID, se decidido no futuro).
- **Chaves estrangeiras**: `$table->foreignId('user_id')->constrained()` — sempre com constraint real no banco, não só a coluna solta.
- **Timestamps**: `$table->timestamps()` em toda tabela que representa uma entidade de negócio (não necessariamente em tabelas puramente técnicas/pivot, avaliar caso a caso).
- **Soft deletes**: usar `$table->softDeletes()` quando o registro precisa de "exclusão lógica" (ex.: histórico de matrículas). Não aplicar por padrão em tudo — é uma decisão por entidade, registrada em [entidades/](../entidades/README.md).
- **Índices**: toda coluna usada em `WHERE`/`JOIN` com frequência (fora a PK/FK, que já ganham índice automaticamente) deve ter `->index()` explícito.

## 2. Regra de ouro: migrations são histórico, não rascunho

- Uma migration **já mergeada em `development` ou `main`** nunca é editada — qualquer ajuste vira uma **migration nova**. Editar uma migration existente quebra quem já rodou o schema antigo (colegas, CI, produção).
- Durante o desenvolvimento de uma feature **na sua própria branch, ainda não mergeada**, é aceitável editar/recriar a migration livremente — nada foi compartilhado ainda.
- Migration com erro em produção: nunca faça `down()` + reescreva `up()` manualmente no banco. Crie uma nova migration de correção.

## 3. Seeders e Factories

- **Factories** (`database/factories/`): usadas para gerar dados de teste (automação de testes, `php artisan tinker`). Toda entidade de negócio nova deveria ganhar uma Factory ao ser criada.
- **Seeders** (`database/seeders/`): usados para popular dados essenciais de ambiente (ex.: um usuário admin padrão) — não confundir com massa de dados de teste, que é papel da Factory.

## 4. Estado atual do schema

Migrations existentes hoje (todas herdadas do skeleton padrão do Laravel — nenhuma migration de domínio criada ainda):

| Migration | Tabelas criadas | Observação |
|---|---|---|
| `0001_01_01_000000_create_users_table` | `users`, `password_reset_tokens`, `sessions` | Model `User` padrão do Laravel, ainda não adaptado ao domínio CORIF. |
| `0001_01_01_000001_create_cache_table` | `cache`, `cache_locks` | Suporte ao driver de cache em banco (`CACHE_STORE=database`). |
| `0001_01_01_000002_create_jobs_table` | `jobs`, `job_batches`, `failed_jobs` | Suporte a filas (`QUEUE_CONNECTION=database`). |

## 5. Migrations planejadas

A preencher conforme o domínio pedagógico (ver [entidades/](../entidades/README.md)) for modelado — cursos, matrículas, avaliações vocais, etc. Cada migration de domínio nova deve ganhar uma linha na tabela acima quando implementada.

## 6. Checklist antes de criar uma migration

- [ ] Nome de tabela/colunas segue as convenções da seção 1.
- [ ] Foreign keys com `->constrained()` e `->index()` onde fizer sentido.
- [ ] `down()` reverte corretamente tudo que o `up()` criou.
- [ ] Se a tabela representa uma entidade de negócio nova, atualizar [entidades/](../entidades/README.md).
- [ ] Rodar a migration localmente (`php artisan migrate`) antes de abrir PR.
