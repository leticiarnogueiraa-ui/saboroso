
<?php
require_once 'config.php';
require_once 'includes.php';
require_once 'receitas.php';

$termoBusca = trim($_GET['busca'] ?? '');
$categoriaSelecionada = trim($_GET['categoria'] ?? '');

$categoriasValidas = $categorias;

if (!in_array($categoriaSelecionada, $categoriasValidas, true)) {
    $categoriaSelecionada = '';
}


function normalizarBusca($texto)
{
    $texto = mb_strtolower($texto, 'UTF-8');

    if (class_exists('Transliterator')) {
        $transliterador = Transliterator::create(
            'NFD; [:Nonspacing Mark:] Remove; NFC'
        );

        if ($transliterador) {
            $texto = $transliterador->transliterate($texto);
        }
    } else {
        $texto = strtr($texto, [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c'
        ]);
    }

    return trim($texto);
}

$favoritos = $_SESSION['favoritos'] ?? [];
$receitasExibidas = [];

$termoNormalizado = normalizarBusca($termoBusca);

foreach ($receitas as $receita) {
    $textoPesquisavel = implode(' ', [
        $receita['titulo'],
        $receita['categoria'],
        $receita['descricao'],
        implode(' ', $receita['ingredientes'])
    ]);

    $textoNormalizado = normalizarBusca($textoPesquisavel);

    $correspondeBusca = (
        $termoNormalizado === '' ||
        mb_strpos(
            $textoNormalizado,
            $termoNormalizado,
            0,
            'UTF-8'
        ) !== false
    );

    $correspondeCategoria = (
        $categoriaSelecionada === '' ||
        $receita['categoria'] === $categoriaSelecionada
    );

    if ($correspondeBusca && $correspondeCategoria) {
        $receitasExibidas[] = $receita;
    }
}

iniciarCabecalho('Início');

$descricoesCategorias = [
    'Café da manhã' => 'Comece o dia com sabor.',
    'Almoço' => 'Ideias para refeições especiais.',
    'Massas' => 'Receitas para os amantes de massas.',
    'Sobremesas' => 'Um toque doce para o seu dia.',
    'Lanches' => 'Opções práticas e deliciosas.'
];
?>

<section class="hero">
    <div class="hero-texto">
        <span class="etiqueta">Streaming de culinária</span>

        <h1>Seu próximo prato favorito começa aqui.</h1>

        <p>
            Descubra receitas, explore novos sabores e encontre
            inspiração para cozinhar todos os dias.
        </p>

        <a href="#catalogo" class="botao botao-secundario">
            Explorar receitas
        </a>
    </div>
</section>

<section class="secao" id="categorias">
    <div class="secao-titulo">
        <div>
            <h2>Explore por categoria</h2>

            <p class="texto-suave">
                Encontre a inspiração perfeita para cada momento.
            </p>
        </div>
    </div>

    <div class="grade-receitas grade-categorias">
        <?php foreach ($categorias as $categoria): ?>
            <a
                class="card-receita card-categoria"
                href="index.php?categoria=<?= urlencode($categoria) ?>#catalogo"
                data-categoria="<?= htmlspecialchars(
                    $categoria,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
                <div class="card-conteudo">
                    <span class="categoria-tag">
                        <?= htmlspecialchars($categoria) ?>
                    </span>

                    <h3>
                        <?= htmlspecialchars(
                            $descricoesCategorias[$categoria] ?? ''
                        ) ?>
                    </h3>

                    <span class="texto-suave">Ver receitas →</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="secao" id="catalogo">
    <div class="secao-titulo">
        <div>
            <h2 id="tituloCatalogo">
                <?php if ($termoBusca !== ''): ?>
                    Resultados para "<?= htmlspecialchars($termoBusca) ?>"
                <?php elseif ($categoriaSelecionada !== ''): ?>
                    Receitas de <?= htmlspecialchars($categoriaSelecionada) ?>
                <?php else: ?>
                    Nosso catálogo
                <?php endif; ?>
            </h2>

            <p class="texto-suave" id="contadorReceitas">
                <?= count($receitasExibidas) ?>
                <?= count($receitasExibidas) === 1
                    ? 'receita encontrada.'
                    : 'receitas encontradas.' ?>
            </p>
        </div>

        <?php if ($termoBusca !== '' || $categoriaSelecionada !== ''): ?>
            <a
                href="index.php#catalogo"
                class="botao botao-secundario"
                id="limparFiltros"
            >
                Limpar filtros
            </a>
        <?php endif; ?>
    </div>

    <form action="index.php" method="GET" class="busca" id="formBusca">
        <input
            type="search"
            name="busca"
            id="campoBusca"
            placeholder="Buscar receitas, ingredientes ou categorias..."
            value="<?= htmlspecialchars(
                $termoBusca,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            aria-label="Buscar receitas"
        >

        <?php if ($categoriaSelecionada !== ''): ?>
            <input
                type="hidden"
                name="categoria"
                value="<?= htmlspecialchars(
                    $categoriaSelecionada,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
        <?php endif; ?>

        <button type="submit" class="botao">Buscar</button>
    </form>

    <div class="filtros-categoria" id="filtrosCategoria">
        <a
            class="botao botao-secundario filtro-categoria <?= $categoriaSelecionada === '' ? 'ativo' : '' ?>"
            href="index.php?busca=<?= urlencode($termoBusca) ?>#catalogo"
        >
            Todas
        </a>

        <?php foreach ($categorias as $categoria): ?>
            <a
                class="botao botao-secundario filtro-categoria <?= $categoriaSelecionada === $categoria ? 'ativo' : '' ?>"
                href="index.php?busca=<?= urlencode($termoBusca) ?>&categoria=<?= urlencode($categoria) ?>#catalogo"
            >
                <?= htmlspecialchars($categoria) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="grade-receitas" id="gradeReceitas">
        <?php foreach ($receitasExibidas as $receita): ?>
            <article
                class="card-receita card-catalogo"
                data-categoria="<?= htmlspecialchars(
                    $receita['categoria'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
                <a href="detalhe.php?id=<?= (int) $receita['id'] ?>">
                    <img
                        src="<?= htmlspecialchars(
                            $receita['imagem'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        alt="<?= htmlspecialchars(
                            $receita['titulo'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        loading="lazy"
                    >
                </a>

                <div class="card-conteudo">
                    <span class="categoria-tag">
                        <?= htmlspecialchars($receita['categoria']) ?>
                    </span>

                    <h3>
                        <a href="detalhe.php?id=<?= (int) $receita['id'] ?>">
                            <?= htmlspecialchars($receita['titulo']) ?>
                        </a>
                    </h3>

                    <p>
                        <?= htmlspecialchars($receita['descricao']) ?>
                    </p>

                    <a
                        href="detalhe.php?id=<?= (int) $receita['id'] ?>"
                        class="botao"
                    >
                        Ver receita
                    </a>

                    <?php if (in_array(
                        (int) $receita['id'],
                        array_map('intval', $favoritos),
                        true
                    )): ?>
                        <span aria-label="Receita favoritada">♥</span>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (count($receitasExibidas) === 0): ?>
        <div class="mensagem" id="mensagemVazia">
            Nenhuma receita encontrada para
            "<?= htmlspecialchars($termoBusca) ?>".
            Tente outro nome ou ingrediente.
        </div>
    <?php endif; ?>
</section>

<?php finalizarPagina(); ?>
