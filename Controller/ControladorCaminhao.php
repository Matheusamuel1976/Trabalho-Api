<?php

// Recebe o HTTP, valida, chama o modelo e responde em JSON.

class ControladorCaminhao
{
    public static function listar(): void
    {
        $status = $_GET['status'] ?? null;
        $marca = $_GET['marca'] ?? null;

        if ($status !== null && !in_array($status, Caminhao::STATUS_VALIDOS, true)) {
            self::responderErro(422, "Status inválido. Valores permitidos: " . implode(', ', Caminhao::STATUS_VALIDOS) . '.');
            return;
        }

        $caminhoes = Caminhao::listarTodos($status, $marca);
        self::responderJson(200, $caminhoes);
    }

    public static function buscar(int $id): void
    {
        $caminhao = Caminhao::buscarPorId($id);

        if (!$caminhao) {
            self::responderErro(404, 'Caminhão não encontrado.');
            return;
        }

        self::responderJson(200, $caminhao);
    }

    public static function criar(): void
    {
        $dados = self::lerCorpoJson();

        if ($dados === null) {
            self::responderErro(400, 'Corpo da requisição inválido. Envie um JSON válido.');
            return;
        }

        $erros = Caminhao::validar($dados);

        if (!empty($erros)) {
            self::responderErro(422, 'Dados inválidos.', $erros);
            return;
        }

        if (Caminhao::buscarPorPlaca($dados['placa'])) {
            self::responderErro(422, 'Já existe um caminhão cadastrado com esta placa.');
            return;
        }

        if (Caminhao::buscarPorPlaca($dados['chassi'])) {
            self::responderErro(422, 'Já existe um caminhão cadastrado com este chassi.');
            return;
        }

        $id = Caminhao::criar($dados);
        $caminhao = Caminhao::buscarPorId($id);

        self::responderJson(201, $caminhao);
    }

    public static function atualizar(int $id): void
    {
        $caminhaoExistente = Caminhao::buscarPorId($id);

        if (!$caminhaoExistente) {
            self::responderErro(404, 'Caminhão não encontrado.');
            return;
        }

        $dados = self::lerCorpoJson();

        if ($dados === null) {
            self::responderErro(400, 'Corpo da requisição inválido. Envie um JSON válido.');
            return;
        }

        $erros = Caminhao::validar($dados, parcial: true);

        if (!empty($erros)) {
            self::responderErro(422, 'Dados inválidos.', $erros);
            return;
        }

        if (isset($dados['placa'])) {
            $outro = Caminhao::buscarPorPlaca($dados['placa']);
            if ($outro && $outro['id'] !== $id) {
                self::responderErro(422, 'Já existe outro caminhão cadastrado com esta placa.');
                return;
            }
        }

        if (isset($dados['chassi'])) {
            $outro = Caminhao::buscarPorPlaca($dados['chassi']);
            if ($outro && $outro['id'] !== $id) {
                self::responderErro(422, 'Já existe outro caminhão cadastrado com este chassi.');
                return;
            }
        }

        if (!Caminhao::atualizar($id, $dados)) {
            self::responderErro(422, 'Nenhum campo para atualizar. Envie ao menos um campo válido.');
            return;
        }

        $caminhaoAtualizado = Caminhao::buscarPorId($id);

        self::responderJson(200, $caminhaoAtualizado);
    }

    public static function excluir(int $id): void
    {
        $caminhao = Caminhao::buscarPorId($id);

        if (!$caminhao) {
            self::responderErro(404, 'Caminhão não encontrado.');
            return;
        }

        Caminhao::excluir($id);
        self::responderJson(204, null);
    }

    private static function lerCorpoJson(): ?array
    {
        $texto = file_get_contents('php://input');

        if (trim($texto) === '') {
            return [];
        }

        $dados = json_decode($texto, true);

        return is_array($dados) ? $dados : null;
    }

    private static function responderJson(int $codigoHttp, mixed $dados): void
    {
        http_response_code($codigoHttp);
        header('Content-Type: application/json; charset=utf-8');

        if ($dados !== null) {
            echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }

    private static function responderErro(int $codigoHttp, string $mensagem, array $detalhes = []): void
    {
        $corpo = ['erro' => $mensagem];

        if (!empty($detalhes)) {
            $corpo['detalhes'] = $detalhes;
        }

        self::responderJson($codigoHttp, $corpo);
    }
}