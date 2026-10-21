```php
<?php
require_once 'config.php';
require_once 'includes.php';

$erros = [];
$email = '';

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
        $erros[] = 'Informe um e-mail válido e sua senha.';
    } else {
        try {
            $consulta = $pdo->prepare(
                'SELECT id, nome, email, senha
                 FROM usuarios
                 WHERE email = :email
                 LIMIT 1'
            );

            $consulta->execute(['email' => $email]);
            $usuario = $consulta->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                session_regenerate_id(true);

                $_SESSION['usuario_id'] = (int) $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];

                header('Location: index.php');
                exit;
            }

            $erros[] = 'E-mail ou senha incorretos.';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $erros[] = 'Não foi possível entrar agora. Tente novamente.';
        }
    }
}

iniciarCabecalho('Entrar');
?>

<section class="formulario">
    <h1>Bem-vindo de volta!</h1>

    <p class="texto-suave">
        Entre na sua conta e continue descobrindo novos sabores.
    </p>

    <?php if (isset($_GET['cadastro']) && $_GET['cadastro'] === 'sucesso'): ?>
        <div class="mensagem">
            Cadastro realizado com sucesso! Agora você já pode entrar.
        </div>
    <?php endif; ?>

    <?php foreach ($erros as $erro): ?>
        <div class="mensagem erro">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endforeach; ?>

    <form action="login.php" method="POST">
        <label for="email">E-mail</label>
        <input
            type="email"
            id="email"
            name="email"
            maxlength="150"
            value="<?= htmlspecialchars($email) ?>"
            autocomplete="email"
            required
        >

        <label for="senha">Senha</label>
        <input
            type="password"
            id="senha"
            name="senha"
            autocomplete="current-password"
            required
        >

        <button type="submit" class="botao">
            Entrar na minha conta
        </button>
    </form>

    <p style="margin-top: 18px; text-align: center;">
        <a href="recuperar-senha.php" style="color: var(--laranja);">
            Esqueci minha senha
        </a>
    </p>

    <p style="margin-top: 12px; text-align: center;">
        Ainda não tem conta?
        <a href="cadastro.php" style="color: var(--laranja);">
            Cadastre-se
        </a>
    </p>
</section>

<?php finalizarPagina(); ?>
```