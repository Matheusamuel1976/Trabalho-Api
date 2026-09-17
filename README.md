# Catálogo de Caminhões

API REST em **PHP 8.3+ puro** para gerenciamento de catálogo de caminhões em uma concessionária.

> Projeto acadêmico da Unidade Curricular **Desenvolvimento de APIs — 3º TI B**.

## Tecnologias

- PHP 8.3+
- MySQL (PDO)
- POO / MVC simples
- Composer
- Swagger-PHP / OpenAPI (documentação)
- Git / GitHub
- Insomnia (testes manuais)
- Laravel Herd (ambiente local)

## Estrutura do projeto

```
CatalogoCaminhoes/
├── config/
│   └── config.php               # Carrega o .env e define constantes de config
├── Controller/
│   └── ControladorCaminhao.php
├── Models/
│   ├── Conexao.php              # Conexão PDO (única responsabilidade)
│   └── Caminhao.php             # Todo o SQL da entidade + validação
├── Routes/
│   └── rotas.php                # Roteamento: método HTTP + URI -> Controlador
├── Swagger/
│   └── openapi.php              # Anotações OpenAPI (PHP Attributes)
├── database/
│   └── schema.sql               # Criação do banco + dados de exemplo
├── public/
│   ├── index.php                # Ponto de entrada (carrega tudo e chama as rotas)
│   ├── openapi.json             # Documentação OpenAPI já gerada
│   └── docs.html                # Swagger UI (lê o openapi.json)
├── insomnia/
│   └── CatalogoCaminhoes-insomnia.json  # Coleção pronta para Insomnia
├── .env.example
├── .gitignore
├── composer.json
└── README.md
```

## Arquitetura

- **Models** (`Conexao`, `Caminhao`): conexão PDO e todo o SQL com *prepared statements*. Também concentra a validação dos dados da entidade.
- **Controller** (`ControladorCaminhao`): lê a requisição (JSON do corpo, query string), chama o Models, monta a resposta em JSON com o código HTTP correto. Não tem SQL.
- **Routes/rotas.php**: interpreta a URI e o método HTTP e despacha para o método certo do Controlador.
- **public/index.php**: ponto de entrada único — carrega as classes e delega para as rotas.

## Instalação

```bash
git clone <url-do-repositorio>
cd CatalogoCaminhoes
composer install
```

> `composer install` é opcional para rodar a API (ela não tem dependências obrigatórias em produção). Só é necessária se você quiser usar o `zircote/swagger-php` para regenerar o `openapi.json` a partir das anotações em `Swagger/openapi.php`.

## Configuração do banco

1. Crie o banco e a tabela executando o script:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   Isso cria o banco `catalogo_caminhoes`, a tabela `caminhoes` e 8 registros de exemplo.

## Configuração do .env

1. Copie o arquivo de exemplo:
   ```bash
   cp .env.example .env
   ```
2. Ajuste as credenciais em `.env` conforme seu ambiente:
   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=catalogo_caminhoes
   DB_USER=root
   DB_PASS=
   APP_ENV=local
   ```

O `.env` nunca deve ser versionado (já está no `.gitignore`).

## Como executar

### Com o servidor embutido do PHP

```bash
php -S localhost:8000 -t public
```

A API ficará disponível em `http://localhost:8000/api/caminhoes`.

### Com Laravel Herd

1. Abra o Herd e aponte um site para a pasta do projeto.
2. Configure o **document root** do site para a pasta `public/`.
3. Acesse `http://catalogo-caminhoes.test/api/caminhoes` (ou o domínio que o Herd atribuir).

## Endpoints

| Método | Rota | Descrição |
|---|---|---|
| GET | `/api/caminhoes` | Lista todos os caminhões |
| GET | `/api/caminhoes?status=disponivel` | Filtra caminhões por status |
| GET | `/api/caminhoes?marca=Volvo` | Filtra caminhões por marca (busca parcial) |
| GET | `/api/caminhoes/{id}` | Busca um caminhão por ID |
| POST | `/api/caminhoes` | Cadastra um caminhão |
| PUT | `/api/caminhoes/{id}` | Atualiza um caminhão (parcial) |
| DELETE | `/api/caminhoes/{id}` | Exclui um caminhão |

Tipos permitidos: `trator`, `rigido`, `basculante`, `cegonha`, `tanque`, `bau`, `outro`.

Status permitidos: `disponivel`, `vendido`, `reservado`, `em_negociacao`.

Campos obrigatórios no `POST`: `marca`, `modelo`, `ano`, `tipo`, `cor`, `placa`, `chassi`, `valor`.

## Exemplos de JSON

**POST /api/caminhoes**

```json
{
  "marca": "Volvo",
  "modelo": "FH 540",
  "ano": 2023,
  "tipo": "trator",
  "cor": "Branco",
  "placa": "ABC1D23",
  "chassi": "9BVHS30X0PC123456",
  "quilometragem": 0,
  "valor": 650000.00,
  "status": "disponivel",
  "observacoes": "Caminhão zero km, garantia de fábrica"
}
```

**Resposta (201 Created)**

```json
{
  "id": 9,
  "marca": "Volvo",
  "modelo": "FH 540",
  "ano": 2023,
  "tipo": "trator",
  "cor": "Branco",
  "placa": "ABC1D23",
  "chassi": "9BVHS30X0PC123456",
  "quilometragem": 0,
  "valor": 650000,
  "status": "disponivel",
  "observacoes": "Caminhão zero km, garantia de fábrica",
  "created_at": "2026-09-15 10:30:00",
  "updated_at": "2026-09-15 10:30:00"
}
```

**PUT /api/caminhoes/9** (atualização parcial)

```json
{
  "status": "vendido",
  "valor": 630000.00,
  "observacoes": "Vendido para transportadora XYZ"
}
```

**Erro de validação (422)**

```json
{
  "erro": "Dados inválidos.",
  "detalhes": [
    "O campo 'marca' é obrigatório.",
    "O campo 'placa' deve estar no formato Mercosul (ex: ABC1D23)."
  ]
}
```

## Como testar no Insomnia

1. Abra o Insomnia e importe a coleção pronta em `insomnia/CatalogoCaminhoes-insomnia.json` (*Import* > *From File*). Ela já traz as 6 requisições da API com a variável `base_url` (`http://localhost:8000` — ajuste para o domínio do Herd se necessário).
2. Ou crie manualmente uma nova coleção "Catálogo de Caminhões" com uma requisição para cada endpoint da tabela acima.
3. Para `POST` e `PUT`, defina o *Body* como `JSON` e cole os exemplos acima.
4. Confira os códigos de status retornados: `200`, `201`, `204`, `400`, `404`, `422` e `405`.

## Documentação Swagger

- **Já pronta:** abra `public/docs.html` no navegador (ex: `http://localhost:8000/docs.html`). Ele carrega o `public/openapi.json` em uma interface Swagger UI.
- **Para regenerar a partir das anotações** em `Swagger/openapi.php`:
  ```bash
  composer require zircote/swagger-php
   vendor/bin/openapi Swagger -o public/openapi.json
  ```
