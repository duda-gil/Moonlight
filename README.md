# Moonlight — Game Key Store

**Trabalho de Conclusão de Curso — Técnico em Informática**

Moonlight é um e-commerce de chaves de jogos online desenvolvido com **PHP, HTML, CSS, JavaScript** e o framework **Bootstrap**.

---

## Funcionalidades

### Usuário Padrão
- Criação e autenticação de conta com perfil personalizável
- Adição de jogos ao carrinho e finalização de compras
- Três métodos de pagamento disponíveis
- Geração de chave de ativação fictícia e única após a compra
- Biblioteca pessoal com os jogos adquiridos

### Administrador
- Perfil pessoal separado da área administrativa (também pode realizar compras)
- Gerenciamento completo de jogos e categorias (adicionar, editar e excluir)
- Relatórios do sistema filtrados por período mensal:
  - Jogos por categoria
  - Vendas por mês
  - Recebimentos por mês

**Credenciais de acesso ADM:**
```markdown
email: adm@adm.com
senha: 123456
```

---

## Como Executar o Projeto

### Pré-requisitos
- [XAMPP](https://www.apachefriends.org/) instalado com os módulos **Apache** e **MySQL** ativos

### Passo a Passo

1. **Copie o projeto para o htdocs**
   - Baixe o `.zip` do Moonlight
   - Navegue até `C:\xampp\htdocs\`
   - Extraia o arquivo `.zip` dentro desta pasta

2. **Crie e importe o banco de dados**
   - Abra o navegador e acesse: `localhost/phpmyadmin`
   - Clique em **Novo** no painel lateral esquerdo
   - Nomeie o banco de dados como `bd` e confirme a criação
   - Com o banco `bd` selecionado, clique em **Importar** na barra superior
   - Em *Escolher arquivo*, localize o arquivo `bd` dentro da pasta do projeto em `htdocs`
   - Clique em **Importar** para concluir

3. **Acesse o sistema**
   - Abra uma nova aba no navegador e acesse: `localhost/Moonlight`
