<?php

if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();

// Somente adm tem acesso
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

// Só entra se a página foi acessada via envio de formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include('include/conexao.php');

    // Lê os dados enviados pelo formulário
    $status = $_POST['status'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];

    // Comando para inserir uma nova linha na tabela
    $sql = 'INSERT INTO categorias (status, nome, descricao) VALUES (?,?,?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $status, $nome, $descricao);
    $ok = $stmt->execute();

    // Mensagem de sucesso/erro
    $_SESSION['flash'] = [
        'tipo' => $ok ? 'success' : 'danger',
        'msg' => $ok ? 'Categoria cadastrada com sucesso!' : ('Falha ao cadastrar: ' . $stmt->error)
    ];
    header('Location: adm.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesNav.css">

    <title>Cadastro Categorias</title>
    <style>
        .main-content {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex: 1;
            padding: 20px 0 10px 0;
        }

        h1 {
            width: 100%;
            max-width: 1600px;
            color: #fff;
            font-size: 2rem;
            font-weight: bold;
        }

        .form-container {
            background: linear-gradient(to bottom, #202330ff 0%, #272b3fff 50%);
            padding: 30px;
            border-radius: 10px;
            width: 700px;
            height: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .form-container .form-inline {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 7px;
        }

        textarea {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            border: none;
            width: 100%;
            height: 100px;
            color: #fff;
            padding: 10px;
            outline: none;
            resize: none !important;
        }

        textarea::placeholder {
            color: rgba(206, 196, 196, 0.56);
            font-size: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 14px;
            margin-bottom: 6px;
            color: #fff;
        }

        .form-group input {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 4px;
            padding: 8px 10px;
            outline: none;
            width: 100%;
            height: 45px;
            color: #fff;
        }

        .form-group input::placeholder {
            color: rgba(206, 196, 196, 0.56);
            font-size: 14px;
        }

        .radio-group {
            display: flex;
            align-items: center;
            gap: 20px;
            border-radius: 4px;
            padding: 8px 10px;
            width: 100%;
            height: 35px;
        }

        .radio-group input[type="radio"] {
            margin-right: 6px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            font-size: 15px;
            color: #fff;
        }

        .btn-detail {
            background: #7aa3ef;
            border-radius: 4px;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
            color: #fff;
            font-size: 14px;
            width: 200px;
            align-self: center;
        }

        .btn-detail:hover {
            background: #345aa1ff;
            color: rgb(126, 171, 255);
            transition: color 0.3s;
        }

        .form-container .btn-detail+.btn-detail {
            margin-top: 9px;
        }
    </style>
</head>

<body>

    <div class="content">

        <?php $NAV_CONTEXT = 'admin';
        require_once 'include/navbar.php'; ?>

        <br><br>

        <div class="main-content">
            <div class="form-container">

                <!-- Formulário para preenchimento e cadastro -->
                <form method="post">
                    <h1> Cadastro de Categorias </h1>
                    <br>

                    <div class="form-group form-inline">
                        <label for="status">Status:</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="status" value="ativo" required>
                                Ativo
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="status" value="inativo" required>
                                Inativo
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nome">Informe o nome da categoria:</label>
                        <input type="text" name="nome" placeholder="Digite o nome da categoria" required>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Disponibilize uma descrição para a categoria:</label>
                        <textarea name="descricao" rows="3" placeholder="Digite a descrição" required></textarea>
                    </div>

                    <button type="submit" class="btn-detail">Cadastrar categoria</button>
                    <button type="reset" class="btn-detail">Limpar</button>

                </form>
            </div>
        </div>

        <br><br>

        <footer>
            <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
        </footer>
    </div>

</body>
</html>