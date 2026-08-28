# Entidades

> O domínio de negócio: glossário, entidades, relacionamentos e regras de negócio. Para a estrutura técnica das tabelas, ver [migrations/](../migrations/README.md) — aqui é sobre o *significado* dos dados, não sua implementação.

## 1. Contexto de negócio

API REST da plataforma de ensino **CORIF**, voltada para **vocologia, canto e regência coral** (conforme [OpenApiSpec.php](../../app/Http/Controllers/OpenApi/OpenApiSpec.php)).

O domínio ainda **não foi modelado** — esta é a próxima decisão de projeto relevante, e vale ser pensada antes de sair criando migrations. Perguntas para guiar essa modelagem:

- Quem são os atores do sistema? (aluno, professor, administrador, avaliador?)
- Qual é a unidade central de ensino? (curso, turma, módulo, aula individual?)
- O que é avaliado e como? (existe uma "avaliação vocal" como conceito de negócio?)
- Existe hierarquia entre entidades? (um curso tem turmas, uma turma tem matrículas?)

## 2. Entidades existentes hoje

### `User`

- **Model**: [User.php](../../app/Models/User.php)
- **Origem**: skeleton padrão do Laravel — ainda genérico, não adaptado ao domínio CORIF.
- **Atributos**: `name`, `email`, `password` (+ campos técnicos de autenticação).
- **Pendências**: decidir se `User` é a entidade final para "usuário do sistema" ou se vai existir uma distinção entre tipos de usuário (aluno/professor/admin) — via coluna de papel (`role`), tabela separada, ou Models distintos.

## 3. Entidades planejadas

A preencher conforme o domínio for modelado com o desenvolvedor. Sugestão de processo: modelar uma entidade por vez, começando pela mais central (provavelmente o que hoje é `User` — decidir os tipos de usuário), documentando aqui antes de criar a migration correspondente.

## 4. Padrão de documentação por entidade

Quando uma entidade nova for modelada, adicionar uma subseção em "2. Entidades existentes hoje" com:

- **Model**: link para o arquivo.
- **Propósito**: o que ela representa no negócio, em uma frase.
- **Atributos principais**: os campos que importam para entender a entidade (não precisa listar tudo, isso a migration já documenta).
- **Relacionamentos**: com quais outras entidades se relaciona e como (`hasMany`, `belongsTo`, etc.).
- **Regras de negócio**: invariantes que o código deve garantir (ex.: "uma matrícula só pode existir se o curso tiver vagas").
