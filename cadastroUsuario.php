<?php
session_start();
require_once 'include/conexao.php';

$erro = '';

// Validação do cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $passRaw  = (string) ($_POST['password'] ?? '');

    // Todos os campos devem estar preenchidos
    if (empty($_POST['mais14'])) {
        $erro = 'Você precisa declarar que tem 14 anos ou mais para prosseguir.';
    } elseif ($username === '' || $email === '' || $passRaw === '') {
        $erro = 'Preencha todos os campos.';
    } else {
        $chk = $conn->prepare("SELECT 1 FROM usuarios WHERE email = ? OR usuario = ? LIMIT 1");
        $chk->bind_param('ss', $email, $username);
        $chk->execute();

        // Impede duplicidade
        if ($chk->get_result()->num_rows) {
            $erro = 'E-mail ou usuário já cadastrado.';
        } 
        
        // "Criptografa" a senha no banco + define o papel + insere dados no banco
        else {
            $hash = password_hash($passRaw, PASSWORD_DEFAULT);
            $role = 'user';
            $ins  = $conn->prepare("INSERT INTO usuarios (usuario, senha, email, role) VALUES (?, ?, ?, ?)");
            $ins->bind_param("ssss", $username, $hash, $email, $role);
            $ins->execute();

            // Apaga avatares com o mesmo ID do recém cadastrado, se houver
            $newId     = $conn->insert_id;
            $avatarDir = __DIR__ . '/uploads/avatars';
            if (is_dir($avatarDir)) {
                foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
                    $p = "{$avatarDir}/{$newId}.{$ext}";
                    if (is_file($p)) {
                        @unlink($p);
                    }
                }
            }

            // Mensagem de sucesso caso cadastro bem sucedido
            $_SESSION['flash'] = [
                'tipo' => 'success',
                'msg'  => 'Cadastro realizado com sucesso! Faça login para continuar.'
            ];
            header('Location: login.php');
            exit;
        }
    }
}
?>


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
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        text-align: center;
    }

    .form-container .form-control {
        background-color: rgba(255, 255, 255, 0.10) !important;
        color: #fff !important;
        outline: none !important;
        box-shadow: none !important;
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
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        border: none;
        width: 100%;
        height: 45px;
        color: #fff;
        padding: 10px;
    }

    .form-check-input {
        width: 18px !important;
        height: 18px !important;
        padding: 0 !important;
        border-radius: 4px;
        background: rgba(206, 196, 196, 0.56);
        box-shadow: none !important;
    }

    .form-check-input:checked {
        background-color: #7aa3ef !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23ffffff' stroke-width='3' d='M3.5 8.5l2.5 2.5 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 12px 12px;
    }

    .form-group input::placeholder {
        color: rgba(206, 196, 196, 0.56);
        font-size: 14px;
    }

    .btn-detail {
        width: 100px;
    }

    .input-wrap {
        position: relative;
    }

    .input-wrap .form-control {
        padding-right: 234px;
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
        cursor: pointer;
    }

    .form-check.check-agree {
        display: grid;
        grid-template-columns: 1.2em 1fr;
        column-gap: .6em;
        align-items: start;
        padding-left: 0;
        font-size: 14px;
    }

    .form-check.check-agree .form-check-input {
        float: none;
        margin: .15em 0 0 0;
    }

    .form-check.check-agree .form-check-label {
        display: block;
        margin: 0;
        line-height: 1.4;
        text-align: left;
    }

    .mt-3 {
        color: #fff;
        font-size: 0.9rem;
    }

    .mt-3 a {
        color: #7aa3efff;
        text-decoration: none;
    }

    .mt-3 a:hover {
        text-decoration: underline;
    }

    .link-termos {
        color: #7aa3efff;
        text-decoration: none;
        font-weight: 600;
    }

    .link-termos:hover {
        text-decoration: underline;
    }
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <link rel="stylesheet" href="css/styles2.css">
    <link rel="stylesheet" href="css/stylesNav.css">

    <title>Cadastro</title>
</head>

<body>
    <div class="content">

        <?php require_once 'include/navbar.php' ?>

        <!-- Mensagem de erro caso haja algum problema no envio do formulário, ao recarregar a página -->
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

        <main class="main-content">
            <div class="form-container">

                <!-- Formulário de cadastro -->
                <form method="post">
                    <h2>Cadastro</h2>
                    <br>

                    <div class="form-group">
                        <label>USUÁRIO:</label>
                        <input type="text" name="username" placeholder="Digite seu usuário" class="form-control"
                            required>
                    </div>

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

                    <!-- Checkbox de confirmação de idade + termos -->
                    <div class="form-group">
                        <div class="form-check check-agree">
                            <input class="form-check-input" type="checkbox" id="mais14" name="mais14" value="1"
                                required>
                            <label class="form-check-label" for="mais14">
                                Tenho <strong>14 anos ou mais</strong> e concordo com os
                                <a href="#" class="link-termos" data-bs-toggle="modal" data-bs-target="#modalTermos">
                                     termos e a política de privacidade
                                </a>.
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-detail">Cadastrar</button>
                    <br><br>
                    <p class="mt-3">Já tem conta? <a href="login.php">Faça login aqui</a></p>
                </form>
            </div>
        </main>
    </div>

    <!-- Modal dos termos e política de privacidade -->
    <div class="modal fade" id="modalTermos" tabindex="-1" aria-labelledby="modalTermosLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="background:#202330ff;border:1px solid #4e5c72;color:#fff;">
                <div class="modal-header" style="border-bottom:1px solid #4e5c72;">
                    <h5 class="modal-title" id="modalTermosLabel">Termos de Uso e Política de Privacidade</h5>
                </div>

                <div class="modal-body">
                    <h6><strong>1. Informações Gerais</strong></h6>
                    <p>
                        A Moonlight é uma plataforma de distribuição digital de jogos. Ao criar uma conta,
                        você declara que leu e concorda com estes Termos de Uso e com a Política de Privacidade.
                    </p>

                    <h6><strong>2. Dados Coletados</strong></h6>
                    <p>Podemos coletar e tratar os seguintes dados pessoais:</p>
                    <ul>
                        <li>Dados de cadastro: nome de usuário, e-mail, senha, CPF e data de nascimento;</li>
                        <li>Dados de uso: endereço IP, datas e horários de acesso, páginas visitadas;</li>
                        <li>Dados de transações: histórico de compras, forma de pagamento e valores, sempre por meio de
                            intermediadores de pagamento.</li>
                    </ul>
                    <p>
                        As senhas são armazenadas de forma criptografada. Não armazenamos o número completo do cartão
                        nem códigos de segurança (CVV).
                    </p>

                    <h6><strong>3. Finalidade do Uso dos Dados</strong></h6>
                    <p>Seus dados são utilizados para:</p>
                    <ul>
                        <li>Criar e gerenciar sua conta na Moonlight;</li>
                        <li>Processar compras de jogos e liberar acesso à sua biblioteca;</li>
                        <li>Cumprir obrigações legais e fiscais, como emissão de notas;</li>
                        <li>Manter a segurança da conta e da plataforma, prevenindo fraudes;</li>
                        <li>Enviar comunicações importantes sobre sua conta e, quando autorizado, novidades e ofertas.
                        </li>
                    </ul>

                    <h6><strong>4. Compartilhamento de Dados</strong></h6>
                    <p>Seus dados podem ser compartilhados com:</p>
                    <ul>
                        <li>Intermediadores de pagamento e instituições financeiras, para processar suas compras;</li>
                        <li>Provedores de hospedagem e serviços de tecnologia utilizados pela Moonlight;</li>
                        <li>Autoridades públicas, quando houver obrigação legal ou ordem judicial.</li>
                    </ul>
                    <p>Compartilhamos sempre o mínimo de informações necessário para cada finalidade.</p>

                    <h6><strong>5. Direitos do Usuário</strong></h6>
                    <p>Você pode, a qualquer momento, nos solicitar:</p>
                    <ul>
                        <li>Confirmação sobre a existência de tratamento de dados pessoais;</li>
                        <li>Acesso, correção ou atualização dos seus dados;</li>
                        <li>Anonimização ou exclusão de dados desnecessários, quando permitido em lei;</li>
                        <li>Revogação de consentimentos concedidos.</li>
                    </ul>

                    <h6><strong>6. Uso da Plataforma</strong></h6>
                    <p>Ao utilizar a Moonlight, você se compromete a:</p>
                    <ul>
                        <li>Não compartilhar seu login e senha com terceiros;</li>
                        <li>Não utilizar a plataforma para atividades ilícitas ou que infrinjam direitos de terceiros;
                        </li>
                        <li>Respeitar os direitos autorais dos desenvolvedores e editoras dos jogos adquiridos;</li>
                        <li>Não burlar sistemas de segurança ou mecanismos de proteção de conteúdo.</li>
                    </ul>

                    <h6><strong>7. Propriedade Intelectual</strong></h6>
                    <p>
                        A marca Moonlight, o layout do site e os elementos visuais da plataforma são protegidos por
                        direitos de propriedade intelectual. Os jogos e artes exibidos pertencem aos respectivos
                        desenvolvedores e editoras. Ao comprar um jogo, você recebe uma licença de uso, e não a
                        propriedade total da obra.
                    </p>

                    <h6><strong>8. Segurança e Armazenamento</strong></h6>
                    <p>
                        Empregamos medidas razoáveis de segurança para proteger seus dados, mas nenhum sistema é
                        totalmente imune. Em caso de incidente relevante de segurança, adotaremos as providências
                        cabíveis, incluindo comunicação aos usuários afetados, quando necessário.
                    </p>

                    <h6><strong>9. Atualizações dos Termos</strong></h6>
                    <p>
                        Estes Termos de Uso e a Política de Privacidade podem ser atualizados periodicamente.
                        A versão mais recente estará sempre disponível no site da Moonlight. O uso continuado da
                        plataforma após alterações significa que você concorda com os novos termos.
                    </p>

                    <h6><strong>10. Contato</strong></h6>
                    <p>
                        Em caso de dúvidas sobre estes termos ou sobre o tratamento de dados pessoais, você pode
                        entrar em contato pelo e-mail: <strong>suporte@moonlight.com</strong>.
                    </p>
                </div>

                <div class="modal-footer" style="border-top:1px solid #4e5c72;">
                    <button type="button" class="btn-detail" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
    </footer>

    <script>
        // Olhinho para esconder/ver a senha
        document.querySelectorAll('.toggle-password').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const input = this.closest('.input-wrap')?.querySelector('input');
                const icon = this.querySelector('i');
                if (!input) return;
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                if (icon) { icon.classList.toggle('bi-eye', showing); icon.classList.toggle('bi-eye-slash', !showing); }
                this.setAttribute('aria-pressed', String(!showing));
            });
        });

        // Não permite o cadastro se o usuário não marcar a checkbox
        (function () {
            const form = document.querySelector('form');
            const chk  = document.getElementById('mais14');
            if (!form || !chk) return;

            form.addEventListener('submit', (e) => {
                if (!chk.checked) {
                    e.preventDefault();
                    if (window.mlToast) {
                        window.mlToast('err', 'Você precisa declarar que tem 14 anos ou mais para prosseguir.');
                    } else {
                        alert('Você precisa declarar que tem 14 anos ou mais para prosseguir.');
                    }
                    chk.focus();
                }
            });
        })();

    </script>

</body>
</html>