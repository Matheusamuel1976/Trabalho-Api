<?php

// Descreve a API para a documentação Swagger.
// Para regenerar o openapi.json: vendor/bin/openapi Swagger -o public/openapi.json

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Catálogo de Caminhões',
    version: '1.0.0',
    description: 'API REST para gerenciamento de catálogo de caminhões em uma concessionária.'
)]
#[OA\Schema(
    schema: 'Caminhao',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'marca', type: 'string', example: 'Volvo'),
        new OA\Property(property: 'modelo', type: 'string', example: 'FH 540'),
        new OA\Property(property: 'ano', type: 'integer', example: 2023),
        new OA\Property(
            property: 'tipo',
            type: 'string',
            enum: ['trator', 'rigido', 'basculante', 'cegonha', 'tanque', 'bau', 'outro'],
            example: 'trator'
        ),
        new OA\Property(property: 'cor', type: 'string', example: 'Branco'),
        new OA\Property(property: 'placa', type: 'string', example: 'ABC1D23'),
        new OA\Property(property: 'chassi', type: 'string', example: '9BVHS30X0PC123456'),
        new OA\Property(property: 'quilometragem', type: 'integer', example: 0),
        new OA\Property(property: 'valor', type: 'number', format: 'float', example: 650000.00),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['disponivel', 'vendido', 'reservado', 'em_negociacao'],
            example: 'disponivel'
        ),
        new OA\Property(property: 'observacoes', type: 'string', nullable: true, example: 'Caminhão zero km, garantia de fábrica'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'CaminhaoEntrada',
    type: 'object',
    required: ['marca', 'modelo', 'ano', 'tipo', 'cor', 'placa', 'chassi', 'valor'],
    properties: [
        new OA\Property(property: 'marca', type: 'string', example: 'Volvo'),
        new OA\Property(property: 'modelo', type: 'string', example: 'FH 540'),
        new OA\Property(property: 'ano', type: 'integer', example: 2023),
        new OA\Property(
            property: 'tipo',
            type: 'string',
            enum: ['trator', 'rigido', 'basculante', 'cegonha', 'tanque', 'bau', 'outro'],
            example: 'trator'
        ),
        new OA\Property(property: 'cor', type: 'string', example: 'Branco'),
        new OA\Property(property: 'placa', type: 'string', example: 'ABC1D23'),
        new OA\Property(property: 'chassi', type: 'string', example: '9BVHS30X0PC123456'),
        new OA\Property(property: 'quilometragem', type: 'integer', example: 0),
        new OA\Property(property: 'valor', type: 'number', format: 'float', example: 650000.00),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['disponivel', 'vendido', 'reservado', 'em_negociacao'],
            example: 'disponivel'
        ),
        new OA\Property(property: 'observacoes', type: 'string', example: 'Caminhão zero km, garantia de fábrica'),
    ]
)]
class DocumentacaoApi
{
    #[OA\Get(
        path: '/api/caminhoes',
        summary: 'Lista todos os caminhões do catálogo',
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filtra os caminhões por status',
                schema: new OA\Schema(type: 'string', enum: ['disponivel', 'vendido', 'reservado', 'em_negociacao'])
            ),
            new OA\Parameter(
                name: 'marca',
                in: 'query',
                required: false,
                description: 'Filtra os caminhões por marca (busca parcial)',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de caminhões',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Caminhao'))
            ),
        ]
    )]
    public function listar(): void
    {
    }

    #[OA\Get(
        path: '/api/caminhoes/{id}',
        summary: 'Busca um caminhão pelo ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Caminhão encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Caminhao')),
            new OA\Response(response: 404, description: 'Caminhão não encontrado'),
        ]
    )]
    public function buscar(): void
    {
    }

    #[OA\Post(
        path: '/api/caminhoes',
        summary: 'Cadastra um novo caminhão no catálogo',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CaminhaoEntrada')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Caminhão criado', content: new OA\JsonContent(ref: '#/components/schemas/Caminhao')),
            new OA\Response(response: 422, description: 'Dados inválidos'),
        ]
    )]
    public function criar(): void
    {
    }

    #[OA\Put(
        path: '/api/caminhoes/{id}',
        summary: 'Atualiza um caminhão existente',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CaminhaoEntrada')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Caminhão atualizado', content: new OA\JsonContent(ref: '#/components/schemas/Caminhao')),
            new OA\Response(response: 404, description: 'Caminhão não encontrado'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
        ]
    )]
    public function atualizar(): void
    {
    }

    #[OA\Delete(
        path: '/api/caminhoes/{id}',
        summary: 'Exclui um caminhão do catálogo',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Caminhão excluído'),
            new OA\Response(response: 404, description: 'Caminhão não encontrado'),
        ]
    )]
    public function excluir(): void
    {
    }
}