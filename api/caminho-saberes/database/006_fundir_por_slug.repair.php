<?php
/**
 * Reparação 006 — refaz a fusão 005 usando SLUG, não ID.
 *
 * Motivo: os IDs de produção (30xxx) não são os mesmos do dev (4xx). A 005
 * usou IDs fixos e não encontrou nada em produção, mas ainda assim gravou
 * sua entrada em `migrations`. Esta versão é idempotente e identifica as
 * lições pelo slug determinístico do importador do wiki.
 *
 * Só incorpora informação que faltava; nada é inventado nem duplicado.
 */

$fusoes = [
    'pistis-sophia-drama-alma' =>
        'Gnose é conhecimento direto e experiencial da natureza divina — não crença, mas experiência. '
        . 'Seu mapa inclui os três mundos (Plerooma, Kenoma e Sarak) e os textos da Biblioteca de Nag Hammadi e dos evangelhos gnósticos.',

    'pratica-diaria-rotina' =>
        'A base de toda a prática é a autoobservação: um exercício de observação interna sem julgamento.',

    'epigenetica-pratica-guia' =>
        'Na base, a epigenética estuda mudanças na atividade dos genes sem alterar a sequência do DNA — '
        . 'metilação do DNA, modificação de histonas e microRNA — influenciadas por alimentação, estresse, exercício, sono e conexões sociais.',

    'hermetismo-caibalion-ser-todo' =>
        'A Tábua de Esmeralda, o texto hermético mais antigo e seus ensinamentos, é uma das raízes desta tradição.',

    'kundalini-fogo-espirito-santo' =>
        'O canal Sushumna percorre as 33 vértebras e conduz a ascensão da energia pelos chakras.',

    'teosofia-helena-blavatsky' =>
        'Três princípios sustentam essa visão: reencarnação, karma e a unidade fundamental de toda vida.',

    'coracao-centro-inteligencia' =>
        'O coração gera um campo eletromagnético cerca de 60 vezes maior que o do cérebro; '
        . 'a respiração coerente é a técnica que equilibra o sistema nervoso.',
];

$incorporadas = 0;
foreach ($fusoes as $slug => $texto) {
    $stmt = $pdo->prepare('SELECT id, conteudo FROM licoes WHERE slug = ? AND tipo = ?');
    $stmt->execute([$slug, 'licao']);
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$linha) {
        echo "  Aviso: lição '{$slug}' não encontrada\n";
        continue;
    }
    if (mb_strpos($linha['conteudo'], $texto) !== false) {
        continue;
    }
    $novo = '<p><strong>Em síntese:</strong> ' . $texto . '</p>' . "\n" . $linha['conteudo'];
    $pdo->prepare('UPDATE licoes SET conteudo = ? WHERE id = ?')->execute([$novo, $linha['id']]);
    $incorporadas++;
}
echo "  Informações fundidas em {$incorporadas} lições ricas (por slug)\n";

$sementes = [
    'gnose-o-que-e', 'gnose-tres-mundos', 'gnose-textos', 'gnose-pratica',
    'epigenetica-o-que-e', 'epigenetica-mecanismos', 'epigenetica-influencias',
    'hermetismo-principios', 'lei-correspondencia', 'tabua-esmeralda',
    'kundalini-o-que-e', 'kundalini-vertebras', 'kundalini-praticas',
    'teosofia-o-que-e', 'teosofia-blavatsky', 'teosofia-principios',
    'coracao-campo', 'coracao-coerencia', 'coracao-respiracao',
];
$marcas = implode(',', array_fill(0, count($sementes), '?'));
$stmt = $pdo->prepare("UPDATE licoes SET publicado_em = NULL WHERE slug IN ($marcas) AND publicado_em IS NOT NULL");
$stmt->execute($sementes);
echo '  Sementes arquivadas: ' . $stmt->rowCount() . "\n";
