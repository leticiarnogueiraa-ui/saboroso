
<?php

require_once __DIR__ . '/config.php';

function buscarVideoYouTube($consulta)
{
    global $youtubeApiKey;

    if (
        empty($youtubeApiKey) ||
        $youtubeApiKey === 'COLE_SUA_CHAVE_AQUI'
    ) {
        error_log('A chave da API do YouTube não foi configurada.');
        return null;
    }

    $consulta = trim((string) $consulta);

    if ($consulta === '') {
        return null;
    }

    
    $pastaCache = __DIR__ . '/cache_videos';

    if (!is_dir($pastaCache)) {
        if (!mkdir($pastaCache, 0755, true) && !is_dir($pastaCache)) {
            error_log('Não foi possível criar a pasta de cache.');
            return null;
        }
    }

    $arquivoCache = $pastaCache . '/' .
        hash('sha256', strtolower($consulta)) . '.json';

    if (is_file($arquivoCache)) {
        $cache = json_decode(
            file_get_contents($arquivoCache),
            true
        );

        if (
            is_array($cache) &&
            isset($cache['salvo_em']) &&
            time() - $cache['salvo_em'] < 30 * 24 * 60 * 60
        ) {
            return $cache['video_id'] ?? null;
        }
    }

    $url = 'https://www.googleapis.com/youtube/v3/search?' .
        http_build_query([
            'part' => 'snippet',
            'type' => 'video',
            'maxResults' => 1,
            'q' => $consulta,
            'videoEmbeddable' => 'true',
            'safeSearch' => 'moderate',
            'key' => $youtubeApiKey
        ]);

    $contexto = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 15,
            'ignore_errors' => true,
            'header' => "Accept: application/json\r\n"
        ]
    ]);

    $resposta = @file_get_contents($url, false, $contexto);

    if ($resposta === false) {
        error_log('Falha ao conectar com a API do YouTube.');
        return null;
    }

    $dados = json_decode($resposta, true);

    if (isset($dados['error'])) {
        error_log(
            'Erro da API do YouTube: ' .
            ($dados['error']['message'] ?? 'Erro desconhecido')
        );
        return null;
    }

    $videoId = $dados['items'][0]['id']['videoId'] ?? null;

    if (
        !is_string($videoId) ||
        !preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)
    ) {
        $videoId = null;
    }

    file_put_contents(
        $arquivoCache,
        json_encode([
            'video_id' => $videoId,
            'salvo_em' => time()
        ]),
        LOCK_EX
    );

    return $videoId;
}
