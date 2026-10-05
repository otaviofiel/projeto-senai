// ============================================================
// FILAZERO / SENAI - SCRIPT PRINCIPAL
// ============================================================

// ============================================================
// 1. SISTEMA DE TEMAS E CORES DINÂMICAS
// ============================================================

function escurecerCor(hex, percentual) {
    hex = hex.replace('#', '');
    if (hex.length === 3) {
        hex = hex.split('').map(c => c + c).join('');
    }
    let num = parseInt(hex, 16);
    let r = (num >> 16) - Math.round(255 * (percentual / 100));
    let g = ((num >> 8) & 0x00FF) - Math.round(255 * (percentual / 100));
    let b = (num & 0x0000FF) - Math.round(255 * (percentual / 100));

    r = Math.max(0, Math.min(255, r));
    g = Math.max(0, Math.min(255, g));
    b = Math.max(0, Math.min(255, b));

    return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
}

window.selecionarTemaCor = function(cor, botaoElemento) {
    if (!cor) return;

    document.documentElement.style.setProperty('--cor-primaria', cor);
    const corHover = escurecerCor(cor, 20);
    document.documentElement.style.setProperty('--cor-primaria-hover', corHover);

    // Converte hex para rgba com 15% de opacidade
    let hexLimpo = cor.replace('#', '');
    if (hexLimpo.length === 3) hexLimpo = hexLimpo.split('').map(c => c + c).join('');
    let num = parseInt(hexLimpo, 16);
    let r = (num >> 16) & 255;
    let g = (num >> 8) & 255;
    let b = num & 255;
    document.documentElement.style.setProperty('--cor-primaria-clara', `rgba(${r}, ${g}, ${b}, 0.15)`);

    localStorage.setItem('senai_cor_tema', cor);

    // Atualiza controles na tela do painel se existirem
    const inputColor = document.getElementById('input-cor-personalizada');
    if (inputColor) inputColor.value = cor;

    const labelHex = document.getElementById('hex-code-label');
    if (labelHex) labelHex.textContent = cor.toUpperCase();

    // Atualiza classe active nos botões de amostra
    document.querySelectorAll('.color-swatch-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('onclick')?.toLowerCase().includes(cor.toLowerCase())) {
            btn.classList.add('active');
        }
    });

    if (botaoElemento) {
        botaoElemento.classList.add('active');
    }
};

function carregarTemaSalvo() {
    const corSalva = localStorage.getItem('senai_cor_tema') || '#E30613';
    window.selecionarTemaCor(corSalva);
}

// Executa imediatamente em qualquer página carregada
carregarTemaSalvo();


// ============================================================
// 2. SISTEMA DE LOGO DINÂMICA
// ============================================================

window.aplicarLogoEmTudo = function(logoSrc) {
    if (!logoSrc) return;
    document.querySelectorAll('.senai-logo, .header-logo, #preview-logo-painel').forEach(img => {
        img.src = logoSrc;
    });
};

window.manipularUploadLogo = function(evento) {
    const arquivo = evento.target.files[0];
    if (!arquivo) return;

    if (!arquivo.type.startsWith('image/')) {
        alert('Por favor, selecione um arquivo de imagem válido (PNG, JPG, SVG ou WEBP).');
        return;
    }

    // Limite de 2MB
    if (arquivo.size > 2 * 1024 * 1024) {
        alert('A imagem é muito grande! Por favor, selecione uma imagem com até 2MB.');
        return;
    }

    const leitor = new FileReader();
    leitor.onload = function(e) {
        const base64Logo = e.target.result;
        localStorage.setItem('senai_custom_logo', base64Logo);
        window.aplicarLogoEmTudo(base64Logo);
        alert('✓ Logo do site atualizada com sucesso em todas as páginas!');
    };
    leitor.readAsDataURL(arquivo);
};

window.restaurarLogoOriginal = function() {
    localStorage.removeItem('senai_custom_logo');
    window.aplicarLogoEmTudo('senai-logo.png');
    alert('✓ Logo original restaurada com sucesso!');
};

function carregarLogoSalva() {
    const logoSalva = localStorage.getItem('senai_custom_logo');
    if (logoSalva) {
        window.aplicarLogoEmTudo(logoSalva);
    }
}

// Executa em todas as páginas carregadas
carregarLogoSalva();


// ============================================================
// 3. CONFIGURAÇÃO INICIAL DO CARRINHO
// ============================================================

let carrinho = JSON.parse(localStorage.getItem('senai_cart')) || [];

function atualizarBadgeCarrinho() {
    const badge = document.querySelector('.cart-badge');
    if (badge) {
        const totalItens = carrinho.reduce(
            (total, item) => total + item.quantidade,
            0
        );
        badge.textContent = totalItens;
    }
}

atualizarBadgeCarrinho();

// ============================================================
// 3. CARREGAR PRODUTOS (CARDÁPIO)
// ============================================================

async function carregarProdutos() {
    const container = document.getElementById('lista-produtos');
    if (!container) return;

    try {
        const resposta = await fetch('api-produtos.php');
        const produtos = await resposta.json();

        container.innerHTML = '';

        if (!produtos || produtos.length === 0) {
            container.innerHTML = '<p style="text-align:center; width:100%; color:#666;">Nenhum produto cadastrado.</p>';
            return;
        }

        produtos.forEach(produto => {
            const precoFormatado = parseFloat(produto.preco)
                .toFixed(2)
                .replace('.', ',');

            const cardHTML = `
                <div class="product-card">
                    <img src="${produto.imagem}" alt="${produto.nome}">
                    <h4>${produto.nome}</h4>
                    <p class="category">${produto.categoria}</p>
                    <p class="price">R$ ${precoFormatado}</p>
                    <button class="btn-add">+</button>
                </div>
            `;

            container.innerHTML += cardHTML;
        });

        ligarBotoesDeAdicionar();

    } catch (erro) {
        console.error("Erro ao carregar produtos:", erro);
        container.innerHTML = '<p style="color:red; text-align:center; width:100%;">Erro ao carregar o cardápio.</p>';
    }
}

function ligarBotoesDeAdicionar() {
    const botoesAdicionar = document.querySelectorAll('.btn-add');

    botoesAdicionar.forEach(botao => {
        botao.addEventListener('click', function(e) {
            const card = e.target.closest('.product-card');
            const nome = card.querySelector('h4').textContent;
            const precoTexto = card.querySelector('.price').textContent;
            const preco = parseFloat(
                precoTexto.replace('R$', '').replace(',', '.').trim()
            );
            const imagem = card.querySelector('img').src;

            const itemExistente = carrinho.find(item => item.nome === nome);

            if (itemExistente) {
                itemExistente.quantidade += 1;
            } else {
                carrinho.push({
                    nome: nome,
                    preco: preco,
                    imagem: imagem,
                    quantidade: 1
                });
            }

            localStorage.setItem('senai_cart', JSON.stringify(carrinho));
            atualizarBadgeCarrinho();

            // Feedback visual no botão
            botao.textContent = '✓';
            botao.style.backgroundColor = '#28a745';

            setTimeout(() => {
                botao.textContent = '+';
                botao.style.backgroundColor = '';
            }, 800);
        });
    });
}

carregarProdutos();

// ============================================================
// 4. EXIBIR ITENS NO CARRINHO
// ============================================================

const caixaItensCarrinho = document.querySelector('.cart-items-box');

if (caixaItensCarrinho) {
    if (carrinho.length === 0) {
        caixaItensCarrinho.innerHTML = `
            <p style="padding:20px; color:#666; width:100%; text-align:center;">
                Seu carrinho está vazio.
            </p>
        `;

        const subtotalElemento = document.querySelector('.summary-line:nth-child(2) span:nth-child(2)');
        const totalElemento = document.querySelector('.total-line span:nth-child(2)');

        if (subtotalElemento) subtotalElemento.textContent = 'R$ 0,00';
        if (totalElemento) totalElemento.textContent = 'R$ 0,00';

    } else {
        caixaItensCarrinho.innerHTML = '';
        let subtotal = 0;

        carrinho.forEach((item, index) => {
            subtotal += item.preco * item.quantidade;

            const itemHTML = `
                <div class="cart-item" style="position:relative;">
                    <button
                        class="btn-remove"
                        onclick="removerItem(${index})"
                        style="position:absolute; top:2px; right:5px; background:none; border:none; color:#E30613; font-weight:bold; cursor:pointer;"
                    >
                        X
                    </button>
                    <img src="${item.imagem}" alt="${item.nome}">
                    <p>${item.nome}</p>
                    <p style="color:var(--cor-primaria); font-size:11px;">
                        ${item.quantidade}x R$ ${item.preco.toFixed(2).replace('.', ',')}
                    </p>
                </div>
            `;

            caixaItensCarrinho.innerHTML += itemHTML;
        });

        const precoFinalFormatado = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
        const subtotalElemento = document.querySelector('.summary-line:nth-child(2) span:nth-child(2)');
        const totalElemento = document.querySelector('.total-line span:nth-child(2)');

        if (subtotalElemento) subtotalElemento.textContent = precoFinalFormatado;
        if (totalElemento) totalElemento.textContent = precoFinalFormatado;
    }
}

window.removerItem = function(index) {
    carrinho.splice(index, 1);
    localStorage.setItem('senai_cart', JSON.stringify(carrinho));
    window.location.reload();
};

// ============================================================
// 5. FINALIZAR PEDIDO (SALVAR NO BANCO)
// ============================================================

const btnFinalizarPedido = document.getElementById('btn-finalizar');

if (btnFinalizarPedido) {
    btnFinalizarPedido.addEventListener('click', async function() {
        let carrinhoAtual = JSON.parse(localStorage.getItem('senai_cart')) || [];

        if (carrinhoAtual.length === 0) {
            alert("Seu carrinho está vazio! Adicione alguns lanches primeiro.");
            return;
        }

        const totalPedido = carrinhoAtual.reduce(
            (soma, item) => soma + (item.preco * item.quantidade),
            0
        );

        const dadosPedido = {
            itens: carrinhoAtual,
            total: totalPedido
        };

        try {
            btnFinalizarPedido.disabled = true;
            btnFinalizarPedido.textContent = "Enviando Pedido...";

            const resposta = await fetch('salvar-pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dadosPedido)
            });

            const resultado = await resposta.json();

            if (resultado.sucesso) {
                localStorage.removeItem('senai_cart');
                window.location.href = `pedido-final.html?senha=${resultado.senha}`;
            } else {
                alert("Erro do servidor: " + (resultado.erro || 'Não foi possível salvar o pedido.'));
                btnFinalizarPedido.disabled = false;
                btnFinalizarPedido.textContent = "PROSSEGUIR";
            }

        } catch (erro) {
            console.error("Erro na requisição:", erro);
            alert("Erro de conexão ao tentar salvar o pedido.");
            btnFinalizarPedido.disabled = false;
            btnFinalizarPedido.textContent = "PROSSEGUIR";
        }
    });
}

// ============================================================
// 6. TELA FINAL / TICKET
// ============================================================

function carregarSenhaDoTicket() {
    const urlParams = new URLSearchParams(window.location.search);
    const senhaDoBanco = urlParams.get('senha');

    const senhaElemento = document.getElementById('senha-gerada');
    if (senhaElemento) {
        if (senhaDoBanco) {
            senhaElemento.textContent = String(senhaDoBanco).padStart(2, '0');
        } else {
            const senhaAleatoria = Math.floor(Math.random() * 99) + 1;
            senhaElemento.textContent = String(senhaAleatoria).padStart(2, '0');
        }
    }

    const btnRestart = document.getElementById('btn-restart');
    if (btnRestart) {
        btnRestart.addEventListener('click', function() {
            localStorage.removeItem('senai_cart');
            window.location.href = 'index.html';
        });
    }
}

carregarSenhaDoTicket();

// ============================================================
// 7. NAVEGAÇÃO DE RODAPÉ
// ============================================================

const navHome = document.querySelector('.bottom-nav .nav-item:nth-child(1)');
if (navHome) {
    navHome.addEventListener('click', () => {
        window.location.href = 'menu.html';
    });
}

const navSearch = document.querySelector('.bottom-nav .nav-item:nth-child(2)');
if (navSearch) {
    navSearch.addEventListener('click', () => {
        window.location.href = 'categorias.html';
    });
}

const navCart = document.querySelector('.cart-btn');
if (navCart) {
    navCart.addEventListener('click', () => {
        window.location.href = 'carrinho.html';
    });
}

// ============================================================
// 8. LOGIN DO ALUNO (index.html)
// ============================================================

const btnLoginAluno = document.getElementById('btn-login-aluno');
const inputLoginAlunoEmail = document.getElementById('aluno-login-email');

async function executarLoginAluno() {
    if (!inputLoginAlunoEmail) return;

    const email = inputLoginAlunoEmail.value.trim();

    if (!email) {
        alert('Digite seu e-mail.');
        inputLoginAlunoEmail.focus();
        return;
    }

    if (btnLoginAluno) {
        btnLoginAluno.disabled = true;
        btnLoginAluno.textContent = 'VERIFICANDO...';
    }

    try {
        const resposta = await fetch('login-aluno.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email: email })
        });

        const resultado = await resposta.json();

        if (resultado.sucesso && resultado.status === 'aprovado') {
            localStorage.setItem('aluno_logado', JSON.stringify(resultado.aluno));
            window.location.href = 'menu.html';
            return;
        }

        // Aluno pendente, recusado ou não cadastrado
        alert(resultado.erro || 'Não foi possível realizar o login.');

        if (btnLoginAluno) {
            btnLoginAluno.disabled = false;
            btnLoginAluno.textContent = 'ENTRAR';
        }

    } catch (erro) {
        console.error('Erro no login do aluno:', erro);
        alert('Erro de conexão com o servidor. Verifique se o XAMPP (Apache e MySQL) está ativo.');

        if (btnLoginAluno) {
            btnLoginAluno.disabled = false;
            btnLoginAluno.textContent = 'ENTRAR';
        }
    }
}

if (btnLoginAluno) {
    btnLoginAluno.addEventListener('click', executarLoginAluno);
}

if (inputLoginAlunoEmail) {
    inputLoginAlunoEmail.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            executarLoginAluno();
        }
    });
}

// ============================================================
// 9. LOGIN DO ADMINISTRADOR (loginADM.html)
// ============================================================

const btnLoginAdm = document.getElementById('btn-login-adm');
const inputAdmUser = document.getElementById('adm-user');
const inputAdmPass = document.getElementById('adm-pass');

async function executarLoginAdm() {
    if (!inputAdmUser || !inputAdmPass) return;

    const usuario = inputAdmUser.value.trim();
    const senha = inputAdmPass.value;

    if (!usuario || !senha) {
        alert('Informe usuário e senha.');
        return;
    }

    if (btnLoginAdm) {
        btnLoginAdm.disabled = true;
        btnLoginAdm.textContent = 'ENTRANDO...';
    }

    try {
        const resposta = await fetch('login-adm.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                usuario: usuario,
                senha: senha
            })
        });

        const resultado = await resposta.json();

        if (resultado.sucesso) {
            localStorage.setItem('adm_logado', JSON.stringify(resultado.admin));
            window.location.href = 'painel-adm.html';
        } else {
            alert(resultado.erro || 'Usuário ou senha inválidos.');

            if (btnLoginAdm) {
                btnLoginAdm.disabled = false;
                btnLoginAdm.textContent = 'ENTRAR';
            }
        }

    } catch (erro) {
        console.error('Erro no login ADM:', erro);
        alert('Erro de conexão com o servidor. Verifique se o XAMPP (Apache e MySQL) está ativo.');

        if (btnLoginAdm) {
            btnLoginAdm.disabled = false;
            btnLoginAdm.textContent = 'ENTRAR';
        }
    }
}

if (btnLoginAdm) {
    btnLoginAdm.addEventListener('click', executarLoginAdm);
}

if (inputAdmUser) {
    inputAdmUser.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            if (inputAdmPass) inputAdmPass.focus();
        }
    });
}

if (inputAdmPass) {
    inputAdmPass.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            executarLoginAdm();
        }
    });
}

// ============================================================
// 10. CADASTRO DO ALUNO (cadastro.html / cadastro-aluno.html)
// ============================================================

const btnCadastrarAluno = document.getElementById('btn-cadastrar-aluno');

if (btnCadastrarAluno) {
    btnCadastrarAluno.addEventListener('click', async function() {
        const inputNome = document.getElementById('aluno-nome');
        const inputEscola = document.getElementById('aluno-escola');
        const inputTurma = document.getElementById('aluno-turma');
        const inputEmail = document.getElementById('aluno-email');

        const nome = inputNome ? inputNome.value.trim() : '';
        const escola = inputEscola ? inputEscola.value.trim() : '';
        const turma = inputTurma ? inputTurma.value.trim() : '';
        const email = inputEmail ? inputEmail.value.trim() : '';

        if (!nome || !escola || !turma || !email) {
            alert('Preencha todos os campos para realizar o cadastro.');
            return;
        }

        const emailValido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailValido.test(email)) {
            alert('Digite um e-mail válido.');
            return;
        }

        btnCadastrarAluno.disabled = true;
        btnCadastrarAluno.textContent = 'CADASTRANDO...';

        try {
            const resposta = await fetch('cadastrar-aluno.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nome: nome,
                    escola: escola,
                    turma: turma,
                    email: email
                })
            });

            const resultado = await resposta.json();

            if (resultado.sucesso) {
                alert('Cadastro realizado com sucesso!\n\nAguarde a aprovação da cantina para acessar.');
                window.location.href = 'index.html';
            } else {
                alert(resultado.erro || 'Não foi possível realizar o cadastro.');
                btnCadastrarAluno.disabled = false;
                btnCadastrarAluno.textContent = 'CADASTRAR';
            }

        } catch (erro) {
            console.error('Erro ao cadastrar aluno:', erro);
            alert('Erro de conexão com o servidor. Verifique se o XAMPP (Apache e MySQL) está ativo.');
            btnCadastrarAluno.disabled = false;
            btnCadastrarAluno.textContent = 'CADASTRAR';
        }
    });
}

// ============================================================
// 11. PAINEL ADMINISTRATIVO (painel-adm.html)
// ============================================================

window.alternarAba = function(nomeAba, botaoElemento) {
    // 1. Esconde todas as abas
    document.querySelectorAll('.tab-content').forEach(aba => {
        aba.classList.remove('active');
        aba.style.display = 'none';
    });

    // 2. Remove active de todos os botões de abas
    document.querySelectorAll('.admin-tabs .tab-btn').forEach(botao => {
        botao.classList.remove('active');
    });

    // 3. Exibe a aba clicada
    const abaAlvo = document.getElementById('aba-' + nomeAba);
    if (abaAlvo) {
        abaAlvo.classList.add('active');
        abaAlvo.style.display = 'block';
    }

    // 4. Marca o botão como ativo
    if (botaoElemento) {
        botaoElemento.classList.add('active');
    } else {
        const btn = document.querySelector(`.admin-tabs button[onclick*="'${nomeAba}'"]`);
        if (btn) btn.classList.add('active');
    }

    // 5. Carrega dados sob demanda se necessário
    if (nomeAba === 'pedidos') {
        carregarPedidosPainel();
    } else if (nomeAba === 'estoque') {
        carregarEstoque();
    } else if (nomeAba === 'alunos') {
        carregarAlunos();
    }
};

window.fazerLogoutAdm = function() {
    localStorage.removeItem('adm_logado');
    window.location.href = 'index.html';
};

// --- PEDIDOS DO DIA ---
async function carregarPedidosPainel() {
    const containerPedidos = document.getElementById('lista-pedidos-chegando');
    if (!containerPedidos) return;

    try {
        const resposta = await fetch('api-pedidos.php');
        const pedidos = await resposta.json();

        if (!pedidos || pedidos.length === 0) {
            containerPedidos.innerHTML = `
                <p style="text-align:center; color:#666; padding-top:20px;">
                    🎉 Nenhum pedido pendente por enquanto!
                </p>
            `;
            return;
        }

        containerPedidos.innerHTML = '';

        pedidos.forEach(pedido => {
            let resumoItens = Array.isArray(pedido.itens)
                ? pedido.itens.map(item => `${item.quantidade}x ${item.nome}`).join(', ')
                : 'Itens do pedido';

            const linhaHTML = `
                <div class="admin-row-item" id="pedido-linha-${pedido.id}">
                    <div class="item-img">🍕</div>
                    <div class="item-details">
                        <strong>Pedido #${pedido.id} - Senha ${pedido.senha}</strong>
                        <p>${resumoItens}</p>
                        <small style="color:#999;">
                            Total: R$ ${parseFloat(pedido.total).toFixed(2).replace('.', ',')}
                        </small>
                    </div>
                    <div class="item-actions">
                        <span class="status-badge waiting">Pendente</span>
                        <button class="btn-check" onclick="finalizarPedidoNoBanco(${pedido.id})">
                            Finalizar
                        </button>
                    </div>
                </div>
            `;

            containerPedidos.innerHTML += linhaHTML;
        });

    } catch (erro) {
        console.error("Erro ao carregar pedidos:", erro);
        containerPedidos.innerHTML = `
            <p style="color:red; text-align:center;">
                Erro ao carregar os pedidos do dia.
            </p>
        `;
    }
}

async function finalizarPedidoNoBanco(idPedido) {
    if (!confirm(`Deseja marcar o pedido #${idPedido} como finalizado?`)) {
        return;
    }

    try {
        const resposta = await fetch('api-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: idPedido })
        });

        const resultado = await resposta.json();

        if (resultado.sucesso) {
            const linha = document.getElementById(`pedido-linha-${idPedido}`);
            if (linha) {
                linha.style.transition = '0.3s';
                linha.style.opacity = '0';
                setTimeout(() => {
                    linha.remove();
                    carregarPedidosPainel();
                }, 300);
            }
        } else {
            alert("Erro ao finalizar o pedido: " + (resultado.erro || 'Erro desconhecido.'));
        }

    } catch (erro) {
        console.error("Erro na finalização:", erro);
        alert("Erro de conexão ao tentar finalizar o pedido.");
    }
}

// --- ESTOQUE ---
async function carregarEstoque() {
    const containerEstoque = document.getElementById('lista-estoque');
    if (!containerEstoque) return;

    try {
        const resposta = await fetch('api-estoque.php');
        const produtos = await resposta.json();

        containerEstoque.innerHTML = '';

        if (!produtos || produtos.length === 0) {
            containerEstoque.innerHTML = '<p style="text-align:center; color:#666;">Nenhum produto cadastrado no estoque.</p>';
            return;
        }

        produtos.forEach(produto => {
            const corEstoque = produto.estoque <= 5 ? '#E30613' : '#28a745';

            const linhaHTML = `
                <div class="admin-row-item">
                    <div class="item-img" style="overflow:hidden; padding:0; border:1px solid #ccc;">
                        <img
                            src="${produto.imagem}"
                            alt="${produto.nome}"
                            style="width:100%; height:100%; object-fit:cover; border-radius:10px;"
                        >
                    </div>
                    <div class="item-details">
                        <strong>${produto.nome}</strong>
                        <p>
                            Estoque atual:
                            <span style="color:${corEstoque}; font-weight:bold;">
                                ${produto.estoque} unidades
                            </span>
                        </p>
                    </div>
                    <div class="item-actions">
                        <button
                            class="btn-edit"
                            onclick="editarEstoque(${produto.id}, '${produto.nome.replace(/'/g, "\\'")}', ${produto.estoque})"
                        >
                            Editar
                        </button>
                    </div>
                </div>
            `;

            containerEstoque.innerHTML += linhaHTML;
        });

    } catch (erro) {
        console.error("Erro ao carregar estoque:", erro);
        containerEstoque.innerHTML = `
            <p style="color:red; text-align:center;">
                Erro ao carregar o estoque.
            </p>
        `;
    }
}

async function editarEstoque(idProduto, nomeProduto, estoqueAtual) {
    const novoEstoque = prompt(
        `Digite a nova quantidade em estoque para: ${nomeProduto}`,
        estoqueAtual
    );

    if (novoEstoque === null || novoEstoque.trim() === '') {
        return;
    }

    if (isNaN(novoEstoque) || parseInt(novoEstoque) < 0) {
        alert("Por favor, digite um número válido!");
        return;
    }

    try {
        const resposta = await fetch('api-estoque.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: idProduto,
                estoque: parseInt(novoEstoque)
            })
        });

        const resultado = await resposta.json();

        if (resultado.sucesso) {
            carregarEstoque();
        } else {
            alert("Erro ao atualizar estoque: " + (resultado.erro || 'Erro desconhecido.'));
        }

    } catch (erro) {
        console.error("Erro de estoque:", erro);
        alert("Erro de conexão ao tentar atualizar o estoque.");
    }
}

// --- ALUNOS ---
async function carregarAlunos() {
    const container = document.getElementById('lista-alunos');
    if (!container) return;

    try {
        const resposta = await fetch('api-alunos.php');
        const resultado = await resposta.json();

        if (!resultado.sucesso) {
            container.innerHTML = `
                <p style="color:red; text-align:center;">
                    ${resultado.erro || 'Erro ao consultar alunos.'}
                </p>
            `;
            return;
        }

        const alunos = resultado.alunos;

        if (!alunos || alunos.length === 0) {
            container.innerHTML = `
                <p style="text-align:center; color:#666; padding:20px;">
                    Nenhum aluno cadastrado.
                </p>
            `;
            return;
        }

        container.innerHTML = '';

        alunos.forEach(aluno => {
            let statusTexto = '';
            let statusClasse = '';

            if (aluno.status === 'pendente') {
                statusTexto = 'Pendente';
                statusClasse = 'waiting';
            } else if (aluno.status === 'aprovado') {
                statusTexto = 'Aprovado';
                statusClasse = 'approved';
            } else {
                statusTexto = 'Recusado';
                statusClasse = 'refused';
            }

            let botoes = '';
            if (aluno.status === 'pendente') {
                botoes = `
                    <button class="btn-check" onclick="aprovarAluno(${aluno.id})">
                        Aprovar
                    </button>
                    <button class="btn-recusar" onclick="recusarAluno(${aluno.id})">
                        Recusar
                    </button>
                `;
            }

            const linhaHTML = `
                <div class="admin-row-item" id="aluno-${aluno.id}">
                    <div class="item-img">👤</div>
                    <div class="item-details">
                        <strong>${aluno.nome}</strong>
                        <p>${aluno.escola} • ${aluno.turma}</p>
                        <small style="color:#777;">${aluno.email}</small>
                    </div>
                    <div class="item-actions">
                        <span class="status-badge ${statusClasse}">${statusTexto}</span>
                        ${botoes}
                    </div>
                </div>
            `;

            container.innerHTML += linhaHTML;
        });

    } catch (erro) {
        console.error("Erro ao carregar alunos:", erro);
        container.innerHTML = `
            <p style="color:red; text-align:center;">
                Erro ao carregar os alunos.
            </p>
        `;
    }
}

async function aprovarAluno(id) {
    if (!confirm('Deseja aprovar este aluno?')) {
        return;
    }

    try {
        const resposta = await fetch('aprovar-aluno.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const resultado = await resposta.json();

        if (resultado.sucesso) {
            alert('Aluno aprovado com sucesso!');
            carregarAlunos();
        } else {
            alert(resultado.erro || 'Erro ao aprovar aluno.');
        }

    } catch (erro) {
        console.error("Erro ao aprovar aluno:", erro);
        alert('Erro de conexão ao aprovar o aluno.');
    }
}

async function recusarAluno(id) {
    if (!confirm('Deseja recusar este aluno?')) {
        return;
    }

    try {
        const resposta = await fetch('recusar-aluno.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const resultado = await resposta.json();

        if (resultado.sucesso) {
            alert('Aluno recusado.');
            carregarAlunos();
        } else {
            alert(resultado.erro || 'Erro ao recusar aluno.');
        }

    } catch (erro) {
        console.error("Erro ao recusar aluno:", erro);
        alert('Erro de conexão ao recusar o aluno.');
    }
}

// Inicializações do Painel ADM se os containers existirem
if (document.getElementById('lista-pedidos-chegando')) {
    carregarPedidosPainel();
    setInterval(carregarPedidosPainel, 15000);
}

if (document.getElementById('lista-estoque')) {
    carregarEstoque();
}

if (document.getElementById('lista-alunos')) {
    carregarAlunos();
}
