<?php

/**
 * Funções auxiliares para o sistema de controle financeiro
 */

/**
 * Função para sanitização de strings
 * 
 * @param string $str String a ser sanitizada
 * @return string String sanitizada
 */
function sanitizarString($str)
{
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida uma data no formato Y-m-d
 * 
 * @param string $data Data no formato Y-m-d
 * @return bool Verdadeiro se a data é válida, falso caso contrário
 */
function validarData($data)
{
    $formato = 'Y-m-d';
    $dataObj = DateTime::createFromFormat($formato, $data);
    return $dataObj && $dataObj->format($formato) === $data;
}

/**
 * Formata um valor para o formato monetário brasileiro
 * 
 * @param float $valor Valor a ser formatado
 * @return string Valor formatado
 */
function formatarMoeda($valor)
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Formata uma data do banco (Y-m-d) para o formato brasileiro (d/m/Y)
 * 
 * @param string $data Data no formato Y-m-d
 * @return string Data no formato d/m/Y
 */
function formatarData($data)
{
    if (empty($data)) return '';
    $dataObj = DateTime::createFromFormat('Y-m-d', $data);
    if (!$dataObj) return $data; // Retorna original se falhar
    return $dataObj->format('d/m/Y');
}

/**
 * Verifica se um valor é um número flutuante válido
 * 
 * @param mixed $valor Valor a ser validado
 * @return bool Verdadeiro se é um número válido
 */
function validarNumeroDecimal($valor)
{
    return is_numeric($valor) && $valor >= 0;
}

/**
 * Gera resposta de erro em JSON
 * 
 * @param string $mensagem Mensagem de erro
 * @param int $codigo Código HTTP
 * @return void
 */
function respostaErro($mensagem, $codigo = 400)
{
    header('Content-Type: application/json');
    http_response_code($codigo);
    echo json_encode(['error' => $mensagem]);
    exit;
}

/**
 * Gera resposta de sucesso em JSON
 * 
 * @param array $dados Dados a serem retornados
 * @param string $mensagem Mensagem de sucesso
 * @return void
 */
function respostaSucesso($dados = [], $mensagem = 'Operação realizada com sucesso')
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $mensagem,
        'data' => $dados
    ]);
    exit;
}

/**
 * Calcula o total de receitas de uma lista de movimentações
 * 
 * @param array $movimentacoes Lista de movimentações
 * @return float Total de receitas
 */
function totalReceitas($movimentacoes)
{
    $total = 0;
    foreach ($movimentacoes as $mov) {
        if ($mov['tipo'] === 'Receitas') {
            $total += floatval($mov['valor']);
        }
    }
    return $total;
}

/**
 * Calcula o total de despesas de uma lista de movimentações
 * 
 * @param array $movimentacoes Lista de movimentações
 * @return float Total de despesas
 */
function totalDespesas($movimentacoes)
{
    $total = 0;
    foreach ($movimentacoes as $mov) {
        if ($mov['tipo'] !== 'Receitas') {
            $total += floatval($mov['valor']);
        }
    }
    return $total;
}

/**
 * Calcula o saldo (receitas - despesas)
 * 
 * @param array $movimentacoes Lista de movimentações
 * @return float Saldo
 */
function calcularSaldo($movimentacoes)
{
    return totalReceitas($movimentacoes) - totalDespesas($movimentacoes);
}
