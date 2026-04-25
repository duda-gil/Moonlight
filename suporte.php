<?php
require_once 'include/conexao.php';
session_start();

// Verifica o método POST e os campos do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    // Validação de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $mensagem === '') {
        $_SESSION['flash'] = [
            'tipo' => 'danger',
            'msg' => 'Preencha um e-mail válido e descreva o problema.'
        ];
        header('Location: suporte.php');
        exit;
    } else {
        $_SESSION['flash'] = [
            'tipo' => 'success',
            'msg' => 'Mensagem enviada! Em breve entraremos em contato pelo email informado.'
        ];
        header('Location: suporte.php');
        exit;
    }
}
$NAV_CONTEXT = 'store';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="css/styles2.css">
    <link rel="stylesheet" href="css/stylesNav.css">

    <title>Moonlight</title>
</head>

<body>
    <div class="content">

        <?php require_once 'include/navbar.php'; ?>

        <?php
        // Disponibiliza o tipo e o texto da mensgaem
        if (!empty($_SESSION['flash'])):
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $kind = ($f['tipo'] === 'success') ? 'success' : 'err';
        $msg  = (string)$f['msg'];
        ?>

<!-- Script que decide o tipo e o texto da mensagem com base no sucesso do envio -->
<script>
  window.addEventListener('DOMContentLoaded', function () {
    const kind = <?= json_encode($kind) ?>;
    const msg  = <?= json_encode($msg) ?>;
    if (typeof window.mlToast === 'function') {
      window.mlToast(kind, msg, 3600);
    } else {
      alert(msg);
    }
  });
</script>

<?php endif; ?>

        <br><br>

        <!-- Envia o email e a mensagem, exibindo a mensagem de sucesso/erro em seguida -->
        <main class="main-content">
            <div class="form-container">
                <form method="post">
                    <h2>Suporte Moonlight</h2>
                    <br>
                    <h5>Precisa de ajuda? Contate-nos!</h5>
                    <br>

                    <div class="form-group">
                        <label for="email">Preencha seu email para contato:</label>
                        <input id="email" name="email" placeholder="Informe seu email">
                    </div>

                    <div class="form-group">
                        <label for="mensagem">Conte seu problema abaixo:</label>
                        <textarea id="mensagem" name="mensagem" placeholder="Informe o problema"></textarea>
                    </div>

                    <button type="submit">Enviar</button>
                </form>
            </div>
        </main>
    </div>

    <footer>
        <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
    </footer>

    <style>
        .main-content {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex: 1;
            padding: 20px 0;
        }

        h2 {
            font-weight: bold !important;
        }

        .form-container {
            background: linear-gradient(to bottom, #202330ff 0%, #272b3fff 50%);
            padding: 30px;
            border-radius: 10px;
            width: 500px;
            height: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, .3);
            text-align: center;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .form-group label {
            color: #fff;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .form-group input {
            background: rgba(255, 255, 255, .1);
            border-radius: 4px;
            border: none;
            outline: none;
            width: 100%;
            color: #fff;
            padding: 8px 10px;
            height: 45px;
        }

        .form-group input::placeholder {
            color: rgba(206, 196, 196, .56);
            font-size: 14px;
        }

        textarea {
            background: rgba(255, 255, 255, .1);
            border-radius: 4px;
            border: none;
            width: 100%;
            height: 150px;
            color: #fff;
            padding: 10px;
            outline: none;
            resize: none !important;
        }

        textarea::placeholder {
            color: rgba(206, 196, 196, .56);
            font-size: 14px;
        }

        .flash {
            position: fixed;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1080;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 600;
            box-shadow: 0 10px 28px rgba(0, 0, 0, .35);
            transition: opacity .35s ease, transform .35s ease;
        }

        .flash-ico {
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, .15);
            font-weight: 700;
        }

        .flash-success {
            background: #1c3824 !important;
            border: 1px solid #2e7b47 !important;
            color: #d3f0df !important;
        }

        .flash-danger {
            background: #3a2020 !important;
            border: 1px solid #a54343 !important;
            color: #ffd9d9 !important;
        }
    </style>

</body>
</html>