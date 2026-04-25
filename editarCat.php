<?php
require_once 'include/verifica.php';
require_once 'include/conexao.php';

// Somente adm tem acesso
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: adm.php');
    exit;
}

// Busca e filtra o ID da categoria
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: adm.php');
    exit;
}

// Busca a categoria e informações
$st = $conn->prepare('SELECT id, status, nome, descricao FROM categorias WHERE id=? LIMIT 1');
$st->bind_param('i', $id);
$st->execute();
$categoria = $st->get_result()->fetch_assoc();

if (!$categoria) {
    header('Location: adm.php');
    exit;
}

// Lê os campos do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = trim($_POST['status'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    // Faz o update
    $sql = 'UPDATE categorias SET status=?, nome=?, descricao=? WHERE id=?';
    $upd = $conn->prepare($sql);
    $upd->bind_param('sssi', $status, $nome, $descricao, $id);
    $ok = $upd->execute();

    $_SESSION['flash'] = [
        'tipo' => $ok ? 'success' : 'danger',
        'msg' => $ok ? 'Categoria atualizada com sucesso!' : ('Falha ao atualizar: ' . $upd->error)
    ];
    header('Location: adm.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Editar Categoria</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesNav.css">

</head>

<body>
    <?php $NAV_CONTEXT = 'admin'; require_once 'include/navbar.php'; ?>

    <div class="content">
        <br><br>
        <div class="main-content">
            <div class="form-container">

                <h1>Editar Categoria</h1>
                <br>

                <!-- Puxa a categoria a ser editada pelo ID -->
                <form method="post" action="editarCat.php?id=<?= $id ?>">

                    <!-- Formulário de edição -->
                    <div class="form-group">
                        <label>Status:</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="status" value="ativo" <?= (($categoria['status'] ?? '') === 'ativo') ? 'checked' : '' ?> required> Ativo
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="status" value="inativo" <?= (($categoria['status'] ?? '') === 'inativo') ? 'checked' : '' ?> required> Inativo
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nome da categoria:</label>
                        <input type="text" name="nome" placeholder="Digite o nome da categoria"
                            value="<?= htmlspecialchars($categoria['nome']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Descrição:</label>
                        <textarea name="descricao" rows="3" placeholder="Digite sua descrição"
                            required><?= htmlspecialchars($categoria['descricao'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn-detail">Salvar alterações</button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p style="text-align:center; color:#fff; margin:20px 0;">&copy; 2025 Moonlight. Todos os direitos reservados.
        </p>
    </footer>

    <style>
        .content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

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
            box-shadow: 0 4px 6px rgba(0, 0, 0, .3);
            text-align: center;
            color: #fff;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .form-group label {
            font-size: 14px;
            margin-bottom: 6px;
            color: #fff;
        }

        .form-group input {
            background: rgba(255, 255, 255, .1);
            border: none;
            border-radius: 4px;
            padding: 8px 10px;
            outline: none;
            width: 100%;
            height: 35px;
            color: #fff;
        }

        .form-group input::placeholder {
            color: rgba(206, 196, 196, .56);
            font-size: 14px;
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
            width: 220px;
            align-self: center;
        }

        .btn-detail:hover {
            background: #345aa1ff;
            color: rgb(126, 171, 255);
            transition: color .3s;
        }
    </style>

</body>
</html>