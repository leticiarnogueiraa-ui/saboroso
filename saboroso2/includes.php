
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function iniciarCabecalho($titulo = 'Início') {
    $logado = isset($_SESSION['usuario_id']);
    $nome = $_SESSION['usuario_nome'] ?? '';
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($titulo) ?> | Saboroso</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <header class="cabecalho">
            <a href="index.php" class="marca">
                <img src="logo saboroso.png" alt="Logo Saboroso" class="logo">
                <span>Saboroso</span>
            </a>

            <nav class="menu">
                <a href="index.php">Início</a>
                <a href="index.php#categorias">Categorias</a>
                <a href="favoritos.php">Favoritos</a>

                <?php if ($logado): ?>
                    <span class="usuario">Olá, <?= htmlspecialchars($nome) ?>!</span>
                    <a href="logout.php">Sair</a>
                <?php else: ?>
                    <a href="login.php">Entrar</a>
                    <a href="cadastro.php" class="botao-menu">Cadastrar</a>
                <?php endif; ?>
            </nav>
        </header>

        <main class="conteudo">
    <?php
}

function finalizarPagina() {
    ?>
        </main>

        <footer class="rodape">
            <p>© <?= date('Y') ?> Saboroso — Seu próximo prato favorito começa aqui.</p>
        </footer>
        <script src="js/script.js"></script>
    </body>
    </html>
    <?php
}
?>
