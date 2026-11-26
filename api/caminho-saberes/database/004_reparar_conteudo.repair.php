<?php
/**
 * Reparação de dados — aplicada uma vez pelo migrate.php (recebe $pdo).
 *
 * 1. Dupla codificação UTF-8: o texto foi salvo como UTF-8 de caracteres já
 *    codificados ("não" → "nÃ£o", "—" → "â€”"). A reversão é decodificar de
 *    UTF-8 para Windows-1252 (foi assim que o mojibake nasceu) e só aceitar
 *    o resultado se ele for UTF-8 válido e tiver MENOS marcas. Nunca mexe em
 *    texto correto: o SQL LIKE do MySQL é insensível a acento e dava falsos
 *    positivos (Ã casa com A).
 *
 * 2. Cinco aulas de desenvolvimento web (HTML/CSS/JS) foram importadas do
 *    projeto `helena-blavatsky/estudo-web` como se fossem Teosofia. Não são
 *    duplicados nem conteúdo espiritual: são despublicadas (reversível),
 *    mantidas no banco para auditoria. O importador foi desativado.
 */

/** @return int */
function contarMarcasReparacao(string $texto, array $marcas): int
{
    $n = 0;
    foreach ($marcas as $marca) {
        $n += substr_count($texto, $marca);
    }
    return $n;
}

/** @return string|null texto reparado, ou null se não houver reparo seguro */
function repararTextoUtf8($valor, array $marcas)
{
    if ($valor === null || $valor === '') {
        return null;
    }
    $texto = (string)$valor;
    $antes = contarMarcasReparacao($texto, $marcas);
    if ($antes === 0) {
        return null;
    }

    $reparado = mb_convert_encoding($texto, 'Windows-1252', 'UTF-8');
    if (!mb_check_encoding($reparado, 'UTF-8')) {
        return null;
    }
    if (contarMarcasReparacao($reparado, $marcas) >= $antes) {
        return null;
    }
    return $reparado;
}

$marcas = ['Ã£', 'Ã©', 'Ã¡', 'Ã­', 'Ã³', 'Ãº', 'Ã§', 'â€'];
$campos = [
    'categorias' => ['descricao'],
    'licoes'     => ['titulo', 'resumo', 'conteudo', 'tags', 'fonte'],
];
$totalCampos = 0;

foreach ($campos as $tabela => $colunas) {
    $linhas = $pdo->query("SELECT * FROM `{$tabela}`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($linhas as $linha) {
        $alteracoes = [];
        foreach ($colunas as $coluna) {
            $reparado = repararTextoUtf8($linha[$coluna] ?? null, $marcas);
            if ($reparado !== null && $reparado !== $linha[$coluna]) {
                $alteracoes[$coluna] = $reparado;
            }
        }
        if ($alteracoes === []) {
            continue;
        }

        $sets = [];
        foreach (array_keys($alteracoes) as $coluna) {
            $sets[] = "`{$coluna}` = :{$coluna}";
        }
        $alteracoes['id'] = $linha['id'];
        $sql = "UPDATE `{$tabela}` SET " . implode(', ', $sets) . " WHERE id = :id";
        $pdo->prepare($sql)->execute($alteracoes);
        $totalCampos += count($sets);
    }
}
echo "  UTF-8: {$totalCampos} campos reparados\n";

$stmt = $pdo->prepare(
    "UPDATE `licoes` SET `publicado_em` = NULL
      WHERE `fonte` = 'Helena Blavatsky — Estudo Web' AND `publicado_em` IS NOT NULL"
);
$stmt->execute();
echo '  Aulas de web despublicadas: ' . $stmt->rowCount() . "\n";
