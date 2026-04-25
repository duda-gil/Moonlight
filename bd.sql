-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25/04/2026 às 20:50
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `bd`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `biblioteca`
--

CREATE TABLE `biblioteca` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jogo_id` int(11) NOT NULL,
  `chave` char(23) NOT NULL,
  `compra_data` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `biblioteca`
--

INSERT INTO `biblioteca` (`id`, `user_id`, `jogo_id`, `chave`, `compra_data`) VALUES
(31, 1, 18, '2YU5C-A6NBX-TU6EY-TPVCD', '2025-11-24 00:45:20'),
(32, 1, 18, 'LP49P-RAHEY-VQUFF-EA8EJ', '2025-11-24 00:45:20'),
(33, 1, 9, 'H2H2G-YWD5D-EN2Z8-PXZWA', '2025-11-24 00:47:28'),
(34, 1, 20, '98WF4-HQ3CX-HBFTH-PW9ST', '2025-11-25 00:51:43'),
(35, 1, 17, 'ADJ3B-LH38N-Z4LJ5-SWTUM', '2025-11-25 00:51:43'),
(36, 1, 21, 'EASZR-A2YNF-Y7U58-KCZEV', '2025-11-25 00:51:43'),
(37, 9, 18, 'NXGTP-DDYKM-FCKKT-693NJ', '2025-11-25 00:55:57'),
(38, 9, 9, 'PWLRW-B9GGF-CYPV8-FFCBX', '2025-11-25 00:55:57'),
(39, 9, 1, 'L39DW-R2JM9-CZDQL-LY5M8', '2025-11-25 00:55:57'),
(40, 1, 14, 'CNAHX-65TFT-GYJS7-GGQ4E', '2025-11-25 00:57:08'),
(41, 1, 23, 'F8UD8-CH9ZP-MLJLM-WCBCN', '2025-11-26 01:51:35'),
(42, 1, 15, 'B8VU7-QZWW8-UN5SN-H8GAY', '2025-11-26 01:51:35'),
(44, 1, 3, 'RMXBV-PPGPB-8AF4E-27KGA', '2025-12-01 17:37:35'),
(45, 28, 17, 'QM8AP-8G5XR-28AS7-7NJCU', '2025-12-02 14:20:38'),
(46, 28, 17, 'ZAKT3-2HZM5-G4ZYT-2CDQZ', '2025-12-02 14:20:38'),
(47, 28, 21, 'P4WCE-CAB94-WGBZM-67VJ7', '2025-12-02 14:20:38'),
(48, 28, 13, '7JWLL-S6ZDF-NJGFW-SPCKM', '2025-12-02 14:26:25'),
(49, 28, 11, 'K5J8B-EMEEV-2DHZ3-ZUKME', '2025-12-02 14:26:25'),
(50, 28, 8, '6RR2H-HF27W-EKMKS-WQD5H', '2025-12-02 14:26:25'),
(52, 28, 4, '6YRYP-C8UKD-HLFF9-62AKR', '2025-12-02 18:49:24'),
(56, 31, 19, 'TGCQ5-RXQFQ-ML9E8-EV5T8', '2025-12-02 21:24:15'),
(57, 31, 16, 'A33NB-JX9K7-K698C-XN438', '2025-12-02 21:24:15'),
(58, 31, 13, 'NKHZ7-VCG6X-SNLXU-UCXHH', '2025-12-02 21:25:09'),
(59, 9, 20, 'ZZX8B-AA77G-VLXZS-RFFRN', '2025-12-03 00:51:44'),
(60, 9, 8, '5JC4J-D669T-VYYSC-WB6MC', '2025-12-03 00:51:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(10) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` varchar(300) NOT NULL,
  `status` enum('ativo','inativo') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `descricao`, `status`) VALUES
(1, 'Um jogador', 'Campanhas criadas para você mergulhar no seu ritmo, sem distrações. Ideal para focar na história, no mundo e no seu progresso pessoal.', 'ativo'),
(2, 'Multijogador', 'Entre com a galera em modos cooperativos ou competitivos e crie momentos épicos a cada partida. Prove sua habilidade, suba no ranking e colecione vitórias.', 'ativo'),
(3, 'Ritmo', 'Sinta a batida e execute ações no timing perfeito para pontuar alto. Mecânicas musicais e visuais pulsantes transformam cada fase em um show interativo.', 'ativo'),
(4, 'Aventura', 'Explore cenários, descubra segredos e resolva pequenos desafios enquanto a história te conduz. Perfeito para quem busca jornadas memoráveis e cheias de descobertas.', 'ativo'),
(5, 'Ação', 'Combates intensos, reflexos rápidos e set pieces de tirar o fôlego. Entre no fluxo, encadeie golpes e domine cada arena.', 'ativo'),
(6, 'Narrativa', 'Histórias profundas, personagens inesquecíveis e escolhas que realmente impactam. Viva tramas ricas em emoção e decisões com consequências.', 'ativo'),
(7, 'Atmosfético', 'Ambientes que falam por si com arte, iluminação e trilha sonora imersivas. Uma experiência sensorial pensada para arrepiar do começo ao fim.', 'ativo'),
(8, 'Terror', 'Sobreviva à tensão constante com recursos limitados e sustos calculados. Perfeito para quem gosta de adrenalina, mistério e coragem à prova.', 'ativo'),
(9, 'Primeira pessoa', 'Viva tudo pelos olhos do personagem: cada passo, cada recarga, cada susto. Imersão total, mira precisa e presença intensa.', 'ativo'),
(10, 'Terceira pessoa', 'Câmera sobre o ombro e visão ampla do cenário e do seu herói. Combate estiloso, acrobacias e leitura tática do ambiente para ação e aventura cinematográficas.', 'ativo'),
(11, 'Indie', 'Ideias autorais, estilos únicos e criatividade sem amarras. Descubra joias que inovam em mecânicas, arte e narrativa.', 'ativo'),
(12, 'Plataforma', 'Saltos precisos, fases criativas e desafios que valorizam habilidade. Do clássico ao moderno, a diversão é rápida e viciante.', 'ativo'),
(13, '2D', 'Jogabilidade afiada em duas dimensões, clara e responsiva. Visual limpo e ação direta, perfeita para quem ama o “clássico bem feito”.', 'ativo'),
(14, '3D', 'Explore mundos em todas as direções com liberdade de câmera. Descubra rotas, segredos e novas perspectivas a cada passo.', 'ativo'),
(15, 'Pixel Art', 'Charme retrô com identidade moderna e animações cheias de carisma. Um estilo que conquista pela nostalgia e pela personalidade.', 'ativo'),
(16, 'Puzzle', 'Desafios de lógica que premiam criatividade e raciocínio. Resolva enigmas, experimente soluções e sinta o momento “eureka”.', 'ativo'),
(17, 'Cartas', 'Monte o deck ideal e vire partidas com estratégia. Combinações inteligentes e sinergias poderosas garantem reviravoltas épicas.', 'ativo'),
(18, 'Roguelike', 'Fases procedurais, morte permanente e aprendizado a cada run. Recomeçar faz parte: teste rotas e refine sua maestria.', 'ativo'),
(19, 'Roguelite', 'Acesse upgrades persistentes entre tentativas para evoluir sempre. Cada run te deixa mais forte, sem perder o desafio.', 'ativo'),
(20, 'Metroidvania', 'Mapa interconectado e novas habilidades que desbloqueiam caminhos. Volte a áreas antigas, descubra segredos e sinta a progressão.', 'ativo'),
(21, 'Mundo aberto', 'Liberdade total para criar sua própria jornada em mapas vastos. Entre em missões, explore e encontre histórias a cada esquina.', 'ativo'),
(22, 'RPG', 'Evolua atributos, escolha sua build e personalize seu herói. Missões, loot e decisões constroem a sua lenda.', 'ativo'),
(23, 'RPG de turno', 'Planejamento por rodada e decisões cirúrgicas. Monte estratégias, controle o campo e vença com inteligência.', 'ativo'),
(24, 'Simulação', 'Sistemas profundos para gerenciar, viver e dominar atividades reais. Da fazenda à cidade, da vida ao voo, detalhe é tudo.', 'ativo'),
(25, 'Construção', 'Erga bases, cidades ou fábricas e otimize cada recurso. Do primeiro bloco ao grande império, eficiência é a chave.', 'ativo'),
(26, 'Anime', 'Visual vibrante, emoção intensa e heróis carismáticos. Estética japonesa com ação cinematográfica e cenas marcantes.', 'ativo'),
(27, 'Luta', 'Combos, counters e timing preciso em duelos eletrizantes. Treine, domine seus mains e conquiste a arena.', 'ativo'),
(36, 'Detetive', 'Investigue cenas de crime, siga pistas e conecte as peças do quebra-cabeça. Desvende mistérios, confronte suspeitos e descubra quem é o verdadeiro culpado.\r\n', 'ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `classificacao_ind`
--

CREATE TABLE `classificacao_ind` (
  `id` int(10) NOT NULL,
  `tipo` varchar(2) NOT NULL,
  `descricao` varchar(45) NOT NULL,
  `url_imagem` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `classificacao_ind`
--

INSERT INTO `classificacao_ind` (`id`, `tipo`, `descricao`, `url_imagem`) VALUES
(1, 'L', 'Livre para todas as idades.', 'uploads/clas-L.png'),
(2, '10', 'Inapropriado para menores de 10 anos.', 'uploads/clas-10.png'),
(3, '12', 'Inapropriado para menores de 12 anos.', 'uploads/clas-12.png'),
(4, '14', 'Inapropriado para menores de 14 anos.', 'uploads/clas-14.png'),
(5, '16', 'Inapropriado para menores de 16 anos.', 'uploads/clas-16.png'),
(6, '18', 'Inapropriado para menores de 18 anos.', 'uploads/clas-18.png');

-- --------------------------------------------------------

--
-- Estrutura para tabela `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `data_compra` datetime NOT NULL DEFAULT current_timestamp(),
  `form_pag` enum('Pix','Crédito','Débito') NOT NULL,
  `parcelas` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `valor_final` decimal(10,2) NOT NULL,
  `status` enum('pago','pendente','cancelado') NOT NULL DEFAULT 'pago'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `compras`
--

INSERT INTO `compras` (`id`, `user_id`, `data_compra`, `form_pag`, `parcelas`, `valor_final`, `status`) VALUES
(1, 1, '2025-11-23 20:45:20', 'Pix', 1, 119.98, 'pago'),
(2, 1, '2025-11-23 20:47:28', 'Crédito', 7, 104.25, 'pago'),
(3, 1, '2025-11-24 20:51:43', 'Crédito', 3, 105.85, 'pago'),
(4, 9, '2025-11-24 20:55:57', 'Débito', 1, 196.18, 'pago'),
(5, 1, '2025-11-24 20:57:08', 'Pix', 1, 39.09, 'pago'),
(6, 1, '2025-11-25 21:51:35', 'Crédito', 2, 80.97, 'pago'),
(8, 1, '2025-12-01 13:37:35', 'Crédito', 2, 54.99, 'pago'),
(9, 28, '2025-12-02 10:20:38', 'Débito', 1, 98.60, 'pago'),
(10, 28, '2025-12-02 10:26:25', 'Crédito', 7, 236.22, 'pago'),
(12, 28, '2025-12-02 14:49:24', 'Pix', 1, 45.90, 'pago'),
(15, 31, '2025-12-02 17:24:15', 'Pix', 1, 71.73, 'pago'),
(16, 31, '2025-12-02 17:25:09', 'Crédito', 4, 82.30, 'pago'),
(17, 9, '2025-12-02 20:51:44', 'Crédito', 7, 104.43, 'pago');

-- --------------------------------------------------------

--
-- Estrutura para tabela `compras_itens`
--

CREATE TABLE `compras_itens` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `jogo_id` int(11) NOT NULL,
  `qtd_chave` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `preco_unit` decimal(10,2) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `compras_itens`
--

INSERT INTO `compras_itens` (`id`, `compra_id`, `jogo_id`, `qtd_chave`, `preco_unit`, `valor_total`) VALUES
(1, 1, 18, 2, 59.99, 119.98),
(2, 2, 9, 1, 100.20, 100.20),
(3, 3, 20, 1, 26.39, 26.39),
(4, 3, 17, 1, 19.14, 19.14),
(5, 3, 21, 1, 60.33, 60.33),
(6, 4, 18, 1, 59.99, 59.99),
(7, 4, 9, 1, 100.20, 100.20),
(8, 4, 1, 1, 35.99, 35.99),
(9, 5, 14, 1, 39.09, 39.09),
(10, 6, 23, 1, 32.99, 32.99),
(11, 6, 15, 1, 47.98, 47.98),
(13, 8, 3, 1, 54.99, 54.99),
(14, 9, 17, 2, 19.14, 38.27),
(15, 9, 21, 1, 60.33, 60.33),
(16, 10, 13, 1, 81.29, 81.29),
(17, 10, 11, 1, 71.78, 71.78),
(18, 10, 8, 1, 73.99, 73.99),
(20, 12, 4, 1, 45.90, 45.90),
(24, 15, 19, 1, 31.53, 31.53),
(25, 15, 16, 1, 40.20, 40.20),
(26, 16, 13, 1, 81.29, 81.29),
(27, 17, 20, 1, 26.39, 26.39),
(28, 17, 8, 1, 73.99, 73.99);

-- --------------------------------------------------------

--
-- Estrutura para tabela `jogos`
--

CREATE TABLE `jogos` (
  `id` int(10) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `resumo` varchar(800) NOT NULL,
  `desenvolvedor` varchar(50) NOT NULL,
  `data_lancamento` date DEFAULT NULL,
  `conteudo` varchar(255) NOT NULL,
  `preco` double NOT NULL,
  `desconto` int(3) NOT NULL,
  `url_banner` varchar(255) NOT NULL,
  `status` enum('ativo','inativo') NOT NULL,
  `url_1` varchar(255) DEFAULT NULL,
  `url_2` varchar(255) DEFAULT NULL,
  `url_3` varchar(255) DEFAULT NULL,
  `url_4` varchar(255) DEFAULT NULL,
  `url_5` varchar(255) DEFAULT NULL,
  `classificacao_ind` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `jogos`
--

INSERT INTO `jogos` (`id`, `nome`, `resumo`, `desenvolvedor`, `data_lancamento`, `conteudo`, `preco`, `desconto`, `url_banner`, `status`, `url_1`, `url_2`, `url_3`, `url_4`, `url_5`, `classificacao_ind`) VALUES
(1, 'Sayonara Wild Hearts', 'Sayonara Wild Hearts é um jogo arcade de sonhos sobre andar em motos e skates, batalhas de dança, atirar com laser, brandir espadas e partir corações a 300 km/h.', 'Simogo', '2019-12-12', 'Violência fantasiosa', 35.99, 0, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1122720/daabe8ce68d602810e6e9019597f8440729930db/header.jpg?t=1759736011', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1122720/ss_3190e7aa5351cb206061e50bd5fb75e828206069.600x338.jpg?t=1759736011', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1122720/ss_78e2f5e6afa93bfcea8bbe587487ae4bc0c50e6e.600x338.jpg?t=1759736011', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1122720/ss_aa6b4160134b52df766597acd78cfef9a0b05fce.600x338.jpg?t=1759736011', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1122720/ss_03804e3335afd5edddceafe722ff1c4eb3c19931.600x338.jpg?t=1759736011', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1122720/ss_7e67136f2bf7c5a3299f69e7be43d7a05abcfdcd.600x338.jpg?t=1759736011', 1),
(2, 'Mouthwashing', 'Os cinco tripulantes do Tulpar estão encalhados nos confins vazios do espaço, envoltos num pôr do sol perpétuo. Deus não está aqui.', 'Wrong Organ', '2024-09-26', 'Violência, Temas sensíveis, Linguagem imprópria', 35.9, 0, 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/2475490/header.jpg?t=1758910697', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2475490/ss_d582f6e384652c1a05fb6e928ab6557b0abba1b3.600x338.jpg?t=1758910697', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2475490/ss_ec922c5769acb9986aa009e41b71ecdcd51e484f.600x338.jpg?t=1758910697', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2475490/ss_bf9f8aeca93daffa7a4493d9af8ffc4524cada74.600x338.jpg?t=1758910697', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2475490/ss_fc1d15989f3d8ef8c6493881bb68dd284defa0cb.600x338.jpg?t=1758910697', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2475490/ss_f707dcc6da5c778d9730949037b06089ba92efa4.600x338.jpg?t=1758910697', 5),
(3, 'Celeste', 'Ajude Madeline a enfrentar seus demônios internos em sua jornada até o topo da Montanha Celeste, nesse jogo de plataforma super afiado dos criadores de TowerFall. Desbrave centenas de desafios meticulosos, descubra segredos complicados e desvende o mistério da montanha.', 'Maddy Makes Games Inc., Extremely OK Games, Ltd.', '2018-01-25', 'Violência fantasiosa', 54.99, 0, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/504230/header.jpg?t=1714089525', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/504230/ss_03bfe6bd5ddac7f747c8d2aa1a4f82cfd53c6dcb.600x338.jpg?t=1714089525', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/504230/ss_1012b11ad364ad6c138a25a654108de28de56c5f.600x338.jpg?t=1714089525', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/504230/ss_1098b655a622720cfd549b104736a4eca8948100.600x338.jpg?t=1714089525', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/504230/ss_0eab901ec5c364aa18225fa608ff9cbcc1f432bf.600x338.jpg?t=1714089525', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/504230/ss_3140f6f87aa74c20e142c36d74691f930eda88d5.600x338.jpg?t=1714089525', 1),
(4, 'Balatro', 'O pôquer roguelike. Balatro é um jogo de criação de baralho hipnoticamente satisfatório em que você joga mãos de pôquer ilegais, descobre curingas que mudam o jogo e aciona combos escandalosos e cheios de adrenalina.', 'LocalThunk', '2024-02-20', '', 45.9, 0, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2379780/7a85430784e4d613cdb0547414d8cf16ffa45747/header.jpg?t=1757947116', 'ativo', 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/2379780/96208723dbedef49d71bf1b0a74aee1689018c50/ss_96208723dbedef49d71bf1b0a74aee1689018c50.600x338.jpg?t=1761672326', 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/2379780/ss_3be65a7dd3be072d567e11883d208861a7e959fa.600x338.jpg?t=1761672326', 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/2379780/ss_e32ac94d7d1d6be7dd015d78f2b52aeb4cc282ed.600x338.jpg?t=1761672326', 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/2379780/ss_204350350761316ad3aa8b0184689f3f93e01f7b.600x338.jpg?t=1761672326', 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/2379780/d8bca9a646921db5ecd24c25571b79e4ff15aa60/ss_d8bca9a646921db5ecd24c25571b79e4ff15aa60.600x338.jpg?t=1761672326', 1),
(5, 'Hollow Knight: Silksong', 'Descubra um reino vasto e amaldiçoado em Hollow Knight: Silksong! Explore, lute e sobreviva enquanto você ascende ao pico de uma terra governada pela seda e por canções.', 'Team Cherry', '2025-09-04', 'Violência fantasiosa', 59.9, 0, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1030300/7983574d464e6559ac7e24275727f73a8bcca1f3/header.jpg?t=1756994410', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1030300/09ccaa6c16f158f9df8298feb5d196098506a028/ss_09ccaa6c16f158f9df8298feb5d196098506a028.600x338.jpg?t=1756994410', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1030300/856e33e755a0b9a785c645d116036516ea08812b/ss_856e33e755a0b9a785c645d116036516ea08812b.600x338.jpg?t=1756994410', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1030300/1b93e8131cb6f4bd9e3791a606d0da8f9ee78276/ss_1b93e8131cb6f4bd9e3791a606d0da8f9ee78276.600x338.jpg?t=1756994410', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1030300/2ebe4dc35ad33ad86b672f7cf9faed44c9b1668e/ss_2ebe4dc35ad33ad86b672f7cf9faed44c9b1668e.600x338.jpg?t=1756994410', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1030300/dae3a0798c6c59a433b46de8f5ec18b21ad97fcd/ss_dae3a0798c6c59a433b46de8f5ec18b21ad97fcd.600x338.jpg?t=1756994410', 1),
(6, 'Peak', 'PEAK é um jogo cooperativo de escalada onde o menor erro pode custar a sua vida. Sozinho ou em grupo, sua única esperança de resgate em uma ilha misteriosa é escalar a montanha no seu centro. Você tem o que é preciso para chegar ao PICO?', 'Team PEAK', '2025-06-16', 'Interatividade online, Conversa em jogo', 21.99, 0, 'https://upload.wikimedia.org/wikipedia/en/0/0f/PEAK_cover_image.jpg', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3527290/bac7b90dffb456afecc4517a3e1d69362b95d15b/ss_bac7b90dffb456afecc4517a3e1d69362b95d15b.600x338.jpg?t=1759172507', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3527290/9c500124c060f162f111afa679bf5d3a32b3fb40/ss_9c500124c060f162f111afa679bf5d3a32b3fb40.600x338.jpg?t=1759172507', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3527290/55365bfa09745df86bed72720a842f64d8724b9d/ss_55365bfa09745df86bed72720a842f64d8724b9d.600x338.jpg?t=1759172507', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3527290/63b22127ea64aba8b16c33a172b0fedbf542e834/ss_63b22127ea64aba8b16c33a172b0fedbf542e834.600x338.jpg?t=1759172507', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3527290/52547c087901dec5b065b4559503804c02c10c22/ss_52547c087901dec5b065b4559503804c02c10c22.600x338.jpg?t=1759172507', 2),
(7, 'Cyberpunk 2077', 'Cyberpunk 2077 é um RPG de ação e aventura em mundo aberto que se passa em Night City, uma megalópole perigosa onde todos são obcecados por poder, glamour e alterações corporais.', 'CD PROJEKT RED', '2020-12-09', 'Conteúdo sexual, Drogas, Violência extrema', 190.52, 65, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1091500/header.jpg', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1091500/ss_af2804aa4bf35d4251043744412ce3b359a125ef.600x338.jpg?t=1756209867', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1091500/ss_7924f64b6e5d586a80418c9896a1c92881a7905b.600x338.jpg?t=1756209867', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1091500/ss_b529b0abc43f55fc23fe8058eddb6e37c9629a6a.600x338.jpg?t=1756209867', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1091500/ss_8640d9db74f7cad714f6ecfb0e1aceaa3f887e58.600x338.jpg?t=1756209867', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1091500/ss_bb1a60b8e5061caef7208369f42c5c9d574c9ac4.600x338.jpg?t=1756209867', 6),
(8, 'Red Dead Redemption 2 ', 'Red Dead Redemption 2, a épica aventura de mundo aberto da Rockstar Games aclamada pela crítica e o jogo mais bem avaliado desta geração de consoles, agora chega aprimorado para PC com conteúdos inéditos no Modo História, melhorias visuais e muito mais.', 'Rockstar Games', '2019-12-05', 'Violência, Atos criminosos, Drogas ilícitas', 295.95, 75, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1174180/header.jpg?t=1720558643', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1174180/ss_66b553f4c209476d3e4ce25fa4714002cc914c4f.600x338.jpg?t=1759502961', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1174180/ss_bac60bacbf5da8945103648c08d27d5e202444ca.600x338.jpg?t=1759502961', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1174180/ss_668dafe477743f8b50b818d5bbfcec669e9ba93e.600x338.jpg?t=1759502961', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1174180/ss_4ce07ae360b166f0f650e9a895a3b4b7bf15e34f.600x338.jpg?t=1759502961', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1174180/ss_d1a8f5a69155c3186c65d1da90491fcfd43663d9.600x338.jpg?t=1759502961', 6),
(9, 'Persona 3 Reload', 'Mergulhe na Hora Sombria e desperte as profundezas do seu coração. Persona 3 Reload é uma reimaginação cativante do RPG que redefiniu o gênero, agora repensado para a era moderna com gráficos e jogabilidade de ponta.', 'ATLUS', '2024-02-02', 'Violência, Temas sensíveis, Conteúdo sexual', 250.5, 60, 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/2161700/header.jpg?t=1744328458', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2161700/ss_7017244fb8319ba927a0ef414959b95a6164356f.600x338.jpg?t=1744328458', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2161700/ss_e5f9b3aedadef82286c34d9d419b7cd45e034f87.1920x1080.jpg?t=1744328458', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2161700/ss_f51fc26c627816bbd87011ca278b1fbbda2d6bc8.1920x1080.jpg?t=1744328458', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2161700/ss_b5b93089686b45d6abee593d025c91389c7dc20e.1920x1080.jpg?t=1744328458', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2161700/ss_cfe4b9124ea5e815e7981e3ac29a72a02fd48239.1920x1080.jpg?t=1744328458', 5),
(10, 'Guilty Gear -Strive-', 'Os gráficos híbridos 2D/3D de ponta, pioneiros na série Guilty Gear, foram levados a um novo patamar em \"GUILTY GEAR -STRIVE-\". A nova direção artística e as animações aprimoradas dos personagens vão além de tudo o que você já viu em um jogo de luta!', 'Arc System Works', '2021-06-11', 'Linguagem imprópria, Conteúdo sexual, Violência', 170.99, 60, 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/1384160/header.jpg?t=1755768087', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1384160/ss_104e16788c2ea35e1dac31d96be8d4f1df00f330.600x338.jpg?t=1761305253', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1384160/ss_3bcbd1e70fb68db2347e02299f6606cb551e9ff1.600x338.jpg?t=1761305253', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1384160/ss_a373027db9e3f72f10637204bad95fb3810a3170.600x338.jpg?t=1761305253', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1384160/ss_c290682ecdf413e368d9a66cd4a7a9430860ca1d.600x338.jpg?t=1761305253', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1384160/ss_2561a4ae822856797b5fb231502b820a962336e0.600x338.jpg?t=1761305253', 3),
(11, 'Grand Theft Auto V Enhanced', 'Aproveite os fenômenos do entretenimento Grand Theft Auto V e Grand Theft Auto Online melhorados para uma nova geração, com gráficos deslumbrantes, tempos de carregamento mais rápidos, áudio 3D e mais, além de conteúdo exclusivo para jogadores do GTA Online.', 'Rockstar North', '2025-03-04', 'Drogas ilícitas, Nudez, Violência', 143.55, 50, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3240220/header.jpg?t=1753974947', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3240220/d61184a98c1cf2db2b08b2999c04b0519e3615bb/ss_d61184a98c1cf2db2b08b2999c04b0519e3615bb.600x338.jpg?t=1753974947', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3240220/f4a6f63b4f7cb42262152449ba5e5c5837f20ff4/ss_f4a6f63b4f7cb42262152449ba5e5c5837f20ff4.600x338.jpg?t=1753974947', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3240220/f2e70b5823510daa062293ff0b03821e1dee2d37/ss_f2e70b5823510daa062293ff0b03821e1dee2d37.600x338.jpg?t=1753974947', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3240220/808b550269f898e227dae2c64b5e026f90da85f9/ss_808b550269f898e227dae2c64b5e026f90da85f9.600x338.jpg?t=1753974947', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3240220/85ff4a9fad2064c201c00c27d8d28c28fa03c481/ss_85ff4a9fad2064c201c00c27d8d28c28fa03c481.600x338.jpg?t=1753974947', 6),
(12, 'Hades', 'Desafie o deus dos mortos enquanto você batalha para sair do Submundo neste jogo roguelike dos mesmos criadores de Bastion, Transistor e Pyre.', 'Supergiant Games', '2020-09-17', 'Violência', 71.99, 75, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1145360/header.jpg?t=1758127023', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1145360/ss_fa48b583bfd1423561c6efdd6690b30acd85887c.600x338.jpg?t=1758127023', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1145360/ss_68300459a8c3daacb2ec687adcdbf4442fcc4f47.600x338.jpg?t=1758127023', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1145360/ss_217b70678a2eea71a974fba1a4cd8baa660581bb.600x338.jpg?t=1758127023', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1145360/ss_07ac15de74269ca40b7ef35f502315ae0116f1ae.600x338.jpg?t=1758127023', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1145360/ss_e0622b5a57521b76182d7e7e1ae47ee440edcf90.600x338.jpg?t=1758127023', 4),
(13, 'Elden Ring', 'O NOVO RPG DE AÇÃO E FANTASIA. Levante-se, Maculado, e seja guiado pela graça para portar o poder do Anel Prístino e se tornar um Lorde Prístino nas Terras Intermédias.', 'FromSoftware, Inc.', '2022-02-24', 'Violência, Compras online', 270.95, 70, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1245620/header.jpg?t=1748630546', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1245620/ss_943bf6fe62352757d9070c1d33e50b92fe8539f1.600x338.jpg?t=1748630546', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1245620/ss_3c41384a24d86dddd58a8f61db77f9dc0bfda8b5.600x338.jpg?t=1748630546', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1245620/ss_b1b91299d7e4b94201ac840aa64de54d9f5cb7f3.600x338.jpg?t=1748630546', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1245620/ss_c2baf8aada6140beee79d701d14043899e91af47.600x338.jpg?t=1748630546', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1245620/ss_c494372930ca791bdc6221eca134f2270fb2cb9f.600x338.jpg?t=1748630546', 5),
(14, 'MiSide', 'Imagine que você tem um jogo no qual você cuida de um personagem. Mas você consegue imaginar um dia entrar nesse jogo?', 'AIHASTO', '2024-12-10', 'Violência, Nudez', 45.99, 15, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2527500/header.jpg?t=1754525790', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2527500/ss_7d0dff5dd742b106889cde15fff3be6c4d521bb1.600x338.jpg?t=1754525790', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2527500/ss_bbcfb91713fd45ebf7e8f8d9c5c6c2a194ef00a8.600x338.jpg?t=1754525790', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2527500/ss_b65b001751579fcfb3bc1d77899a71d83186a079.600x338.jpg?t=1754525790', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2527500/ss_7f327615a5938bea292a6307dc10edb152023e52.600x338.jpg?t=1754525790', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2527500/ss_0052fb30551d9d7bb6e342ee47005f57856d0a16.600x338.jpg?t=1754525790', 3),
(15, 'Sally Face', 'Mergulhe em uma Aventura sinistra sobre um garoto com o rosto protético e um passado trágico. Desvende os sinistros mistérios da história de Sally para encontrar a verdade oculta por segredos sombrios.', 'Portable Moose', '2016-12-14', 'Violência extrema, Conteúdo sexual, Drogas lícitas', 47.98, 0, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/567990/header.jpg?t=1753030279', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/567990/ss_aa1bc9bf90460773ad412c54114516fb08cf0ae5.600x338.jpg?t=1753030279', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/567990/ss_370255b4613ed824d3677d6d846561beca70ba7a.600x338.jpg?t=1753030279', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/567990/ss_bd2314258f1db407eaaec10dcf110009f08a794e.600x338.jpg?t=1753030279', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/567990/ss_810c1b89ed799a6f77301697a943b5daa6294008.600x338.jpg?t=1753030279', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/567990/ss_a362cc6c9d3b80924c379dfb66fb53f03bb7d467.600x338.jpg?t=1753030279', 6),
(16, 'Wild Whiskers', 'Você nunca pensou que roedores pudessem desbravar o mar, mas é real. Em uma experiência de RPG, explore as ruas sujas, lute contra delinquentes e coma comidas de origem duvidosa. A pirataria nunca foi tão pequena.', 'Jhon Games', '0000-00-00', 'Violência fantasiosa, Drogas ilícitas, Temas sensíveis', 40.2, 0, 'uploads/wildw0.jpg', 'ativo', 'uploads/wildw1.jpg', 'uploads/wildw2.jpg', 'uploads/wildw3.jpeg', 'uploads/wildw4.jpeg', '', 4),
(17, 'Risk of Rain 2', 'Fuja de um planeta alienígena caótico enfrentando hordas de monstros frenéticos com seus amigos ou por conta própria. Combine itens de maneiras inusitadas e domine cada personagem para se tornar o caos que você temia depois da primeira aterrissagem forçada.', 'Hopoo Games', '2020-08-11', 'Armas, Violência, Presença de sangue, Morte intencional', 57.99, 67, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/632360/header.jpg?t=1751308044', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/632360/ss_a3f57f281813cb51cb5d919701470acb962ff297.600x338.jpg?t=1751308044', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/632360/ss_328d6fcb223f848c2a1047bb86702c4175d92317.600x338.jpg?t=1751308044', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/632360/ss_dc777b5c583794c99440b196cd1d26884fb1720b.600x338.jpg?t=1751308044', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/632360/ss_0377ff24b4d60db6a38ddc0824b7f308890b9231.600x338.jpg?t=1751308044', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/632360/ss_85548e86c50ff654c6a49235ea686a956f8ee9ec.600x338.jpg?t=1751308044', 4),
(18, 'Intertwined', 'Descubra os segredos do projeto INTERTWINED e explore um mundo futurístico e cyberpunk. Conheça personagens carismáticos (ou não), em um mundo 2D. Lute contra inimigos formidáveis e melhore suas habilidades de combate. SE VINGUE DE QUEM TE FEZ SOFRER.', 'Gamewiz', '0000-00-00', 'Violência, Linguagem imprópria', 59.99, 0, 'uploads/int1.png', 'ativo', 'uploads/int2.png', 'uploads/int3.png', 'uploads/int4.png', 'uploads/int5.png', 'uploads/int6.png', 4),
(19, 'Koira', 'Salve um filhotinho e embarquem juntos em uma emocionante aventura desenhada à mão. Viaje para o coração de uma floresta encantada, resolvendo quebra-cabeças e evitando caçadores para proteger o seu novo amigo.', 'Studio Tolima', '2025-04-01', 'Violência fantasiosa', 52.55, 40, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1626620/header.jpg?t=1750244781', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1626620/bbee91f228d80c77bae58d1c95880ff8dd7ac718/ss_bbee91f228d80c77bae58d1c95880ff8dd7ac718.600x338.jpg?t=1760622992', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1626620/94022a0d07e10b4f4fe5a330d5a9fa5c1edf4e91/ss_94022a0d07e10b4f4fe5a330d5a9fa5c1edf4e91.600x338.jpg?t=1760622992', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1626620/ss_3ff16c19a83932e6918d954ab504563ac47fd611.600x338.jpg?t=1760622992', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1626620/8e0acbd4725c74aa7bd5f8bb8b2996c2bafd6d0a/ss_8e0acbd4725c74aa7bd5f8bb8b2996c2bafd6d0a.600x338.jpg?t=1760622992', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1626620/ss_45708af16447bd5039c199b57ee68b35dd410eef.600x338.jpg?t=1760622992', 1),
(20, 'R.E.P.O', 'Um jogo de terror cooperativo online para até 6 jogadores. Localize objetos valiosos, totalmente baseados em física, e manuseie-os com cuidado enquanto os recupera e extrai para satisfazer os desejos do seu criador.', 'semiwork', '2025-02-26', 'Violência, Medo', 32.99, 20, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3241660/1ea445e044a2d5b09cfa8291350b63ebed6e5741/header.jpg?t=1759236707', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3241660/ss_e6babaab52581f81df91e50768cee6a9334ef6ec.600x338.jpg?t=1761334930', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3241660/ss_af7d8d6302d543d89019bad49ea853a970bb82de.600x338.jpg?t=1761334930', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3241660/ss_a66715d57329c456d91aeb11fbd406e7d8c5dbc7.600x338.jpg?t=1761334930', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3241660/ss_cd332c2299810a65c6aee61c04750197a919692a.600x338.jpg?t=1761334930', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3241660/ss_e4dc1bc0ada8fb7cb164b3ac37be82f51aae5627.600x338.jpg?t=1761334930', 4),
(21, 'Subnautica', 'Mergulhe nas profundezas de um mundo subaquático alienígena repleto de maravilhas e perigos. Crie equipamentos, pilote submarinos e supere a vida selvagem para explorar recifes de corais exuberantes, vulcões, sistemas de cavernas e muito mais — tudo isso enquanto tenta sobreviver.', 'Unknown Worlds Entertainment', '2018-01-23', 'Violência', 120.65, 50, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/264710/header.jpg?t=1751944840', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/264710/ss_e182b6b20bb797500f9f63c561586d920d44e37c.600x338.jpg?t=1751944840', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/264710/ss_970a13f246e33e0df26d93baf9f8e975732adb4b.600x338.jpg?t=1751944840', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/264710/ss_cebc378d2f7bc78978c21db4e3c5e12ccd067349.600x338.jpg?t=1751944840', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/264710/ss_883a98ad56021ce409219e1b749818866b6115cd.600x338.jpg?t=1751944840', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/264710/ss_0ace7f8b4350b8fbdd16345a76bc30545256e918.600x338.jpg?t=1751944840', 2),
(22, 'Stardew Valley', 'Você herdou a antiga fazenda do seu avô, em Stardew Valley. Com ferramentas de segunda-mão e algumas moedas, você parte para dar início a sua nova vida. Será que você vai aprender a viver da terra, a transformar esse matagal em um próspero lar?', 'ConcernedApe', '2016-02-26', 'Violência fantasiosa, Violência, Droga lícitas', 23.99, 0, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/413150/header.jpg?t=1754692865', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/413150/ss_b887651a93b0525739049eb4194f633de2df75be.600x338.jpg?t=1754692865', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/413150/ss_9ac899fe2cda15d48b0549bba77ef8c4a090a71c.600x338.jpg?t=1754692865', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/413150/ss_64d942a86eb527ac817f30cc04406796860a6fc1.600x338.jpg?t=1754692865', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/413150/ss_980472fb4f4860639155880938b6ec292a0648c4.600x338.jpg?t=1754692865', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/413150/ss_37f15ea893ec1fa7c9e73106f512e98161bac61b.600x338.jpg?t=1754692865', 3),
(23, 'Cuphead', 'Cuphead é um jogo de ação e tiros clássico, com enorme ênfase nas batalhas de chefes. Inspirado nas animações infantis da década de 1930, os visuais e efeitos sonoros foram minuciosamente recriados com as mesmíssimas técnicas dessa era. Jogue como Cuphead ou Mugman e atravesse mundos estranhos, adquira novas armas, aprenda supergolpes potentes e descubra segredos ocultos, tudo isso enquanto tenta pagar a dívida que você fez com o diabo!', 'Studio MDHR Entertainment Inc.', '2017-09-29', 'Linguagem imprópria', 32.99, 0, 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/268910/header.jpg?t=1709068852', 'ativo', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/268910/ss_615455299355eaf552c638c7ea5b24a8b46e02dd.600x338.jpg?t=1709068852', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/268910/ss_380296effbf1073bbedfd480e50cf246eb542b66.600x338.jpg?t=1709068852', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/268910/ss_48477e4a865827aa0be6a44f00944d8d2a3e5eb9.600x338.jpg?t=1709068852', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/268910/ss_874f2d27a9120ee60cbce0c7bd4085525fd09b26.600x338.jpg?t=1709068852', 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/268910/ss_ae3db08c403209d868e52ae513540e1ba0489302.600x338.jpg?t=1709068852', 2),
(58, 'CyberPixel', 'Cyber Pixel é um jogo 2D de Ação e Plataforma com foco em combate e pixel art, ambientado em uma cidade futurista onde Alex, um agente experiente, deve desativar um núcleo central para deter uma revolução de robôs. Inspirado em Metal Slug e Celeste.', 'Manoel Entertainment', '0000-00-00', 'Armas, Violência', 55.9, 0, 'uploads/cpixel.png', 'ativo', 'uploads/cpixel1.jpeg', 'uploads/cpixel2.jpeg', 'uploads/cpixel3.jpeg', '', '', 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `jogos_categorias`
--

CREATE TABLE `jogos_categorias` (
  `jogo_id` int(10) NOT NULL,
  `categoria_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `jogos_categorias`
--

INSERT INTO `jogos_categorias` (`jogo_id`, `categoria_id`) VALUES
(1, 1),
(1, 3),
(1, 5),
(1, 6),
(1, 7),
(1, 9),
(1, 10),
(2, 1),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 11),
(3, 1),
(3, 4),
(3, 6),
(3, 7),
(3, 11),
(3, 12),
(3, 13),
(3, 15),
(4, 1),
(4, 13),
(4, 15),
(4, 17),
(4, 18),
(4, 19),
(5, 1),
(5, 4),
(5, 6),
(5, 7),
(5, 11),
(5, 12),
(5, 13),
(5, 20),
(5, 21),
(6, 2),
(6, 4),
(6, 7),
(6, 9),
(6, 12),
(6, 18),
(7, 1),
(7, 5),
(7, 6),
(7, 7),
(7, 9),
(7, 14),
(7, 21),
(7, 22),
(8, 1),
(8, 2),
(8, 4),
(8, 5),
(8, 6),
(8, 7),
(8, 9),
(8, 10),
(8, 14),
(8, 21),
(9, 1),
(9, 4),
(9, 6),
(9, 7),
(9, 10),
(9, 14),
(9, 22),
(9, 23),
(9, 26),
(10, 2),
(10, 5),
(10, 14),
(10, 26),
(10, 27),
(11, 1),
(11, 2),
(11, 4),
(11, 5),
(11, 6),
(11, 7),
(11, 14),
(11, 21),
(12, 1),
(12, 5),
(12, 6),
(12, 7),
(12, 11),
(12, 14),
(12, 18),
(12, 19),
(12, 22),
(13, 1),
(13, 2),
(13, 5),
(13, 7),
(13, 10),
(13, 14),
(13, 22),
(14, 1),
(14, 6),
(14, 7),
(14, 8),
(14, 9),
(14, 14),
(14, 16),
(14, 26),
(15, 1),
(15, 4),
(15, 6),
(15, 8),
(15, 11),
(15, 13),
(15, 16),
(16, 1),
(16, 4),
(16, 6),
(16, 11),
(16, 13),
(16, 15),
(16, 22),
(16, 23),
(17, 2),
(17, 5),
(17, 10),
(17, 11),
(17, 14),
(17, 18),
(17, 19),
(18, 1),
(18, 4),
(18, 6),
(18, 11),
(18, 12),
(18, 13),
(18, 15),
(18, 20),
(19, 1),
(19, 4),
(19, 6),
(19, 13),
(19, 16),
(20, 2),
(20, 5),
(20, 8),
(20, 9),
(20, 14),
(21, 1),
(21, 4),
(21, 5),
(21, 7),
(21, 8),
(21, 9),
(21, 11),
(21, 14),
(21, 21),
(21, 25),
(22, 2),
(22, 13),
(22, 15),
(22, 21),
(22, 22),
(22, 24),
(22, 25),
(23, 2),
(23, 5),
(23, 6),
(23, 11),
(23, 12),
(23, 13),
(58, 1),
(58, 4),
(58, 5),
(58, 11),
(58, 12),
(58, 13),
(58, 15);

-- --------------------------------------------------------

--
-- Estrutura para tabela `requisitos`
--

CREATE TABLE `requisitos` (
  `id` int(11) NOT NULL,
  `jogo_id` int(11) NOT NULL,
  `processador` varchar(255) DEFAULT NULL,
  `memoria` varchar(255) DEFAULT NULL,
  `placa_video` varchar(255) DEFAULT NULL,
  `sistema_op` varchar(255) DEFAULT NULL,
  `armazenamento` varchar(255) DEFAULT NULL,
  `directx` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `requisitos`
--

INSERT INTO `requisitos` (`id`, `jogo_id`, `processador`, `memoria`, `placa_video`, `sistema_op`, `armazenamento`, `directx`) VALUES
(1, 1, 'Intel Core2 Duo E8300', '2 GB de RAM', 'GeForce 8800 GT', 'Windows 10', '2 GB de espaço disponível', 'Versão 9.0c'),
(2, 2, 'i5-6300HQ', '8 GB de RAM', 'GeForce GTX 560', 'Windows 7 64-bit', '3 GB de espaço disponível', 'Versão 11'),
(3, 3, 'Intel Core i3 M380', '2 GB de RAM', 'Intel HD 4000', 'Windows 7 ou mais atual', '1.2 GB de espaço disponível', 'Versão 10'),
(4, 4, 'Intel Core i3', '1 GB de RAM', 'Compatível com OpenGL 2.1', 'Windows 7 ou mais atual', '150 MB de espaço disponível', ''),
(5, 5, 'Intel Core i3-3240', '4 GB de RAM', 'GeForce GTX 560 Ti (1GB)', 'Windows 10 ou mais atual', '8 GB de espaço disponível', 'Versão 10'),
(6, 6, 'Intel Core i5 GHz', '8 GB de RAM', 'GeForce GTX 1060', 'Windows 10', '4 GB de espaço disponível', 'Versão 12'),
(7, 15, '2 GHz', '4 GB de RAM', NULL, 'Windows 7', '4 GB de espaço disponível', ''),
(8, 16, NULL, NULL, NULL, 'A ser definido', NULL, ''),
(9, 18, NULL, NULL, NULL, 'A ser definido', NULL, ''),
(10, 22, '2 GHz', '2 GB de RAM', '256 MB', 'Windows Vista ou mais atual', '500 MB de espaço disponível', ''),
(11, 23, 'Intel Core2 Duo E8400, 3.0GHz', '3 GB de RAM', 'Geforce 9600 GT', 'Windows 7', '4 GB de espaço disponível', 'Versão 11'),
(12, 7, 'Intel Core i7-6700', '12 GB de RAM', 'GeForce GTX 1060 6GB', 'Windows 10 64-bits', '70 GB de espaço disponível', 'Versão 12'),
(13, 8, ' Intel Core i5-2500K', '8 GB de RAM', 'GeForce GTX 770 2GB', ' Windows 10 64-bit', '150 GB de espaço disponível', ''),
(14, 9, 'Intel Core i5-2300', '8 GB de RAM', 'GeForce GTX 650 Ti, 2 GB', 'Windows 10', '30 GB de espaço disponível', 'Versão 12'),
(15, 10, 'Intel Core i5-3450, 3.10 GHz', '4 GB de RAM', 'GeForce GTX 650 Ti, 1 GB', 'Windows 8 ou mais atual', '26 GB de espaço disponível', 'Versão 11'),
(16, 11, 'Intel Core i7-4770', '8 GB de RAM', 'GeForce GTX 1630 (4GB)', 'Windows 10', '105 GB de espaço disponível', ''),
(17, 12, 'Dual Core 2.4 GHz', '4 GB de RAM', '1 GB', 'Windows 7', '15 GB de espaço disponível', ''),
(18, 13, 'Intel Core I5-8400', '12 GB de RAM', 'GeForce GTX 1060 3 GB', 'Windows 10', '60 GB de espaço disponível', 'Versão 12'),
(19, 14, 'AMD FX 8120 3.1 GHz', '4 GB de RAM', 'AMD Radeon HD6570', 'Windows 7 ou mais atual', '2 GB de espaço disponível', 'Versão 10'),
(20, 17, 'Intel Core i3-6100', '4 GB de RAM', 'GeForce GTX 580', 'Windows 7 ou mais atual', '4 GB de espaço disponível', 'Versão 11'),
(21, 19, 'Intel Core i3-2120', '4 GB de RAM', 'GeForce GTS 450', 'Windows 10', '2 GB de espaço disponível', 'Versão 11'),
(22, 20, 'Intel Core i5 6600', '8 GB de RAM', 'GeForce GTX 970', 'Windows 10', ' 1 GB de espaço disponível', 'Versão 10'),
(23, 21, 'Intel Haswell 2.5GHz', '4 GB de RAM', 'Intel HD 4600', 'Windows Vista ou mais atual', '20 GB de espaço disponível', 'Versão 11'),
(24, 58, '', '', '', 'A ser definido', '', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `url_avatar` varchar(255) NOT NULL,
  `status` enum('ativo','inativo') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `email`, `senha`, `role`, `url_avatar`, `status`) VALUES
(1, 'administrador', 'adm@adm.com', '$2y$10$UTuRrvP/YW0GUqWRXs.NKegHcO4nNKDVPu6TWwGn61yanSVnGYG8G', 'admin', 'uploads/avatars/1.jpg', 'ativo'),
(9, 'duda', 'dudagil.me@gmail.com', '$2y$10$9CIYxleUtFYND0W14JAAcemaZUE9z6lp6NY.LAEZmSUT8wkMLa.a2', 'user', 'uploads/avatars/9.png', 'ativo'),
(28, 'leo', 'leocorradinis@gmail.com', '$2y$10$1J.b.96MqqkE1XnfXDo/euyy3/d6mVEKI9k7YBAjWGxWDP4EGLIAW', 'user', '', 'ativo'),
(31, 'leia', 'teste@teste.com', '$2y$10$NJe0QcNa9c75t23RpW2G6O1IP3.pPhBpU2ZtkLVDzZHHEtW.IJP9y', 'user', '', 'inativo');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `biblioteca`
--
ALTER TABLE `biblioteca`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_chave` (`chave`),
  ADD UNIQUE KEY `ux_biblioteca_chave` (`chave`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_jogo` (`jogo_id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `classificacao_ind`
--
ALTER TABLE `classificacao_ind`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_compras_data` (`data_compra`),
  ADD KEY `idx_compras_status` (`status`),
  ADD KEY `idx_compras_user` (`user_id`);

--
-- Índices de tabela `compras_itens`
--
ALTER TABLE `compras_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_itens_compra` (`compra_id`),
  ADD KEY `idx_itens_jogo` (`jogo_id`);

--
-- Índices de tabela `jogos`
--
ALTER TABLE `jogos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jogos_classificacao` (`classificacao_ind`);

--
-- Índices de tabela `jogos_categorias`
--
ALTER TABLE `jogos_categorias`
  ADD PRIMARY KEY (`jogo_id`,`categoria_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices de tabela `requisitos`
--
ALTER TABLE `requisitos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jogo_id` (`jogo_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`) USING BTREE;

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `biblioteca`
--
ALTER TABLE `biblioteca`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `classificacao_ind`
--
ALTER TABLE `classificacao_ind`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `compras_itens`
--
ALTER TABLE `compras_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `jogos`
--
ALTER TABLE `jogos`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de tabela `requisitos`
--
ALTER TABLE `requisitos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `biblioteca`
--
ALTER TABLE `biblioteca`
  ADD CONSTRAINT `fk_bib_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bib_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `compras_itens`
--
ALTER TABLE `compras_itens`
  ADD CONSTRAINT `fk_itens_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_itens_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `jogos`
--
ALTER TABLE `jogos`
  ADD CONSTRAINT `fk_jogos_classificacao` FOREIGN KEY (`classificacao_ind`) REFERENCES `classificacao_ind` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `jogos_categorias`
--
ALTER TABLE `jogos_categorias`
  ADD CONSTRAINT `jogos_categorias_ibfk_1` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jogos_categorias_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Restrições para tabelas `requisitos`
--
ALTER TABLE `requisitos`
  ADD CONSTRAINT `requisitos_ibfk_1` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
