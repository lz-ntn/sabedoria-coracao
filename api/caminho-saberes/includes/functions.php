<?php
/**
 * Funções auxiliares do sistema
 * 
 * Todas as funções globais que facilitam o desenvolvimento.
 */

/**
 * Resposta JSON padronizada para a API
 * 
 * @param array $data     Dados a retornar
 * @param int   $status   Código HTTP (200, 400, 404, 409, 500)
 */
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Erro padronizado
 */
function json_error($mensagem, $status = 400) {
    json_response(['error' => $mensagem], $status);
}

/**
 * Valida se campos obrigatórios existem no $_POST ou JSON body
 * 
 * @param array $fields          ['campo1', 'campo2']
 * @param array $data            Dados recebidos (geralmente $_POST ou parsed JSON)
 * @return string|null           Mensagem de erro ou null se OK
 */
function validar_campos($fields, $data) {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            return "O campo '{$field}' é obrigatório.";
        }
    }
    return null;
}

/**
 * Lê o corpo da requisição (JSON ou form-data)
 */
function ler_corpo() {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'application/json') !== false) {
        $body = file_get_contents('php://input');
        return json_decode($body, true) ?? [];
    }

    return $_POST;
}

/**
 * Escapa HTML para prevenir XSS
 * 
 * @param string|null $texto
 * @return string
 */
function esc_html($texto) {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Renderiza o conteúdo de uma lição de forma segura.
 *
 * O banco pode conter tanto texto puro quanto HTML rico (h2/h3/p/ul/ol/li).
 * Esta função detecta automaticamente o formato:
 *   - Se contém tags HTML válidas: sanitiza com allowlist e renderiza
 *   - Se for texto puro: escapa e aplica nl2br
 *
 * @param string|null $conteudo
 * @return string HTML pronto para ecoar
 */
function render_conteudo($conteudo) {
    $conteudo = (string) ($conteudo ?? '');
    if ($conteudo === '') return '';

    // Detecta se há tags HTML estruturais (h2/h3/p/ul/ol/li/blockquote)
    $temHtml = (bool) preg_match('/<\s*(h[1-6]|p|ul|ol|li|blockquote|strong|em)\b/i', $conteudo);

    if (!$temHtml) {
        return nl2br(esc_html($conteudo));
    }

    // HTML detectado: sanitiza com allowlist e mantém estrutura
    $permitidas = '<h2><h3><h4><h5><h6><p><br><hr>'
                . '<ul><ol><li><blockquote><pre><code>'
                . '<strong><b><em><i><u><s>'
                . '<a><img>';

    $html = strip_tags($conteudo, $permitidas);

    // Segurança: remove handlers javascript: em links e atributos perigosos
    $html = preg_replace('/\s*on\w+\s*=\s*"[^"]*"/i', '', $html);
    $html = preg_replace('/\s*on\w+\s*=\s*\'[^\']*\'/i', '', $html);
    $html = preg_replace('/href\s*=\s*"javascript:[^"]*"/i', 'href="#"', $html);
    $html = preg_replace("/href\s*=\s*'javascript:[^']*'/i", "href='#'", $html);

    return $html;
}

/**
 * Slugify: transforma "O Que é Gnose?" em "o-que-e-gnose"
 */
function slugify($texto) {
    $texto = mb_strtolower($texto, 'UTF-8');
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    return trim($texto, '-');
}

/**
 * Gera UUID versão 4 (identificador único)
 */
function gerar_uuid() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Formata data para o padrão brasileiro
 */
function formatar_data($data, $formato = 'd/m/Y H:i') {
    if (!$data) return '-';
    $timestamp = is_numeric($data) ? $data : strtotime($data);
    return date($formato, $timestamp);
}

/**
 * Log de erros em arquivo
 */
function log_erro($mensagem, $arquivo = 'app') {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $linha = '[' . date('Y-m-d H:i:s') . '] ' . $mensagem . PHP_EOL;
    file_put_contents("{$logDir}/{$arquivo}.log", $linha, FILE_APPEND);
}

/**
 * Redireciona para outra página
 */
function redirecionar($url) {
    header("Location: {$url}");
    exit;
}

/**
 * Valida token CSRF vindo de requisição AJAX (header X-CSRF-Token)
 */
function validar_csrf_api() {
    $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!\Core\Csrf::validate($header)) {
        json_error('Token CSRF inválido.', 403);
    }
}

/**
 * Obtém ou cria UUID do usuário (via cookie)
 */
function obter_uuid_usuario() {
    $cookieName = 'caminho_uuid';

    if (isset($_COOKIE[$cookieName])) {
        return $_COOKIE[$cookieName];
    }

    $uuid = gerar_uuid();
    setcookie($cookieName, $uuid, time() + 86400 * 365, '/', '', false, true); // 1 ano
    $_COOKIE[$cookieName] = $uuid;

    return $uuid;
}

/**
 * Obtém ID do usuário no banco (cria se não existir)
 */
function obter_usuario_id($db) {
    $uuid = obter_uuid_usuario();

    $usuario = $db->fetch(
        'SELECT id FROM usuarios WHERE uuid = ?',
        [$uuid]
    );

    if ($usuario) {
        // Atualiza último acesso
        $db->update('usuarios', ['ultimo_acesso' => date('Y-m-d H:i:s')], 'id = ?', [$usuario['id']]);
        return $usuario['id'];
    }

    // Cria novo usuário
    return $db->insert('usuarios', [
        'uuid' => $uuid,
        'criado_em' => date('Y-m-d H:i:s'),
        'ultimo_acesso' => date('Y-m-d H:i:s')
    ]);
}
