<?php
require_once 'include/conexao.php';

if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();

$erro = '';

// Formulário roda quando há o envio, captando o email e a senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = (string) ($_POST['password'] ?? '');

    // Caso ambos campos estejam vazios
    if ($email === '' || $pass === '') {
        $erro = 'Preencha e-mail e senha.';
    }

    // Busca no banco um usuário com o email informado
    else {
        $stmt = $conn->prepare('SELECT id, usuario, email, senha, role, status FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        // Se o email for encontrado no banco e a senha coincidir com o cadastrado, ocorre o login
        if ($user && password_verify($pass, $user['senha'])) {
            $status = strtolower(trim($user['status'] ?? 'ativo'));

            // Conta existe, mas está inativa
            if ($status !== 'ativo') {
                $erro = "Sua conta está inativa. Entre em contato com o suporte para reativação.";
            } else {


                // Verifica pelo role se o usuário é adm ou não + sempre lê o email "adm@adm.com" como adm
                $isAdmin = (($user['role'] ?? '') === 'admin') || (strcasecmp($user['email'], 'adm@adm.com') === 0);

                $_SESSION['id'] = (int) $user['id'];
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = (string) $user['usuario'];
                $_SESSION['email'] = (string) $user['email'];
                $_SESSION['role'] = $isAdmin ? 'admin' : 'user';

                // Redirecionamento para a página inicial pós-login + novo ID gerado a cada login
                header('Location: index.php');
                session_regenerate_id(true);
                exit;
            }

        } else {
            $erro = 'Credenciais inválidas!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>

    <link rel="stylesheet" href="css/styles2.css">
    <link rel="stylesheet" href="css/stylesNav.css">
    <title>Login</title>
    <style>
        .main-content {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex: 1;
            padding: 20px 0 10px
        }

        h2 {
            font-weight: bold
        }

        .form-container {
            background: linear-gradient(to bottom, #202330ff 0%, #272b3fff 50%);
            padding: 30px;
            border-radius: 10px;
            width: 500px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, .3);
            text-align: center
        }

        .form-container .form-control {
            background-color: rgba(255, 255, 255, .10) !important;
            color: #fff !important;
            outline: none !important;
            box-shadow: none !important
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 20px
        }

        .form-group label {
            font-size: 14px;
            margin-bottom: 6px;
            color: #fff
        }

        .form-group input {
            background: rgba(255, 255, 255, .1);
            border: none !important;
            border-radius: 4px;
            padding: 8px 10px;
            outline: none !important;
            width: 100%;
            height: 45px;
            color: #fff
        }

        .form-group input::placeholder {
            color: #fdfaf194;
            font-size: 14px
        }

        .btn-detail {
            width: 100px
        }

        .input-wrap {
            position: relative
        }

        .input-wrap .form-control {
            padding-right: 234px
        }

        .input-wrap .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 0;
            line-height: 1;
            cursor: pointer
        }

        .mt-3 {
            color: #fff;
            font-size: .9rem
        }

        .mt-3 a {
            color: #7aa3efff;
            text-decoration: none
        }

        .mt-3 a:hover {
            text-decoration: underline
        }
    </style>
</head>

<body>
    <div class="content">
        <?php require_once 'include/navbar.php' ?>

        <!-- Caso erro de login, injeta o script para a mensgaem de erro -->
        <?php if (!empty($erro)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (window.mlToast) {
                        window.mlToast('err', <?= json_encode($erro, JSON_UNESCAPED_UNICODE) ?>);
                    }
                });
            </script>
        <?php endif; ?>

        <br><br>

        <div class="main-content">
            <div class="form-container">

                <!-- Formulário de login -->
                <form method="post">
                    <h2>Login</h2>
                    <br>

                    <div class="form-group">
                        <label>EMAIL:</label>
                        <input type="email" name="email" placeholder="Digite seu email" class="form-control" required>
                    </div>

                    <div class="form-group position-relative">
                        <label>SENHA:</label>
                        <div class="input-wrap">
                            <input type="password" name="password" placeholder="Digite sua senha" class="form-control"
                                required autocomplete="current-password">
                            <button class="toggle-password" type="button" aria-pressed="false"
                                aria-label="Mostrar/ocultar senha">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                                <span class="visually-hidden">Mostrar/ocultar senha</span>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-detail">Entrar</button>
                    <br><br>
                    <p class="mt-3">Não tem conta? <a href="cadastrousuario.php">Cadastre-se aqui</a></p>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
    </footer>

    <!-- Script do botão de mostrar/ocultar senha -->
    <script>
        document.querySelectorAll('.toggle-password').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const wrap = this.closest('.input-wrap');
                const input = wrap?.querySelector('input');
                const icon = this.querySelector('i');
                if (!input) return;
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                if (icon) {
                    icon.classList.toggle('bi-eye', showing);
                    icon.classList.toggle('bi-eye-slash', !showing);
                }
                this.setAttribute('aria-pressed', String(!showing));
            });
        });
    </script>

</body>

</html>