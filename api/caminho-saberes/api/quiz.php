<?php
/**
 * API de Quiz
 * 
 * GET  /api/quiz.php?categoria=gnose     - Buscar perguntas
 * POST /api/quiz.php                      - Salvar resultado
 */

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../config/app.php';

$db = Database::getInstance();
$usuario_id = obter_usuario_id($db);
$method = $_SERVER['REQUEST_METHOD'];

// ══════════════════════════════════════════
// Banco de Perguntas
// ══════════════════════════════════════════
function get_perguntas($categoria = null) {
    $todas = [
        // Gerais
        [
            'categoria' => 'geral',
            'pergunta' => 'O que significa Gnose?',
            'opcoes' => ['Crença religiosa', 'Conhecimento direto e experiencial', 'Ritual sagrado', 'Livro antigo'],
            'correta' => 1
        ],
        [
            'categoria' => 'geral',
            'pergunta' => 'Qual filósofo grego influenciou o pensamento hermético?',
            'opcoes' => ['Sócrates', 'Platão', 'Aristóteles', 'Pitágoras'],
            'correta' => 1
        ],
        [
            'categoria' => 'geral',
            'pergunta' => 'O que é a Tábua de Esmeralda?',
            'opcoes' => ['Um mineral precioso', 'Um texto hermético antigo', 'Um tipo de meditação', 'Um chakra'],
            'correta' => 1
        ],
        [
            'categoria' => 'geral',
            'pergunta' => 'Quantos princípios herméticos existem?',
            'opcoes' => ['5', '7', '12', '3'],
            'correta' => 1
        ],
        [
            'categoria' => 'geral',
            'pergunta' => 'O que é a Epigenética?',
            'opcoes' => [
                'Mudanças na sequência do DNA',
                'Mudanças na atividade dos genes sem alterar o DNA',
                'Um tipo de gene',
                'Uma proteína'
            ],
            'correta' => 1
        ],
        [
            'categoria' => 'geral',
            'pergunta' => 'O que é a energia Kundalini?',
            'opcoes' => ['Um mantra', 'Energia espiritual na base da coluna', 'Um chakra', 'Uma dieta'],
            'correta' => 1
        ],
        [
            'categoria' => 'geral',
            'pergunta' => 'Quem fundou a Sociedade Teosófica?',
            'opcoes' => ['Helena Blavatsky', 'Carl Jung', 'Alan Watts', 'Sri Aurobindo'],
            'correta' => 0
        ],
        [
            'categoria' => 'geral',
            'pergunta' => 'Quantas vezes maior é o campo eletromagnético do coração comparado ao cérebro?',
            'opcoes' => ['10x', '30x', '60x', '100x'],
            'correta' => 2
        ],
        [
            'categoria' => 'geral',
            'pergunta' => 'O que significa "Como acima, assim abaixo"?',
            'opcoes' => [
                'Lei da Gravidade',
                'Lei da Correspondência',
                'Lei do Karma',
                'Lei da Atração'
            ],
            'correta' => 1
        ],
        [
            'categoria' => 'geral',
            'pergunta' => 'Qual dos seguintes NÃO é um mecanismo epigenético?',
            'opcoes' => ['Metilação do DNA', 'Modificação de histonas', 'MicroRNA', 'Transcrição reversa'],
            'correta' => 3
        ],
        // Gnose
        [
            'categoria' => 'gnose',
            'pergunta' => 'O que é o Plerooma na tradição gnóstica?',
            'opcoes' => ['Um livro sagrado', 'O mundo da plenitude divina', 'Um demônio', 'Um ritual'],
            'correta' => 1
        ],
        [
            'categoria' => 'gnose',
            'pergunta' => 'O que é o Demiurgo no Gnosticismo?',
            'opcoes' => [
                'O Deus supremo',
                'A inteligência criadora imperfeita',
                'Um profeta',
                'Um anjo'
            ],
            'correta' => 1
        ],
        // Epigenética
        [
            'categoria' => 'epigenetica',
            'pergunta' => 'Qual hábito tem impacto epigenético positivo?',
            'opcoes' => ['Sedentarismo', 'Meditação', 'Tabagismo', 'Privação de sono'],
            'correta' => 1
        ],
        [
            'categoria' => 'epigenetica',
            'pergunta' => 'O que a metilação do DNA faz?',
            'opcoes' => ['Ativa genes', 'Desliga genes', 'Cria novos genes', 'Remove genes'],
            'correta' => 1
        ],
        // Hermetismo
        [
            'categoria' => 'hermetismo',
            'pergunta' => 'Qual o primeiro princípio hermético?',
            'opcoes' => ['Correspondência', 'Mentalismo', 'Vibração', 'Causa e Efeito'],
            'correta' => 1
        ],
        [
            'categoria' => 'hermetismo',
            'pergunta' => 'O princípio de Polaridade afirma que:',
            'opcoes' => [
                'Tudo é dual',
                'Tudo é um',
                'Tudo vibra',
                'Tudo tem causa'
            ],
            'correta' => 0
        ],
        // Kundalini
        [
            'categoria' => 'kundalini',
            'pergunta' => 'Quantas vértebras tem o canal Sushumna?',
            'opcoes' => ['22', '33', '44', '12'],
            'correta' => 1
        ],
        // Coração
        [
            'categoria' => 'coracao',
            'pergunta' => 'O que é Coerência Cardíaca?',
            'opcoes' => [
                'Batimento cardíaco regular',
                'Estado de harmonia entre coração, mente e emoção',
                'Exercício físico',
                'Pressão arterial normal'
            ],
            'correta' => 1
        ],
        [
            'categoria' => 'coracao',
            'pergunta' => 'Qual emoção está associada à coerência cardíaca?',
            'opcoes' => ['Raiva', 'Medo', 'Gratidão', 'Ansiedade'],
            'correta' => 2
        ]
    ];

    if ($categoria && $categoria !== 'geral') {
        return array_values(array_filter($todas, function($p) use ($categoria) {
            return $p['categoria'] === $categoria;
        }));
    }

    return $todas;
}

// ══════════════════════════════════════════
// GET - Buscar perguntas
// ══════════════════════════════════════════
if ($method === 'GET') {
    $categoria = $_GET['categoria'] ?? null;
    $perguntas = get_perguntas($categoria);

    // Embaralhar opções para não ficar previsível
    foreach ($perguntas as &$p) {
        $opcoes = $p['opcoes'];
        $correta = $p['correta'];
        $respostaCorreta = $opcoes[$correta];

        shuffle($opcoes);
        $novaCorreta = array_search($respostaCorreta, $opcoes);

        $p['opcoes'] = $opcoes;
        $p['correta'] = $novaCorreta;
    }
    unset($p);

    json_response([
        'total' => count($perguntas),
        'perguntas' => $perguntas
    ]);
}

// ══════════════════════════════════════════
// POST - Salvar resultado do quiz
// ══════════════════════════════════════════
if ($method === 'POST') {
    validar_csrf_api();
    $data = ler_corpo();

    $erro = validar_campos(['acertos', 'total', 'pontuacao'], $data);
    if ($erro) {
        json_error($erro);
    }

    $db->insert('quiz_resultados', [
        'usuario_id' => $usuario_id,
        'categoria'  => $data['categoria'] ?? null,
        'acertos'    => (int) $data['acertos'],
        'total'      => (int) $data['total'],
        'pontuacao'  => (int) $data['pontuacao'],
        'respostas'  => isset($data['respostas']) ? json_encode($data['respostas'], JSON_UNESCAPED_UNICODE) : null,
        'realizado_em' => date('Y-m-d H:i:s')
    ]);

    $percentual = $data['total'] > 0
        ? round(($data['acertos'] / $data['total']) * 100)
        : 0;

    json_response([
        'success' => true,
        'message' => 'Resultado salvo!',
        'percentual' => $percentual
    ]);
}

if ($method === 'OPTIONS') {
    json_response([]);
}

json_error('Método não permitido.', 405);
