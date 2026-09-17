<?php

// Tudo que envolve caminhão: validação e o SQL da tabela.

class Caminhao
{
    public const TIPOS_VALIDOS = [
        'trator',
        'rigido',
        'basculante',
        'cegonha',
        'tanque',
        'bau',
        'outro',
    ];

    public const STATUS_VALIDOS = [
        'disponivel',
        'vendido',
        'reservado',
        'em_negociacao',
    ];

    // Devolve a lista de erros (vazia = válido). Com $parcial, campo ausente não é erro (PUT).
    public static function validar(array $dados, bool $parcial = false): array
    {
        $erros = [];

        $obrigatorios = ['marca', 'modelo', 'ano', 'tipo', 'cor', 'placa', 'chassi', 'valor'];

        foreach ($obrigatorios as $campo) {
            $presente = array_key_exists($campo, $dados) && trim((string) $dados[$campo]) !== '';

            if (!$presente && !$parcial) {
                $erros[] = "O campo '{$campo}' é obrigatório.";
            }
        }

        if (isset($dados['marca']) && mb_strlen((string) $dados['marca']) > 100) {
            $erros[] = "O campo 'marca' deve ter no máximo 100 caracteres.";
        }

        if (isset($dados['modelo']) && mb_strlen((string) $dados['modelo']) > 100) {
            $erros[] = "O campo 'modelo' deve ter no máximo 100 caracteres.";
        }

        if (isset($dados['ano'])) {
            $ano = (int) $dados['ano'];
            $anoAtual = (int) date('Y');
            if ($ano < 1950 || $ano > $anoAtual + 1) {
                $erros[] = "O campo 'ano' deve estar entre 1950 e " . ($anoAtual + 1) . ".";
            }
        }

        if (isset($dados['tipo']) && !in_array($dados['tipo'], self::TIPOS_VALIDOS, true)) {
            $erros[] = "O campo 'tipo' é inválido. Valores permitidos: " . implode(', ', self::TIPOS_VALIDOS) . '.';
        }

        if (isset($dados['cor']) && mb_strlen((string) $dados['cor']) > 50) {
            $erros[] = "O campo 'cor' deve ter no máximo 50 caracteres.";
        }

        if (isset($dados['placa'])) {
            $placa = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $dados['placa']));
            if (!preg_match('/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/', $placa)) {
                $erros[] = "O campo 'placa' deve estar no formato Mercosul (ex: ABC1D23).";
            }
        }

        if (isset($dados['chassi'])) {
            $chassi = strtoupper((string) $dados['chassi']);
            if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $chassi)) {
                $erros[] = "O campo 'chassi' deve ter 17 caracteres alfanuméricos (padrão VIN).";
            }
        }

        if (isset($dados['quilometragem']) && $dados['quilometragem'] !== null && $dados['quilometragem'] !== '' && !is_numeric($dados['quilometragem'])) {
            $erros[] = "O campo 'quilometragem' deve ser numérico.";
        }

        if (isset($dados['valor']) && $dados['valor'] !== null && $dados['valor'] !== '' && !is_numeric($dados['valor'])) {
            $erros[] = "O campo 'valor' deve ser numérico.";
        }

        if (isset($dados['status']) && !in_array($dados['status'], self::STATUS_VALIDOS, true)) {
            $erros[] = "O campo 'status' é inválido. Valores permitidos: " . implode(', ', self::STATUS_VALIDOS) . '.';
        }

        return $erros;
    }

    public static function listarTodos(?string $status = null, ?string $marca = null): array
    {
        $banco = Conexao::conectar();

        $sql = 'SELECT * FROM caminhoes';
        $parametros = [];
        $condicoes = [];

        if (!empty($status) && in_array($status, self::STATUS_VALIDOS, true)) {
            $condicoes[] = 'status = :status';
            $parametros['status'] = $status;
        }

        if (!empty($marca)) {
            $condicoes[] = 'marca LIKE :marca';
            $parametros['marca'] = '%' . $marca . '%';
        }

        if (!empty($condicoes)) {
            $sql .= ' WHERE ' . implode(' AND ', $condicoes);
        }

        $sql .= ' ORDER BY created_at DESC';

        $consulta = $banco->prepare($sql);
        $consulta->execute($parametros);

        return $consulta->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $banco = Conexao::conectar();
        $consulta = $banco->prepare('SELECT * FROM caminhoes WHERE id = :id LIMIT 1');
        $consulta->execute(['id' => $id]);
        $caminhao = $consulta->fetch();

        return $caminhao ?: null;
    }

    public static function buscarPorPlaca(string $placa): ?array
    {
        $banco = Conexao::conectar();
        $consulta = $banco->prepare('SELECT * FROM caminhoes WHERE placa = :placa LIMIT 1');
        $consulta->execute(['placa' => strtoupper($placa)]);
        $caminhao = $consulta->fetch();

        return $caminhao ?: null;
    }

    public static function criar(array $dados): int
    {
        $banco = Conexao::conectar();

        $consulta = $banco->prepare(
            'INSERT INTO caminhoes
                (marca, modelo, ano, tipo, cor, placa, chassi, quilometragem, valor, status, observacoes)
             VALUES
                (:marca, :modelo, :ano, :tipo, :cor, :placa, :chassi, :quilometragem, :valor, :status, :observacoes)'
        );

        $consulta->execute([
            'marca' => $dados['marca'],
            'modelo' => $dados['modelo'],
            'ano' => (int) $dados['ano'],
            'tipo' => $dados['tipo'],
            'cor' => $dados['cor'],
            'placa' => strtoupper(preg_replace('/[^A-Z0-9]/', '', $dados['placa'])),
            'chassi' => strtoupper($dados['chassi']),
            'quilometragem' => $dados['quilometragem'] ?? 0,
            'valor' => $dados['valor'],
            'status' => $dados['status'] ?? 'disponivel',
            'observacoes' => $dados['observacoes'] ?? null,
        ]);

        return (int) $banco->lastInsertId();
    }

    // Monta o UPDATE só com os campos que vieram no $dados.
    public static function atualizar(int $id, array $dados): bool
    {
        $campos = [];
        $parametros = ['id' => $id];

        $permitidos = [
            'marca', 'modelo', 'ano', 'tipo', 'cor',
            'placa', 'chassi', 'quilometragem', 'valor', 'status', 'observacoes',
        ];

        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $dados)) {
                $campos[] = "{$campo} = :{$campo}";
                $valor = $dados[$campo];
                if ($campo === 'placa') {
                    $valor = strtoupper(preg_replace('/[^A-Z0-9]/', '', $valor));
                } elseif ($campo === 'chassi') {
                    $valor = strtoupper($valor);
                } elseif ($campo === 'ano') {
                    $valor = (int) $valor;
                }
                $parametros[$campo] = $valor;
            }
        }

        if (empty($campos)) {
            return false;
        }

        $banco = Conexao::conectar();
        $sql = 'UPDATE caminhoes SET ' . implode(', ', $campos) . ' WHERE id = :id';
        $consulta = $banco->prepare($sql);

        return $consulta->execute($parametros);
    }

    public static function excluir(int $id): bool
    {
        $banco = Conexao::conectar();
        $consulta = $banco->prepare('DELETE FROM caminhoes WHERE id = :id');

        return $consulta->execute(['id' => $id]);
    }
}