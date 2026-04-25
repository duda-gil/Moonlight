<?php
require_once 'include/conexao.php';
require_once __DIR__ . '/include/biblioteca.php';

if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();

// Normaliza id de sessão
$userId = $_SESSION['id']
    ?? $_SESSION['user_id']
    ?? ($_SESSION['user']['id'] ?? null)
    ?? ($_SESSION['usuario']['id'] ?? null);

if (!$userId) {
    header('Location: login.php');
    exit;
}
$userId = (int) $userId;

// Garante que o usuário existe e está ativo no banco
$st = $conn->prepare("SELECT id, usuario, email, role, status FROM usuarios WHERE id = ? LIMIT 1");
$st->bind_param('i', $userId);
$st->execute();
$user = $st->get_result()->fetch_assoc();

// Caso conta removida ou inativa, encerra a sessão e volta ao login
if (!$user || strcasecmp((string) ($user['status'] ?? 'ativo'), 'ativo') !== 0) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: login.php');
    exit;
}

// Token de segurança para exclusão da conta
if (empty($_SESSION['csrf_delete'])) {
    $_SESSION['csrf_delete'] = bin2hex(random_bytes(16));
}

// Diretório onde ficam salvos os avatares 
$avatarDir = __DIR__ . '/uploads/avatars';

// Exclusão da conta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_account') {

    // Compara o token gerado com o que está na sessão
    $csrf = (string) ($_POST['csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf_delete'] ?? '', $csrf)) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Sessão expirada. Recarregue a página.'];
        header('Location: perfil.php');
        exit;
    }

    // Não permite exclusão de contas administrativas
    $isAdmin = strcasecmp((string) ($user['role'] ?? ''), 'admin') === 0
        || strcasecmp((string) ($user['email'] ?? ''), 'adm@adm.com') === 0; // mesma regra do login
    if ($isAdmin) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Contas administrativas não podem ser excluídas.'];
        header('Location: perfil.php');
        exit;
    }

    // Apaga arquivos de avatares salvos quando uma conta é excluída
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $file = "{$avatarDir}/{$userId}.{$ext}";
        if (is_file($file)){
            @unlink($file);
        }
    }

    // Inativa a conta mantendo usuário e e-mail para fins de relatório
    $upd = $conn->prepare("UPDATE usuarios SET status = 'inativo' WHERE id = ? LIMIT 1");
    $upd->bind_param('i', $userId);
    $upd->execute();

    // Encerra a sessão e volta para a página principal
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: index.php');
    exit;
}


// Carrega os dados do usuário no perfil
$st = $conn->prepare('SELECT id, usuario, email FROM usuarios WHERE id = ? LIMIT 1');
$st->bind_param('i', $userId);
$st->execute();
$user = $st->get_result()->fetch_assoc() ?: ['usuario' => 'Usuário', 'email' => ''];

// Upload de avatar
$avatarDir = __DIR__ . '/uploads/avatars';
$avatarUrlBase = 'uploads/avatars';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_avatar') {
    if (!empty($_FILES['avatar']['tmp_name'])) {
        if (!is_dir($avatarDir))
            @mkdir($avatarDir, 0775, true);

        // Aceita somente os formatos JPEG, PNG e WEBP
        $info = @getimagesize($_FILES['avatar']['tmp_name']);
        $mime = $info['mime'] ?? '';
        $ok = in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);

        if ($ok) {
            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'png'
            };
            $dest = "{$avatarDir}/{$userId}.{$ext}";

            // Apaga avatares antigos do mesmo usuário
            foreach (['jpg', 'jpeg', 'png', 'webp'] as $e) {
                $try = "{$avatarDir}/{$userId}.{$e}";
                if ($e !== $ext && file_exists($try))
                    @unlink($try);
            }

            // Mensagens de sucesso/erro conforme o resultado
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Foto atualizada!'];
                header('Location: perfil.php');
                exit;
            } else {
                $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Falha ao enviar a imagem.'];
            }
        } else {
            $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Formato inválido. Envie JPG, PNG ou WEBP.'];
        }
    }
}

// Busca o caminho do avatar para preenchimento do perfil
function findAvatarPath(int $uid, string $baseDir, string $baseUrl): ?string
{
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $fs = "{$baseDir}/{$uid}.{$ext}";
        if (file_exists($fs))
            return "{$baseUrl}/{$uid}.{$ext}";
    }
    return null;
}
$avatarUrl = findAvatarPath($userId, $avatarDir, $avatarUrlBase);


// Busca os jogos da biblioteca do usuário
function fetchUserGames(mysqli $conn, int $uid): array
{
    $sql = "
    
        SELECT j.id,
               j.nome,
               COALESCE(NULLIF(TRIM(j.url_banner),''), j.url_1, j.url_2, j.url_3, j.url_4, j.url_5) AS banner,
               t.ultima_compra
        FROM jogos j
        JOIN (
            SELECT jogo_id, MAX(compra_data) AS ultima_compra
            FROM biblioteca
            WHERE user_id = ?
            GROUP BY jogo_id
        ) t ON t.jogo_id = j.id
        ORDER BY t.ultima_compra DESC, j.nome ASC
    ";
    $st = $conn->prepare($sql);
    $st->bind_param('i', $uid);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];

    // Remove jogos duplicados guardados em $meusJogos
    $uniq = [];
    foreach ($rows as $r)
        $uniq[$r['id']] = $r;
    return array_values($uniq);
}
$meusJogos = fetchUserGames($conn, $userId);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Perfil</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/styles2.css">
    <link rel="stylesheet" href="css/stylesNav.css">

    <style>
        .page-wrap {
            padding: 30px 20px;
        }

        .profile-card {
            max-width: 1100px;
            margin: 0 auto;
            background: linear-gradient(to bottom, #202330ff 0%, #272b3fff 50%);
            border-radius: 22px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, .35);
            padding: 28px;
            color: #fff;
        }

        .pc-title {
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: .2px;
        }

        .pc-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .pc-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }

        .pc-actions form {
            margin: 0;
        }

        .btn-delete {
            background: #ca4242ff;
            height: 35px;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 8px;
        }

        .btn-delete:hover {
            background: #a72525ff;
            color: #ffecec;
        }

        .btn-delete:focus-visible {
            outline: 2px solid #ffb4b4;
            outline-offset: 2px;
        }

        .btn-logout {
            background: #7aa3ef;
            height: 35px;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: #345aa1;
            color: #d5e3ff;
        }

        .info-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 24px;
            align-items: center;
            margin-top: 10px;
            margin-bottom: 16px;
        }

        .avatar {
            width: 140px;
            height: 140px;
            border-radius: 999px;
            background: #0d1320;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .08);
            overflow: hidden;
            position: relative;
            display: inline-block;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .avatar-edit {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 36px;
            height: 36px;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 18px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .avatar:hover .avatar-edit {
            opacity: 1;
        }

        .avatar-edit:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }

        .avatar-edit:focus-visible {
            outline: 2px solid #7aa3ef;
            outline-offset: 2px;
        }

        .user-lines .name {
            font-size: 1.35rem;
            font-weight: 700;
        }

        .user-lines .email {
            color: #c9d3ee;
            margin-top: 4px;
        }

        .divider {
            height: 1px;
            background: rgba(255, 255, 255, .08);
            margin: 14px 0 18px;
        }

        .sec-title {
            font-weight: 700;
            margin-bottom: 8px;
        }

        .sec-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .sec-title {
            margin: 0;
        }

        .bib-search {
            position: relative;
        }

        .bib-search input {
            width: clamp(220px, 32vw, 300px);
            height: 40px;
            padding: 10px 12px 10px 35px;
            border-radius: 6px;
            border: none;
            background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23dbe6ff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='11' cy='11' r='8'/><line x1='21' y1='21' x2='16.65' y2='16.65'/></svg>") no-repeat 10px 50%, #1b2133;
            color: #eaf1ff;
            outline: none !important;
        }

        .bib-search input::placeholder {
            color: #fdfaf194;
            font-size: 14px;
        }

        .bib-search input:focus {
            border-color: #7aa3ef;

        }

        @media (max-width:560px) {
            .sec-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .bib-search input {
                width: 100%;
            }
        }

        .game-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .empty {
            background: #1b2133;
            border: 1px dashed rgba(255, 255, 255, .18);
            border-radius: 12px;
            padding: 18px;
            color: #cdd8ff;
        }

        .empty a {
            color: #7aa3ef;
            text-decoration: none;
        }

        .empty a:hover {
            color: #b1d2eb;
        }

        @media (max-width: 900px) {
            .info-row {
                grid-template-columns: 120px 1fr;
            }

            .avatar {
                width: 120px;
                height: 120px;
            }

            .games-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 560px) {
            .info-row {
                grid-template-columns: 1fr;
                justify-items: center;
                text-align: center;
            }

            .pc-head {
                flex-direction: column;
                gap: 10px;
            }

            .games-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="content">

        <?php require_once 'include/navbar.php'; ?>

        <br><br>

        <div class="page-wrap">
            <div class="profile-card">

                <!-- Botão de logout + form de exclusão com o token gerado no início -->
                <div class="pc-head">
                    <div class="pc-title">Perfil</div>

                    <div class="pc-actions">
                        <a class="btn-logout" href="include/logout.php">Sair</a>

                        <form id="delAccForm" method="post" action="perfil.php">
                            <input type="hidden" name="action" value="delete_account">
                            <!-- importante para não dar 'Sessão expirada' -->
                            <input type="hidden" name="csrf"
                                value="<?= htmlspecialchars($_SESSION['csrf_delete'] ?? '') ?>">
                            <button type="submit" class="btn-delete" id="btnDeleteAccount">Desativar conta</button>
                        </form>
                    </div>
                </div>

                <!-- Mostra a mensagem correta guardada na sessão -->
                <?php if (!empty($_SESSION['flash'])):
                    $tipo = $_SESSION['flash']['tipo'] ?? 'success';
                    $msg = trim($_SESSION['flash']['msg'] ?? '');
                    unset($_SESSION['flash']); ?>
                    <div class="alert alert-<?= $tipo === 'success' ? 'success' : 'danger' ?> py-2">
                        <?= htmlspecialchars($msg) ?>
                    </div>
                <?php endif; ?>

                <!-- Avatar e alterações + informações do usuário no perfil -->
                <div class="info-row">
                    <div class="avatar <?= $avatarUrl ? '' : 'avatar--placeholder' ?>">
                        <?php if ($avatarUrl): ?>
                            <img src="<?= htmlspecialchars($avatarUrl) ?>?v=<?= time() ?>" alt="Avatar">
                        <?php else: ?>
                            <span><?= strtoupper(mb_substr($user['usuario'] ?? 'U', 0, 1)) ?></span>
                        <?php endif; ?>

                        <label for="fileAvatar" class="avatar-edit" title="Alterar foto">
                            <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                            <span class="visually-hidden">Alterar foto</span>
                        </label>
                    </div>

                    <div class="user-lines">
                        <div class="name"><?= htmlspecialchars($user['usuario'] ?? '') ?></div>
                        <div class="email"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Barra de pesquisa para filtrar jogos da biblioteca -->
                <div class="sec-bar">
                    <div class="sec-title">Meus jogos</div>
                    <?php if (!empty($meusJogos)): ?>
                        <div class="bib-search">
                            <input id="bib-search" type="search" placeholder="Buscar na biblioteca">
                        </div>
                    <?php endif; ?>
                </div>

                <br>

                <!-- Mostra os cards dos jogos, ou então o link para a loja caso biblioteca vazia -->
                <?php if (!empty($meusJogos)): ?>
                    <div class="game-grid">
                        <?php foreach ($meusJogos as $g): ?>
                            <?php
                            $gid = (int) $g['id'];
                            $nome = htmlspecialchars($g['nome'] ?? 'Jogo', ENT_QUOTES, 'UTF-8');
                            $banner = htmlspecialchars($g['banner'] ?? 'placeholder.png', ENT_QUOTES, 'UTF-8');
                            ?>
                            <a class="game-card" href="jogo.php?id=<?= $gid ?>">
                                <div class="game-thumb">
                                    <img src="<?= $banner ?>" alt="<?= $nome ?>">
                                </div>
                                <div class="game-info">
                                    <h4 title="<?= $nome ?>"><?= $nome ?></h4>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>

                    <div class="empty">
                        Você ainda não tem jogos aqui. <a href="index.php">Explorar a loja</a>
                    </div>
                <?php endif; ?>

                <!-- Form oculto de envio de avatar -->
                <form id="avatarForm" method="post" enctype="multipart/form-data" style="display:none">
                    <input type="hidden" name="action" value="upload_avatar">
                    <input id="fileAvatar" type="file" name="avatar" accept="image/png,image/jpeg,image/webp">
                </form>
            </div>
        </div>

    </div>

    <footer>
        <p style="text-align:center; color:#fff; margin:20px 0;">&copy; 2025 Moonlight. Todos os direitos reservados.
        </p>
    </footer>


    <!-- Script de envio automático de avatar -->
    <script>
        document.getElementById('fileAvatar')?.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                document.getElementById('avatarForm').submit();
            }
        });
    </script>

    <!-- Filtro da barra de pesquisa da biblioteca -->
    <script>
        (function () {
            const input = document.getElementById('bib-search');
            if (!input) return;

            const grid = document.querySelector('.game-grid');
            const cards = grid ? Array.from(grid.querySelectorAll('.game-card')) : [];

            // Normaliza texto sem acento
            const norm = s => (s || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

            function applyFilter() {
                const q = norm(input.value);
                cards.forEach(card => {
                    const title = card.querySelector('h4')?.textContent || '';
                    card.style.display = !q || norm(title).includes(q) ? '' : 'none';
                });
            }

            // Mostra os cards conforme o título bates com o digitado
            input.addEventListener('input', applyFilter);
            input.addEventListener('search', applyFilter);

            // ESC limpa a busca
            input.addEventListener('keyup', (e) => {
                if (e.key === 'Escape') { input.value = ''; applyFilter(); }
            });
        })();
    </script>

    <!-- Script do confirm de exclusão de conta -->
    <script>
        document.getElementById('delAccForm')?.addEventListener('submit', function (e) {
            if (!confirm('Tem certeza que deseja inativar sua conta? Você não poderá acessar até solicitar a reativação ao suporte.')) {
                e.preventDefault();
            }
        });
    </script>

</body>
</html>