# Entidades

> O domínio de negócio: glossário, entidades, relacionamentos e regras de negócio. Para a estrutura técnica das tabelas, ver [migrations/](../migrations/README.md) — aqui é sobre o *significado* dos dados, não sua implementação.

## 1. Contexto de negócio

API REST da plataforma de ensino **CORIF**, voltada para **vocologia, canto e regência coral** (conforme [OpenApiSpec.php](../../app/Http/Controllers/OpenApi/OpenApiSpec.php)).

## 2. Papéis (atores do sistema)

Decisão-chave: **papel é um atributo fixo e exclusivo do usuário**, via coluna `tipoUser` em `User`. Um usuário tem um único papel — não acumula (ex.: um Professor não pode também ser Aluno na mesma conta; precisaria de duas contas).

| Valor | Papel | Escopo de atuação |
|---|---|---|
| `0` | **Admin** | Global — acesso total à aplicação. |
| `1` | **Gestor** | Curso — administra os cursos que ele mesmo cria. |
| `2` | **Professor** | Módulo — cria/edita conteúdo nos módulos em que está autorizado. |
| `3` | **Aluno** | Curso (via Matrícula) — consome o conteúdo do(s) curso(s) em que está matriculado. |

A coluna `tipoUser` classifica o usuário, mas **não substitui** as relações de escopo — elas continuam necessárias porque um Gestor pode administrar vários Cursos (e um Curso pode ter vários Gestores), um Professor pode atuar em vários Módulos com permissões diferentes em cada um, e um Aluno pode ter Matrículas em vários Cursos:

| Papel | Relação de escopo |
|---|---|
| Gestor | N:N `User` ↔ `Curso` |
| Professor | N:N `User` ↔ `Módulo`, com **permissões por linha da relação** (editar conteúdo, ver progresso, moderar interações) |
| Aluno | via `Matrícula`: `User` ↔ `Curso`, com os `Módulos` inclusos definidos na própria matrícula |

## 3. Cadastro e convites

Nenhum papel além de Aluno se autocadastra. O fluxo de criação de conta é hierárquico, por convite:

| Papel | Quem convida | O convite já vincula a quê? |
|---|---|---|
| **Admin** | Ninguém — criado diretamente no banco de dados, nunca pela API. | — |
| **Gestor** | Admin | Nada — o convite só cria a conta (`tipoUser=Gestor`). O Curso ainda não existe; o Gestor cria seus próprios Cursos depois de cadastrado. |
| **Professor** | Admin ou Gestor | Um `Módulo` específico, já com as permissões daquele professor definidas no momento da criação do convite (editar conteúdo / ver progresso / moderar). |
| **Aluno** (via convite) | Gestor ou Admin | Um `Curso` específico (a matrícula resultante segue a mesma lógica de módulos inclusos da seção 2). |
| **Aluno** (autocadastro) | Ninguém — cadastro público ("cadastre-se") | Nenhum curso por padrão. Acesso só é liberado ao resgatar um **Voucher** — aí sim vinculado a um Curso, com acesso total (todos os módulos). |

## 4. Entidades

### `User`

- **Model**: [User.php](../../app/Models/User.php)
- **Tabela**: `usuarios` (renomeada de `users` — ver [migrations/#4](../migrations/README.md#4-estado-atual-do-schema)).
- **Coluna `tipo_user`**: implementada (tinyint, default `3`=Aluno).
- Usa a trait `HasApiTokens` (Sanctum) para emissão de tokens de autenticação — ver [contratos-api/auth.md](../contratos-api/auth.md).

### `Curso`

- Recurso central de ensino. Pertence a 1+ **Gestores** (N:N).
- Contém 1+ **Módulos**.

### `Módulo`

- Pertence a 1 `Curso`.
- Tem 1+ **Professores** autorizados (N:N com permissões — ver seção 2).
- Contém 1+ **Conteúdos**.
- Contém 1+ **Avaliações**.

### `Conteúdo`

- Pertence a 1 `Módulo`. Material de ensino (aula, texto, mídia — formato ainda a detalhar).

### `Avaliação`

- Pertence a 1 `Módulo` — **não** ao Curso como um todo.
- Criada/gerenciada por um Professor autorizado naquele Módulo.
- Conceito separado de `Conteúdo` (avaliação/prova não é um tipo de conteúdo).
- **Pendência**: "métodos de prova" foi mencionado (ex.: múltipla escolha, dissertativa?) — formato ainda não detalhado.

### `Matrícula`

- Liga `Aluno` (`User`) a `Curso`.
- Define quais `Módulos` daquele curso o aluno tem acesso — pode ser todos (matrícula completa) ou um subconjunto (acesso parcial/venda de módulo avulso dentro do curso).
- Criada por convite (Gestor/Admin) ou por resgate de Voucher (autocadastro, sempre com acesso total).

### `Convite`

- Representa um link de cadastro com prazo/uso controlado, gerado por Admin ou Gestor conforme a tabela da seção 3.
- Carrega: o `tipoUser` de destino, quem criou, e o vínculo (nenhum para Gestor; `módulo_id` + permissões para Professor; `curso_id` para Aluno).
- **Pendência**: regras de expiração e reuso (convite é de uso único? expira em quanto tempo?) — a decidir na migration.

### `Voucher`

- Código promocional resgatável por um Aluno durante o autocadastro público.
- Vinculado a 1 `Curso`; ao ser resgatado, gera uma `Matrícula` com acesso total (todos os módulos).
- **Uso único** — depois de resgatado, não pode ser usado por outro aluno.
- **Pendência**: quem pode criar vouchers (Admin? Gestor do curso vinculado?) — a decidir.

## 5. Relacionamentos (resumo)

```
User(tipoUser=Gestor) ──N:N──> Curso ──1:N──> Módulo ──1:N──> Conteúdo
                                               Módulo ──1:N──> Avaliação
                                               Módulo <──N:N (c/ permissões)── User(tipoUser=Professor)

User(tipoUser=Aluno) ──via Matrícula──> Curso
                        Matrícula define subconjunto de Módulos inclusos

Convite ──cria──> User (Gestor sem vínculo | Professor vinculado a Módulo | Aluno vinculado a Curso)
Voucher ──resgate cria──> Matrícula (acesso total ao Curso vinculado)
```

## 6. Pendências em aberto

- **Estrutura de permissões do Professor por Módulo**: colunas booleanas na tabela pivot (`pode_editar_conteudo`, `pode_ver_progresso`, `pode_moderar`) vs. uma tabela de permissões mais genérica — avaliar quando formos desenhar a migration.
- **Formato de `Conteúdo`**: tipos de mídia/estrutura ainda não definidos.
- **"Métodos de prova" da `Avaliação`**: tipos de avaliação ainda não detalhados.
- **Regras de `Convite`**: expiração, uso único ou múltiplo.
- **Quem cria `Voucher`**: Admin, Gestor do curso, ou ambos.

## 7. Padrão de documentação por entidade

Quando uma entidade nova for modelada, adicionar uma subseção em "4. Entidades" com:

- **Model**: link para o arquivo.
- **Propósito**: o que ela representa no negócio, em uma frase.
- **Atributos principais**: os campos que importam para entender a entidade (não precisa listar tudo, isso a migration já documenta).
- **Relacionamentos**: com quais outras entidades se relaciona e como (`hasMany`, `belongsTo`, etc.).
- **Regras de negócio**: invariantes que o código deve garantir (ex.: "uma matrícula só pode existir se o curso tiver vagas").
