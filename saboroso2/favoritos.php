
<?php
require_once 'config.php';
require_once 'includes.php';
require_once 'receitas.php';

if (!isset($_SESSION['favoritos'])) {
    $_SESSION['favoritos'] = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'receita_id', FILTER_VALIDATE_INT);
    $acao = $_POST['acao'] ?? '';

    if ($id && buscarReceitaPorId($id)) {
        $favoritos = array_map('intval', $_SESSION['favoritos']);

        if ($acao === 'adicionar' && !in_array($id, $favoritos, true)) {
            $favoritos[] = $id;
        } elseif ($acao === 'remover') {
            $favoritos = array_values(
                array_filter(
                    $favoritos,
                    fn($favorito) => $favorito !== $id
                )
            );
        }

        $_SESSION['favoritos'] = $favoritos;
    }

    
    $origem = $_SERVER['HTTP_REFERER'] ?? '';
    $voltarParaDetalhe = strpos($origem, 'detalhe.php') !== false;

    if ($voltarParaDetalhe && $id) {
        header('Location: detalhe.php?id=' . $id);
    } else {
        header('Location: favoritos.php');
    }

    exit;
}

$favoritosIds = array_map('intval', $_SESSION['favoritos']);
$receitasFavoritas = array_values(
    array_filter(
        $receitas,
        fn($receita) => in_array(
            (int) $receita['id'],
            $favoritosIds,
            true
        )
    )
);

iniciarCabecalho('Meus favoritos');
?>

<section class="secao">
    <h1>Minhas receitas favoritas ♥</h1>

    <p class="texto-suave">
        Guarde aqui as receitas que você quer preparar novamente.
    </p>

    <?php if (empty($receitasFavoritas)): ?>
        <div class="mensagem">
            Você ainda não adicionou receitas aos favoritos.
            Explore o catálogo e escolha suas preferidas!
        </div>

        <a href="index.php#catalogo" class="botao">
            Explorar receitas
        </a>

    <?php else: ?>
        <p class="texto-suave">
            Você salvou <?= count($receitasFavoritas) ?> receita(s).
        </p>

        <div class="grade-receitas">
            <?php foreach ($receitasFavoritas as $receita): ?>
                <article class="card-receita">
                    <a href="detalhe.php?id=<?= (int) $receita['id'] ?>">
                        <img
                            src="<?= htmlspecialchars($receita['imagem']) ?>"
                            alt="<?= htmlspecialchars($receita['titulo']) ?>"
                            loading="lazy"
                        >
                    </a>

                    <div class="card-conteudo">
                        <span class="categoria-tag">
                            <?= htmlspecialchars($receita['categoria']) ?>
                        </span>

                        <h3>
                            <?= htmlspecialchars($receita['titulo']) ?>
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

                        <form action="favoritos.php" method="POST">
                            <input
                                type="hidden"
                                name="receita_id"
                                value="<?= (int) $receita['id'] ?>"
                            >

                            <input
                                type="hidden"
                                name="acao"
                                value="remover"
                            >

                            <button
                                type="submit"
                                class="botao botao-secundario"
                                style="margin-top: 10px; width: 100%;"
                            >
                                Remover dos favoritos
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php finalizarPagina(); ?>
