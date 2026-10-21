
<?php
require_once 'config.php';
require_once 'includes.php';

$erros = [];
$email = '';
$mensagemSucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um endereço de e-mail válido.';
    } else {
        try {
            $consulta = $pdo->prepare(
                'SELECT id FROM usuarios WHERE email = :email LIMIT 1'
            );

            $consulta->execute(['email' => $email]);
            $usuario = $consulta->fetch();

            if ($usuario) {
                $mensagemSucesso =
                    'Solicitação recebida! A próxima etapa de recuperação será disponibilizada pelo sistema.';
            } else {
                $erros[] = 'Não encontramos uma conta com esse e-mail.';
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $erros[] = 'Não foi possível processar a solicitação. Tente novamente.';
        }
    }
}

iniciarCabecalho('Recuperar senha');
?>

<section class="formulario">
    <h1>Recuperar senha</h1>

    <p class="texto-suave">
        Informe o e-mail utilizado no cadastro para verificarmos sua conta.
    </p>

    <?php if ($mensagemSucesso !== ''): ?>
        <div class="mensagem">
            <?= htmlspecialchars($mensagemSucesso) ?>
        </div>
    <?php endif; ?>

    <?php foreach ($erros as $erro): ?>
        <div class="mensagem erro">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endforeach; ?>

    <form action="recuperar-senha.php" method="POST">
        <label for="email">E-mail cadastrado</label>

        <input
            type="email"
            id="email"
            name="email"
            maxlength="150"
            value="<?= htmlspecialchars($email) ?>"
            autocomplete="email"
            required
        >

        <button type="submit" class="botao">
            Verificar e-mail
        </button>
    </form>

    <p style="margin-top: 20px; text-align: center;">
        <a href="login.php" style="color: var(--laranja);">
            Voltar para o login
        </a>
    </p>
</section>

<?php finalizarPagina(); ?>
