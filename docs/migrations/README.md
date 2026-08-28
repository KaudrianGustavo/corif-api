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
| `0001_01_01_000000_create_users_table` | `users` *(renomeada depois — ver abaixo)*, `password_reset_tokens`, `sessions` | Model `User` padrão do Laravel. |
| `0001_01_01_000001_create_cache_table` | `cache`, `cache_locks` | Suporte ao driver de cache em banco (`CACHE_STORE=database`). |
| `0001_01_01_000002_create_jobs_table` | `jobs`, `job_batches`, `failed_jobs` | Suporte a filas (`QUEUE_CONNECTION=database`). |
| `2026_08_28_140401_add_tipo_user_to_users_table` | altera `users` (+coluna `tipo_user`) | Classifica o usuário (0=Admin, 1=Gestor, 2=Professor, 3=Aluno) — ver [entidades/#2](../entidades/README.md#2-papéis-atores-do-sistema). Default `3` (Aluno), para o autocadastro público. |
| `2026_08_28_140459_create_cursos_table` | `cursos` | Só `id` + `timestamps` até agora — colunas de negócio (nome, descrição, status) ainda não definidas. |
| `2026_08_28_140826_rename_users_table_to_usuarios` | renomeia `users` → `usuarios` | Consistência de idioma com o resto do domínio (ver [entidades/#4](../entidades/README.md#4-entidades)). Model `User` aponta pra `usuarios` via `protected $table`. |
| `2026_08_28_164944_create_personal_access_tokens_table` | `personal_access_tokens` | Gerada pelo pacote `laravel/sanctum` (`artisan install:api`) — guarda os tokens de autenticação da API. Ver [contratos-api/auth.md](../contratos-api/auth.md). |

## 5. Migrations planejadas

Ordem sugerida, respeitando dependência de chave estrangeira (ver [entidades/](../entidades/README.md)):

- [x] `users`/`usuarios` — adicionar `tipo_user`, renomear tabela
- [x] `personal_access_tokens` (via Sanctum)
- [ ] `cursos` — falta preencher as colunas de negócio (só existe o esqueleto id+timestamps)
- [ ] `curso_gestor` (pivot Curso↔Gestor)
- [ ] `modulos` (depende de `cursos`)
- [ ] `modulo_professor` (pivot Módulo↔Professor, com permissões)
- [ ] `conteudos` (depende de `modulos`)
- [ ] `avaliacoes` (depende de `modulos`)
- [ ] `matriculas` (depende de `users` + `cursos`)
- [ ] `matricula_modulo` (pivot Matrícula↔Módulo, módulos inclusos)
- [ ] `convites`
- [ ] `vouchers`

Cada migration nova ganha uma linha na tabela da seção 4 quando implementada.

## 6. Checklist antes de criar uma migration

- [ ] Nome de tabela/colunas segue as convenções da seção 1.
- [ ] Foreign keys com `->constrained()` e `->index()` onde fizer sentido.
- [ ] `down()` reverte corretamente tudo que o `up()` criou.
- [ ] Se a tabela representa uma entidade de negócio nova, atualizar [entidades/](../entidades/README.md).
- [ ] Rodar a migration localmente (`php artisan migrate`) antes de abrir PR.
