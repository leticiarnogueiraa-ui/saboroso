
<?php
require_once 'config.php';
require_once 'includes.php';
require_once 'receitas.php';
require_once 'buscar_video.php';


$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$receita = $id ? buscarReceitaPorId($id) : null;


if (!$receita) {
    http_response_code(404);
    iniciarCabecalho('Receita não encontrada');
    ?>
    <section class="secao">
        <h1>Ops! Receita não encontrada.</h1>

        <p class="texto-suave">
            Essa receita não existe no nosso catálogo.
        </p>

        <a href="index.php" class="botao">
            Voltar ao catálogo
        </a>
    </section>
    <?php
    finalizarPagina();
    exit;
}


iniciarCabecalho($receita['titulo']);


$favoritos = $_SESSION['favoritos'] ?? [];

$estaFavoritada = in_array(
    (int) $receita['id'],
    array_map('intval', $favoritos),
    true
);


$termoVideo = $receita['video_busca']
    ?? ($receita['titulo'] . ' receita passo a passo em português');

$videoId = buscarVideoYouTube($termoVideo);
?>

<section class="detalhe-receita">

    <a href="index.php" class="texto-suave">
        ← Voltar ao catálogo
    </a>

    <span class="categoria-tag">
        <?= htmlspecialchars($receita['categoria']) ?>
    </span>

    <h1>
        <?= htmlspecialchars($receita['titulo']) ?>
    </h1>

    <p class="texto-suave">
        <?= htmlspecialchars($receita['descricao']) ?>
    </p>

    <img
        class="detalhe-imagem"
        src="<?= htmlspecialchars($receita['imagem']) ?>"
        alt="<?= htmlspecialchars($receita['titulo']) ?>"
    >

    <div class="secao">
        <h2>Prepare essa delícia!</h2>

        <p>
            Descubra os ingredientes e siga o passo a passo
            para preparar essa receita em casa.
        </p>

        <form action="favoritos.php" method="POST">
            <input
                type="hidden"
                name="receita_id"
                value="<?= (int) $receita['id'] ?>"
            >

            <input
                type="hidden"
                name="acao"
                value="<?= $estaFavoritada ? 'remover' : 'adicionar' ?>"
            >

            <button type="submit" class="botao botao-secundario">
                <?= $estaFavoritada
                    ? '♥ Remover dos favoritos'
                    : '♡ Adicionar aos favoritos' ?>
            </button>
        </form>
    </div>

    <div class="detalhe-colunas">

        <section class="lista-ingredientes">
            <h2>Ingredientes</h2>

            <ul>
                <?php foreach ($receita['ingredientes'] as $ingrediente): ?>
                    <li>
                        <?= htmlspecialchars($ingrediente) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="modo-preparo">
            <h2>Modo de preparo</h2>

            <ol>
                <?php foreach ($receita['preparo'] as $passo): ?>
                    <li>
                        <?= htmlspecialchars($passo) ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </section>

    </div>

    <section class="secao">
        <h2>Assista ao preparo</h2>

        <p class="texto-suave">
            Acompanhe um vídeo para aprender a preparar
            <?= htmlspecialchars($receita['titulo']) ?>.
        </p>

        <?php if ($videoId): ?>

            <div class="video-container">
                <iframe
                    src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($videoId, ENT_QUOTES, 'UTF-8') ?>"
                    title="Como preparar <?= htmlspecialchars($receita['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen
                ></iframe>
            </div>

        <?php else: ?>

            <div class="mensagem">
                Não foi possível carregar o vídeo automaticamente
                neste momento. Você pode procurar um vídeo dessa receita
                no YouTube pelo botão abaixo.
            </div>

            <a
                class="botao"
                href="<?= htmlspecialchars(
                    'https://www.youtube.com/results?' .
                    http_build_query([
                        'search_query' => $termoVideo
                    ]),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                Procurar vídeo desta receita
            </a>

        <?php endif; ?>
    </section>

</section>

<?php finalizarPagina(); ?>

