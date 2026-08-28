# Documentação — Corif API

Documentação técnica do projeto, dividida por escopo temático. Cada arquivo é um documento vivo: atualize-o conforme decisões forem tomadas e o projeto evoluir.

| Documento | Escopo |
|---|---|
| [engenharia-de-software/](engenharia-de-software/README.md) | Como escrevemos código: princípios, nomenclatura, Git, testes, papel da IA no aprendizado. |
| [arquitetura/](arquitetura/README.md) | Como o sistema é montado: infraestrutura, camadas da aplicação, estrutura de pastas, ciclo de vida da requisição. |
| [migrations/](migrations/README.md) | Como evoluímos o schema do banco: convenções, regras de alteração, estado atual das tabelas. |
| [contratos-api/](contratos-api/README.md) | O que a API promete a quem consome: formato de resposta, erros, autenticação, endpoints. |
| [entidades/](entidades/README.md) | O domínio de negócio: glossário, entidades, relacionamentos, regras de negócio. |

Cada pasta pode crescer com documentos adicionais conforme o projeto evolui (ex.: `entidades/curso.md`, `contratos-api/autenticacao.md`) — o `README.md` de cada pasta é o índice/visão geral daquele escopo.

## Responsabilidade

Esta documentação é mantida pela IA (Claude Code). O desenvolvimento de features em si segue outra lógica: a IA atua como mentora — explicando, revisando e questionando — em vez de gerar código de negócio pronto, para que o projeto sirva também como espaço de aprendizado do desenvolvedor.
