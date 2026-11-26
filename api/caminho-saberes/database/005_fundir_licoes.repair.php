<?php
/**
 * Reparação de dados 005 — aplicada uma vez pelo migrate.php (recebe $pdo).
 *
 * As 19 lições-semente eram resumos de uma linha, publicados em paralelo às
 * lições ricas do wiki. Este script incorpora a informação que ainda faltava
 * nas lições ricas (sem repetir o que já estava lá) e deixa as sementes
 * arquivadas como rascunho — publicado_em = NULL, que é o estado desde a
 * reparação 004. O front passa a listar apenas lições publicadas.
 *
 * As frases abaixo vêm das próprias sementes; nada é inventado.
 */

$fusoes = [
    // Pistis Sophia ← "O Que é Gnose?", "Os Três Mundos", "Textos Gnósticos"
    494 => 'Gnose é conhecimento direto e experiencial da natureza divina — não crença, mas experiência. '
         . 'Seu mapa inclui os três mundos (Plerooma, Kenoma e Sarak) e os textos da Biblioteca de Nag Hammadi e dos evangelhos gnósticos.',

    // Prática Diária ← "Prática: Autoobservação"
    498 => 'A base de toda a prática é a autoobservação: um exercício de observação interna sem julgamento.',

    // Epigenética Prática ← "O Que é Epigenética?", "Os 3 Mecanismos", "O Que Influencia?"
    499 => 'Na base, a epigenética estuda mudanças na atividade dos genes sem alterar a sequência do DNA — '
         . 'metilação do DNA, modificação de histonas e microRNA — influenciadas por alimentação, estresse, exercício, sono e conexões sociais.',

    // O Caibalion ← "Tábua de Esmeralda"
    484 => 'A Tábua de Esmeralda, o texto hermético mais antigo e seus ensinamentos, é uma das raízes desta tradição.',

    // Kundalini ← "Os 33 Vértebras"
    485 => 'O canal Sushumna percorre as 33 vértebras e conduz a ascensão da energia pelos chakras.',

    // Helena Blavatsky ← "Os Três Princípios"
    483 => 'Três princípios sustentam essa visão: reencarnação, karma e a unidade fundamental de toda vida.',

    // Coração ← "Campo Eletromagnético", "Respiração Coerente"
    500 => 'O coração gera um campo eletromagnético cerca de 60 vezes maior que o do cérebro; '
         . 'a respiração coerente é a técnica que equilibra o sistema nervoso.',
];

$incorporadas = 0;
foreach ($fusoes as $id => $texto) {
    $stmt = $pdo->prepare('SELECT conteudo FROM licoes WHERE id = ?');
    $stmt->execute([$id]);
    $conteudo = $stmt->fetchColumn();
    if ($conteudo === false) {
        continue;
    }
    // Idempotente mesmo se o registro da migration for apagado.
    if (mb_strpos($conteudo, $texto) !== false) {
        continue;
    }
    $novo = '<p><strong>Em síntese:</strong> ' . $texto . '</p>' . $conteudo;
    $pdo->prepare('UPDATE licoes SET conteudo = ? WHERE id = ?')->execute([$novo, $id]);
    $incorporadas++;
}
echo "  Informações fundidas em {$incorporadas} lições ricas\n";

// As sementes ficam arquivadas (rascunho) e fora de qualquer listagem pública.
$stmt = $pdo->prepare("UPDATE licoes SET publicado_em = NULL WHERE id BETWEEN 1 AND 19 AND publicado_em IS NOT NULL");
$stmt->execute();
echo '  Sementes arquivadas: ' . $stmt->rowCount() . "\n";
