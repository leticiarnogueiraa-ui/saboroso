
<?php
require_once 'config.php';
require_once 'includes.php';

$erros = [];
$nome = '';
$email = '';


function enviarAvisoCadastroResend($nomeUsuario, $emailUsuario)
{
    global $resendApiKey, $resendOwnerEmail;

    if (
        empty($resendApiKey) ||
        empty($resendOwnerEmail) ||
        !filter_var($resendOwnerEmail, FILTER_VALIDATE_EMAIL)
    ) {
        error_log('Resend: configure a chave e o e-mail do dono no config.php.');
        return false;
    }

    if (!function_exists('curl_init')) {
        error_log('Resend: a extensão cURL do PHP não está habilitada.');
        return false;
    }

    $nomeSeguro = htmlspecialchars(
        $nomeUsuario,
        ENT_QUOTES,
        'UTF-8'
    );

    $emailSeguro = htmlspecialchars(
        $emailUsuario,
        ENT_QUOTES,
        'UTF-8'
    );

    $dadosEmail = [
        'from' => 'Saboroso <onboarding@resend.dev>',
        'to' => [$resendOwnerEmail],
        'subject' => 'Novo cadastro no Saboroso',
        'html' => '
            <h2>Novo usuário cadastrado!</h2>
            <p>Uma nova pessoa criou uma conta no Saboroso.</p>
            <p><strong>Nome:</strong> ' . $nomeSeguro . '</p>
            <p><strong>E-mail:</strong> ' . $emailSeguro . '</p>
        '
    ];

    $curl = curl_init('https://api.resend.com/emails');

    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $resendApiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode(
            $dadosEmail,
            JSON_UNESCAPED_UNICODE
        ),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20
    ]);

    $resposta = curl_exec($curl);
    $erroCurl = curl_error($curl);
    $statusHTTP = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if ($resposta === false || $statusHTTP < 200 || $statusHTTP >= 300) {
        error_log(
            'Resend falhou. HTTP: ' . $statusHTTP .
            '. Erro cURL: ' . $erroCurl .
            '. Resposta: ' . (string) $resposta
        );

        return false;
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if ($nome === '') {
        $erros[] = 'Informe seu nome.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido.';
    }

    if (strlen($senha) < 8) {
        $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
    }

    if ($senha !== $confirmarSenha) {
        $erros[] = 'As senhas não coincidem.';
    }

    if (empty($erros)) {
        try {
            $verificar = $pdo->prepare(
                'SELECT id FROM usuarios WHERE email = :email LIMIT 1'
            );

            $verificar->execute(['email' => $email]);

            if ($verificar->fetch()) {
                $erros[] = 'Este e-mail já está cadastrado.';
            } else {
                $senhaHash = password_hash(
                    $senha,
                    PASSWORD_DEFAULT
                );

                $inserir = $pdo->prepare(
                    'INSERT INTO usuarios (nome, email, senha)
                     VALUES (:nome, :email, :senha)'
                );

                $inserir->execute([
                    'nome' => $nome,
                    'email' => $email,
                    'senha' => $senhaHash
                ]);

               
                enviarAvisoCadastroResend($nome, $email);

                header('Location: login.php?cadastro=sucesso');
                exit;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());

            $erros[] =
                'Não foi possível concluir o cadastro. Tente novamente.';
        }
    }
}

iniciarCabecalho('Criar conta');
?>

<section class="formulario">
    <h1>Crie sua conta</h1>

    <p class="texto-suave">
        Faça parte do Saboroso e descubra sua próxima receita favorita.
    </p>

    <?php foreach ($erros as $erro): ?>
        <div class="mensagem erro">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endforeach; ?>

    <form action="cadastro.php" method="POST">
        <label for="nome">Nome completo</label>
        <input
            type="text"
            id="nome"
            name="nome"
            maxlength="100"
            value="<?= htmlspecialchars($nome) ?>"
            autocomplete="name"
            required
        >

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
            minlength="8"
            autocomplete="new-password"
            required
        >

        <label for="confirmar_senha">Confirmar senha</label>
        <input
            type="password"
            id="confirmar_senha"
            name="confirmar_senha"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <button type="submit" class="botao">
            Criar minha conta
        </button>
    </form>

    <p style="margin-top: 22px; text-align: center;">
        Já tem uma conta?
        <a href="login.php" style="color: var(--laranja);">
            Entrar
        </a>
    </p>
</section>

<?php finalizarPagina(); ?>
