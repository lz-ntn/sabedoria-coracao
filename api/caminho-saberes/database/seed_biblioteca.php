<?php
/**
 * Seed da Biblioteca do Caminho Saberes
 * Insere artigos de exemplo (tipo='artigo') distribuídos nas categorias existentes.
 *
 * Uso: php database/seed_biblioteca.php
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();

echo "=== Seed da Biblioteca (Caminho Saberes) ===\n";

$admin = $db->fetch("SELECT id FROM usuarios ORDER BY id LIMIT 1");
$autorId = $admin ? (int) $admin['id'] : null;

echo "Autor padrão para artigos: usuario_id = " . var_export($autorId, true) . "\n\n";

$categorias = $db->select('SELECT id, slug FROM categorias ORDER BY id');
$mapCat = [];
foreach ($categorias as $c) {
    $mapCat[$c['slug']] = (int) $c['id'];
}

$artigos = [
    [
        'categoria' => 'gnose',
        'titulo'    => 'O que é Gnose: Conhecimento que Transforma',
        'slug'      => 'artigo-gnose-conhecimento',
        'resumo'    => 'A gnose não é crença, é experiência direta. Um mergulho no significado prático do autoconhecimento.',
        'tags'      => 'gnose, autoconhecimento, experiencia',
        'nivel'     => 'iniciante',
        'duracao'   => 20,
        'conteudo'  => 'A palavra gnose vem do grego e significa conhecimento. Não um conhecimento intelectual, decorado, mas um conhecimento vivo, direto e experiencial — o saber que nasce de dentro.\n\nNo caminho gnóstico, conhecer a si mesmo é o primeiro passo. "Conhece-te a ti mesmo" não é um conselho moral, mas uma técnica: observar os próprios pensamentos, emoções e reações sem julgamento.\n\nQuando você observa, você deixa de ser vítima do automatismo. Começa a perceber o que é o eu verdadeiro e o que é apenas condicionamento. Essa percepção é o início da libertação. Tudo o mais é estudo — importante, mas secundário.',
        'fonte'     => 'Sabedoria de Coração (redação)',
    ],
    [
        'categoria' => 'gnose',
        'titulo'    => 'Pleroma: A Plenitude que Habita em Nós',
        'slug'      => 'artigo-pleroma-plenitude',
        'resumo'    => 'O conceito gnóstico do Pleroma e como ele se relaciona com a experiência de completude interior.',
        'tags'      => 'gnose, pleroma, plenitude',
        'nivel'     => 'avancado',
        'duracao'   => 30,
        'conteudo'  => 'No gnosticismo, Pleroma é o mundo da plenitude divina — o conjunto das realidades espirituais perfeitas. Em contraste, o mundo material é visto como uma cópia imperfeita, criada por um demiurgo ignorante.\n\nLonge de ser um convite à fuga do mundo, o mito do Pleroma nos fala de um estado interior que pode ser reconquistado. A centelha divina que carregamos é um fragmento dessa plenitude, esquecida em meio ao condicionamento.\n\nO trabalho interior — meditação, autoobservação, prática — é uma jornada de retorno ao Pleroma que já existe dentro de cada um. Não se trata de ir para outro lugar, mas de lembrar quem se é. É isso que separa a gnose de uma simples filosofia de fuga: ela é um caminho de reapropriação da própria essência.',
        'fonte'     => 'Sabedoria de Coração (redação)',
    ],
    [
        'categoria' => 'epigenetica',
        'titulo'    => 'Você Não é Vítima dos Seus Genes',
        'slug'      => 'artigo-epigenetica-voce-nao-e-vitima',
        'resumo'    => 'A epigenética mostra que hábitos e emoções modulam a expressão genética. Conheça o impacto prático disso.',
        'tags'      => 'epigenetica, habitos, ciencia',
        'nivel'     => 'iniciante',
        'duracao'   => 25,
        'conteudo'  => 'Durante décadas acreditamos que os genes eram um destino fixo. A epigenética mudou essa história: a sequência do DNA não muda, mas a atividade dos genes pode ser ligada e desligada por metilação, modificação de histonas e microRNAs.\n\nO que ativa esses interruptores? Alimentação, sono, exercício, estresse e — o mais surpreendente — as emoções e os estados mentais cultivados ao longo do tempo.\n\nIsso não significa milagres: a genética continua sendo o ponto de partida. Mas significa que você tem mais voz ativa do que imaginava. A meditação, por exemplo, já demonstrou reduzir a expressão de genes pró-inflamatórios e ativar genes associados à longevidade.\n\nA ciência moderna confirma o que as tradições ancestrais sempre disseram: a mente e o coração moldam o corpo. A direção da mudança começa hoje, com escolhas conscientes.',
        'fonte'     => 'Sabedoria de Coração (redação)',
    ],
    [
        'categoria' => 'epigenetica',
        'titulo'    => 'Meditação e Expressão Genética',
        'slug'      => 'artigo-epigenetica-meditacao',
        'resumo'    => 'Estudos recentes mostram como a prática meditativa regular altera a expressão de genes ligados à inflamação e à longevidade.',
        'tags'      => 'epigenetica, meditacao, longevidade',
        'nivel'     => 'intermediario',
        'duracao'   => 35,
        'conteudo'  => 'Em estudos conduzidos na última década, praticantes de meditação apresentaram mudanças mensuráveis na expressão genética após poucas semanas de prática diária.\n\nOs efeitos observados incluem a redução da atividade de genes pró-inflamatórios (como os da via NF-kB) e o aumento na expressão de genes ligados à proteção celular e ao reparo do DNA.\n\nIsso sugere um mecanismo concreto: o estado de repouso profundo induzido pela meditação sinaliza ao organismo que o ambiente é seguro, permitindo que recursos sejam direcionados da resposta ao estresse para a regeneração.\n\nMelhor ainda, tais efeitos não exigem anos de retiro: sessões de 15 a 30 minutos por dia, de forma consistente, já geram assinaturas genéticas favoráveis. O corpo inteiro escuta — a pergunta é: o que você está dizendo a ele todos os dias?',
        'fonte'     => 'Sabedoria de Coração (redação)',
    ],
    [
        'categoria' => 'hermetismo',
        'titulo'    => 'Os 7 Princípios Herméticos na Vida Prática',
        'slug'      => 'artigo-hermetismo-7-principios',
        'resumo'    => 'Mentalismo, Correspondência, Vibração e os demais princípios aplicados a decisões e relações do cotidiano.',
        'tags'      => 'hermetismo, principios, pratica',
        'nivel'     => 'iniciante',
        'duracao'   => 40,
        'conteudo'  => 'Atribuído a Hermes Trismegisto, o texto "O Caibalion" apresenta sete princípios que descrevem a estrutura do real. Mais do que metafísica, são ferramentas de leitura da vida.\n\nMentalismo: "o tudo é mente" — seus estados internos influenciam aquilo que você percebe e cria. Correspondência: "o que está em cima é como o que está embaixo" — os padrões se repetem em todas as escalas, do micro ao macro. Vibração: nada está parado; tudo oscila. Polaridade: tudo é dual e os opostos são extremos do mesmo. Ritmo: tudo flui e reflui. Causa e Efeito: toda ação tem consequência. Gênero: toda criação envolve princípios recebidor e emissor.\n\nNa prática, esses princípios oferecem um vocabulário: em um conflito, pergunte onde está a polaridade; antes de uma decisão, considere causa e efeito; ao enfrentar dificuldade, lembre do ritmo. Não são regras mágicas — são lentes. E trocar de lente muda a forma como você caminha.',
        'fonte'     => 'Sabedoria de Coração (redação)',
    ],
    [
        'categoria' => 'kundalini',
        'titulo'    => 'Kundalini: A Energia que Desperta a Coluna',
        'slug'      => 'artigo-kundalini-energia-coluna',
        'resumo'    => 'A serpente de fogo das tradições tântricas: o que é, onde mora e por que seu despertar pede preparo e prudência.',
        'tags'      => 'kundalini, chakras, energia',
        'nivel'     => 'avancado',
        'duracao'   => 45,
        'conteudo'  => 'Kundalini é descrita nas tradições tântricas como uma energia espiritual latente, figurativamente adormecida na base da coluna vertebral, como uma serpente enrolada.\n\nO despertar dessa energia costuma ser narrado como uma ascensão pelo canal central (Sushumna), atravessando os centros de energia (chakras) até o topo da cabeça.\n\nÉ importante um aviso: o despertar espontâneo pode ser intenso e desorientador. Por isso, as tradições enfatizam a preparação — ética, corpo purificado, mente estabilizada — e a orientação de alguém experiente.\n\nA prática segura não busca "forçar" fenômenos, mas remover obstáculos: postura, respiração consciente, purificação de hábitos. Quando o canal está limpo, a energia flui por si. O caminho é mais sobre preparar o leito do rio do que empurrar a água.',
        'fonte'     => 'Sabedoria de Coração (redação)',
    ],
    [
        'categoria' => 'teosofia',
        'titulo'    => 'Teosofia: A Sabedoria Divina em Síntese',
        'slug'      => 'artigo-teosofia-sabedoria-divina',
        'resumo'    => 'Sociedade Teosófica, Helena Blavatsky e a busca pela sabedoria comum que une todas as tradições espirituais.',
        'tags'      => 'teosofia, blavatsky, sintese',
        'nivel'     => 'intermediario',
        'duracao'   => 35,
        'conteudo'  => 'Teosofia significa, literalmente, "sabedoria divina" (theos = Deus, sophia = sabedoria). Como movimento, surge no final do século XIX com a Sociedade Teosófica, fundada por Helena Blavatsky, Henry Olcott e William Judge.\\n\\nSua proposta central é audaciosa: por trás da diversidade de religiões e filosofias, existiria um corpo comum de verdades espirituais — a mesma sabedoria reexpressa em diferentes linguagens e épocas.\\n\\nEntre os temas clássicos estão a unidade fundamental de toda a vida, a reencarnação como processo educativo e o karma como lei de equilíbrio — não como castigo, mas como consequência.\\n\\nPara além das doutrinas específicas, o legado mais valioso da teosofia é uma postura: estudar comparativamente, manter a mente aberta e, acima de tudo, testar as ideias na própria experiência. Sabedoria que não se verifica na vida vira apenas erudição.',
        'fonte'     => 'Sabedoria de Coração (redação)',
    ],
    [
        'categoria' => 'coracao',
        'titulo'    => 'O Coração que Pensa: Cardioneurociências',
        'slug'      => 'artigo-coracao-que-pensa',
        'resumo'    => 'Um pequeno cérebro no coração: a rede neural cardíaca, a comunicação com o cérebro e o estado de coerência.',
        'tags'      => 'coracao, coerencia, neurociencia',
        'nivel'     => 'intermediario',
        'duracao'   => 30,
        'conteudo'  => 'O coração é muito mais do que uma bomba. Ele possui uma rede neural própria, com cerca de 40 mil neurônios, capaz de processar informações e tomar decisões de forma parcialmente independente do cérebro.\\n\\nAlém disso, o coração se comunica com o sistema nervoso central por vias aferentes — e, em certas frequências, essa comunicação é mais rápida que a percepção consciente. È o chamado "cérebro cardíaco".\\n\\nQuando respiramos de forma lenta e sustentamos emoções como gratidão e apreço, o ritmo cardíaco exibe um padrão coerente e suave. Estudos do HeartMath Institute associam esse estado a melhor clareza mental, regulação emocional e até coleta de informações intuitivas.\\n\\nA boa notícia: a coerência pode ser treinada em poucos minutos por dia. Respirar contando seis segundos na inspiração e seis na expiração, mantendo a atenção no coração, é um ponto de partida simples e poderoso.',
        'fonte'     => 'Sabedoria de Coração (redação)',
    ],
];

$importados = 0;
$pulados = 0;
$erros = 0;

foreach ($artigos as $a) {
    $categoriaId = $mapCat[$a['categoria']] ?? $mapCat['gnose'] ?? 1;

    $existente = $db->fetch('SELECT id FROM licoes WHERE slug = ?', [$a['slug']]);
    if ($existente) {
        echo "⊘ Pulado (já existe): {$a['titulo']}\n";
        $pulados++;
        continue;
    }

    try {
        $novoId = $db->insert('licoes', [
            'categoria_id' => $categoriaId,
            'titulo'       => $a['titulo'],
            'slug'         => $a['slug'],
            'resumo'       => $a['resumo'],
            'conteudo'     => $a['conteudo'],
            'tags'         => $a['tags'],
            'fonte'        => $a['fonte'],
            'nivel'        => $a['nivel'],
            'duracao_min'  => $a['duracao'],
            'ordem'        => 999,
            'tipo'         => 'artigo',
            'autor_id'     => $autorId,
            'publicado_em' => date('Y-m-d H:i:s'),
            'criada_em'    => date('Y-m-d H:i:s'),
        ]);
        echo "✓ Importado: {$a['titulo']} (ID: $novoId, Cat: $categoriaId)\n";
        $importados++;
    } catch (Exception $e) {
        echo "✗ Erro ao importar '{$a['titulo']}': " . $e->getMessage() . "\n";
        $erros++;
    }
}

echo "\n=== Resumo ===\n";
echo "Importados: $importados\n";
echo "Pulados (já existiam): $pulados\n";
echo "Erros: $erros\n";