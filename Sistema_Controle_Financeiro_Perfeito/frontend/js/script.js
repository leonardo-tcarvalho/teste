/**
 * Sistema de Controle Financeiro
 * JavaScript principal para funcionalidade da interface
 */

// Configuração de endpoints da API 
const API = {
    GET_ALL: '../backend/get_movimentacoes.php',
    GET_ONE: '../backend/get_movimentacao.php',
    INSERT: '../backend/insert_movimentacao.php',
    UPDATE: '../backend/update_movimentacao.php',
    DELETE: '../backend/delete_movimentacao.php',
    GET_TIPOS: '../backend/get_tipos.php',
    GET_TOTALS: '../backend/get_totals.php' // Endpoint para obter totais por usuário
};

// Estado da aplicação
const estado = {
    movimentacoes: [],
    filtros: {
        tipo: '',
        status: '',
        mes: '',
        ano: '',
        valorMin: '',
        valorMax: '',
    },
    paginaAtual: 1,
    itensPorPagina: 10,
    totalPaginas: 1,
    edicaoAtiva: null,
    idParaExclusao: null
};

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    // Iniciar datepicker
    inicializarDatePicker();

    // Carregar tipos disponíveis para os selects
    carregarTipos();

    // Preselecionar filtro para o mês atual (sempre)
    const dataAtual = new Date();
    document.getElementById('filtroMes').value = (dataAtual.getMonth() + 1).toString();
    document.getElementById('filtroAno').value = dataAtual.getFullYear().toString();

    // Aplicar filtro do mês atual automaticamente
    estado.filtros.mes = (dataAtual.getMonth() + 1).toString();
    estado.filtros.ano = dataAtual.getFullYear().toString();

    // Carregar movimentações iniciais com filtro do mês atual
    carregarMovimentacoes();

    // Configurar eventos dos botões
    configurarEventos();

    // Atualizar a navegação conforme a visualização atual
    atualizarNavegacao();

    // Sincronizar tipo predefinido com campo de texto
    document.getElementById('tipoPreDefinido').addEventListener('change', function () {
        if (this.value) {
            document.getElementById('tipo').value = this.value;
        }
    });

    // Configurar melhorias para dispositivos móveis
    setupMobileEnhancements();
});

/**
 * Inicializa o datepicker para campos de data
 */
function inicializarDatePicker() {
    flatpickr("#dataMovimentacao", {
        dateFormat: "Y-m-d",
        locale: "pt",
        altInput: true,
        altFormat: "d/m/Y",
        disableMobile: "true"
    });
}

/**
 * Configura todos os eventos de interação com o usuário
 */
function configurarEventos() {
    // Evento para aplicar filtros
    document.getElementById('aplicarFiltros').addEventListener('click', () => {
        aplicarFiltros();
        const modalFiltros = bootstrap.Modal.getInstance(document.getElementById('modalFiltros'));
        modalFiltros.hide();
    });

    // Evento para limpar filtros
    document.getElementById('limparFiltros').addEventListener('click', () => {
        limparFiltros();
    });

    // Evento para salvar movimentação (novo ou edição)
    document.getElementById('salvarMovimentacao').addEventListener('click', () => {
        salvarMovimentacao();
    });

    // Evento para confirmação de exclusão
    document.getElementById('confirmarExclusao').addEventListener('click', () => {
        excluirMovimentacao();
    });
}

/**
 * Carrega os tipos de movimentações disponíveis do banco de dados
 */
function carregarTipos() {
    fetch(API.GET_TIPOS)
        .then(response => {
            if (!response.ok) {
                throw new Error('Falha ao carregar tipos de movimentações');
            }
            return response.json();
        })
        .then(tipos => {
            // Preencher os selects com os tipos disponíveis
            const selectFiltroTipo = document.getElementById('filtroTipo');
            const selectTipoPreDefinido = document.getElementById('tipoPreDefinido');

            // Limpar opções existentes, mantendo a primeira opção padrão
            while (selectFiltroTipo.options.length > 1) {
                selectFiltroTipo.remove(1);
            }

            while (selectTipoPreDefinido.options.length > 1) {
                selectTipoPreDefinido.remove(1);
            }

            // Adicionar os tipos retornados do servidor
            tipos.forEach(tipo => {
                // Adicionar ao filtro
                const optionFiltro = document.createElement('option');
                optionFiltro.value = tipo;
                optionFiltro.textContent = tipo;
                selectFiltroTipo.appendChild(optionFiltro);

                // Adicionar ao select do formulário
                const optionForm = document.createElement('option');
                optionForm.value = tipo;
                optionForm.textContent = tipo;
                selectTipoPreDefinido.appendChild(optionForm);
            });

            // Verificar se existe a opção para visualizar por tipo específico
            const viewType = new URLSearchParams(window.location.search).get('view');
            if (viewType) {
                document.getElementById('filtroTipo').value = viewType;
                estado.filtros.tipo = viewType;
                document.getElementById('viewTitle').textContent = viewType;
                document.getElementById('viewHeader').classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Erro ao carregar tipos:', error);
            mostrarNotificacao('Não foi possível carregar os tipos de movimentações', 'error');
        });
}

/**
 * Carrega movimentações com base nos filtros aplicados
 */
function carregarMovimentacoes() {
    // Construir URL com parâmetros de filtro
    let url = new URL(API.GET_ALL, window.location.origin);

    // Adicionar os filtros como parâmetros de query
    Object.keys(estado.filtros).forEach(chave => {
        if (estado.filtros[chave]) {
            url.searchParams.append(chave, estado.filtros[chave]);
        }
    });

    // Exibir carregamento
    document.getElementById('listaMovimentacoes').innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2 mb-0">Carregando movimentações...</p>
            </td>
        </tr>
    `;

    // Requisição AJAX
    fetch(url)
        .then(response => {
            if (response.status === 401) {
                // Unauthorized - User not logged in
                window.location.href = 'login.php';
                throw new Error('Sessão expirada. Faça login novamente.');
            }
            if (!response.ok) {
                throw new Error('Falha ao carregar dados');
            }
            return response.json();
        })
        .then(data => {
            // Check if data contains an error message
            if (data.error) {
                throw new Error(data.error);
            }

            // Guardar dados no estado
            estado.movimentacoes = Array.isArray(data) ? data : [];

            // Atualizar contadores financeiros
            atualizarTotais(estado.movimentacoes);

            // Renderizar dados na tabela
            renderizarTabela(estado.movimentacoes);

            // Atualizar paginação
            atualizarPaginacao(estado.movimentacoes);

            // Após carregar movimentações com sucesso, atualize também a navegação para sincronizar
            atualizarNavegacao();
        })
        .catch(error => {
            console.error('Erro ao carregar movimentações:', error);

            if (error.message.includes('login')) {
                // Já está redirecionando
                return;
            }

            mostrarNotificacao('Falha ao carregar movimentações: ' + error.message, 'error');
            document.getElementById('listaMovimentacoes').innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle fs-3 mb-3 d-block"></i>
                        Erro ao carregar dados. Tente novamente.
                    </td>
                </tr>
            `;
        });
}

/**
 * Atualiza os totais de receitas, despesas e saldo
 * @param {Array} dados - Lista de movimentações financeiras
 */
function atualizarTotais(dados) {
    // Se não há dados, zerar todos os totais
    if (!dados || !Array.isArray(dados) || dados.length === 0) {
        document.getElementById('totalReceitas').textContent = formatarMoeda(0);
        document.getElementById('totalDespesas').textContent = formatarMoeda(0);
        document.getElementById('saldoAtual').textContent = formatarMoeda(0);
        return;
    }

    let totalReceitas = 0;
    let totalDespesas = 0;

    // Caso especial para filtragem por tipo específico
    if (estado.filtros.tipo) {
        // Se estamos filtrando por tipo específico
        const total = dados.reduce((acc, item) => acc + parseFloat(item.valor), 0);

        if (estado.filtros.tipo === 'Receitas') {
            // Se estivermos vendo apenas receitas
            totalReceitas = total;
            // Não alteramos totalDespesas pois não temos essa informação
        } else {
            // Se estivermos vendo apenas despesas ou outra categoria
            totalDespesas = total;
            // Não alteramos totalReceitas pois não temos essa informação
        }
    } else {
        // Caso normal - calcular ambos os valores
        dados.forEach(item => {
            const valor = parseFloat(item.valor);
            if (item.tipo === 'Receitas') {
                totalReceitas += valor;
            } else {
                totalDespesas += valor;
            }
        });
    }

    const saldo = totalReceitas - totalDespesas;

    // Atualizar os elementos visuais
    document.getElementById('totalReceitas').textContent = formatarMoeda(totalReceitas);
    document.getElementById('totalDespesas').textContent = formatarMoeda(totalDespesas);
    document.getElementById('saldoAtual').textContent = formatarMoeda(saldo);

    // Mudar cor do saldo conforme valor
    if (saldo < 0) {
        document.getElementById('saldoAtual').classList.add('text-danger');
        document.getElementById('saldoAtual').classList.remove('text-success');
    } else {
        document.getElementById('saldoAtual').classList.add('text-success');
        document.getElementById('saldoAtual').classList.remove('text-danger');
    }
}

/**
 * Renderiza os dados na tabela com paginação
 */
function renderizarTabela(dados) {
    const tbody = document.getElementById('listaMovimentacoes');
    const inicio = (estado.paginaAtual - 1) * estado.itensPorPagina;
    const fim = inicio + estado.itensPorPagina;
    const dadosPaginados = dados.slice(inicio, fim);

    // Se não houver dados
    if (dados.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                    <p class="mb-0 text-muted">Nenhuma movimentação encontrada.</p>
                </td>
            </tr>
        `;
        return;
    }

    // Limpar tabela
    tbody.innerHTML = '';

    // Adicionar linhas para cada item
    dadosPaginados.forEach(item => {
        const tr = document.createElement('tr');

        // Determinar classe de status
        let statusClass = '';
        let statusLabel = item.statusPagamento || 'Não definido';

        if (item.statusPagamento === 'Pago') {
            statusClass = 'badge bg-success';
        } else if (item.statusPagamento === 'Pendente') {
            statusClass = 'badge bg-warning';
        } else if (item.statusPagamento === 'Cancelado') {
            statusClass = 'badge bg-danger';
        } else {
            statusClass = 'badge bg-secondary';
        }

        // Determinar classe para tipo
        let tipoClass = '';
        if (item.tipo === 'Receitas') {
            tipoClass = 'text-success';
        } else if (item.tipo === 'Cartões' || item.tipo === 'Gastos Variados' || item.tipo === 'Gasto Fixo') {
            tipoClass = 'text-danger';
        }

        // Formatar data
        const dataFormatada = formatarData(item.dataMovimentacao);

        tr.innerHTML = `
            <td class="ps-3">${item.id}</td>
            <td>${item.nome}</td>
            <td><span class="${tipoClass}">${item.tipo}</span></td>
            <td class="text-end">${formatarMoeda(item.valor)}</td>
            <td>${dataFormatada}</td>
            <td><span class="${statusClass}">${statusLabel}</span></td>
            <td class="text-end pe-3">
                <button class="btn btn-sm btn-outline-primary btn-action" 
                    onclick="prepararEdicao(${item.id})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger btn-action" 
                    onclick="prepararExclusao(${item.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

/**
 * Atualiza a navegação lateral com os tipos disponíveis
 */
function atualizarNavegacao() {
    // Primeiro, carregamos os totais para o mês/ano atual
    let url = new URL(API.GET_TOTALS, window.location.origin);

    // Adicionar mês e ano como parâmetros
    if (estado.filtros.mes && estado.filtros.ano) {
        url.searchParams.append('mes', estado.filtros.mes);
        url.searchParams.append('ano', estado.filtros.ano);
    }

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao carregar dados de navegação');
            }
            return response.json();
        })
        .then(data => {
            const navLinks = document.getElementById('tiposNavegacao');
            if (!navLinks) return;

            navLinks.innerHTML = '';

            // Adicionar link para visão geral
            const liGeral = document.createElement('li');
            liGeral.className = 'nav-item';

            const linkGeral = document.createElement('a');
            linkGeral.href = '#';
            linkGeral.className = `nav-link ${!estado.filtros.tipo ? 'active' : ''}`;
            linkGeral.innerHTML = `
                <i class="bi bi-grid-3x3-gap"></i>
                <span>Visão Geral</span>
                <span class="badge bg-primary rounded-pill ms-auto">${data.totalMovimentacoes || 0}</span>
            `;

            // Event listener para o link geral (limpar todos os filtros)
            linkGeral.addEventListener('click', function (e) {
                e.preventDefault();
                limparFiltros(true); // Reset completo incluindo tipo

                // Atualizar classes ativas
                document.querySelectorAll('.sidebar .nav-link').forEach(el => {
                    el.classList.remove('active');
                });
                this.classList.add('active');
            });

            liGeral.appendChild(linkGeral);
            navLinks.appendChild(liGeral);// Adicionar link para cada categoria
            if (data.categorias && Array.isArray(data.categorias)) {
                data.categorias.forEach(categoria => {
                    const li = document.createElement('li');
                    li.className = 'nav-item';

                    // Definir ícone com base no tipo
                    let icone = 'bi-tag';
                    if (categoria.nome === 'Receitas') {
                        icone = 'bi-graph-up-arrow text-success';
                    } else if (categoria.nome === 'Cartões') {
                        icone = 'bi-credit-card text-warning';
                    } else if (categoria.nome.includes('Fixo')) {
                        icone = 'bi-pin-angle text-danger';
                    }

                    // Link que aplica filtro diretamente com parâmetros na URL
                    const link = document.createElement('a');
                    link.href = '#';
                    link.className = `nav-link ${estado.filtros.tipo === categoria.nome ? 'active' : ''}`;
                    link.innerHTML = `
                            <i class="bi ${icone}"></i>
                            <span>${categoria.nome}</span>
                            <span class="badge ${categoria.nome === 'Receitas' ? 'bg-success' : 'bg-danger'} rounded-pill ms-auto">
                                ${formatarMoeda(categoria.total)}
                            </span>
                        `;

                    // Event listener para aplicar filtro quando o link for clicado
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        // Limpar filtros ativos
                        limparFiltros(false);
                        // Aplicar filtro por tipo
                        estado.filtros.tipo = categoria.nome;
                        // Preencher o select de filtro
                        document.getElementById('filtroTipo').value = categoria.nome;
                        // Carregar dados com o filtro aplicado
                        carregarMovimentacoes();
                        // Atualizar interface para mostrar o filtro aplicado
                        exibirFiltrosAplicados();

                        // Atualizar classes ativas
                        document.querySelectorAll('.sidebar .nav-link').forEach(el => {
                            el.classList.remove('active');
                        });
                        this.classList.add('active');
                    });

                    li.appendChild(link);
                    navLinks.appendChild(li);
                });
            }
        })
        .catch(error => {
            console.error('Erro ao atualizar navegação:', error);
        });
}

/**
 * Atualiza a paginação baseada no total de itens
 */
function atualizarPaginacao(dados) {
    const totalItens = dados.length;
    estado.totalPaginas = Math.ceil(totalItens / estado.itensPorPagina);

    const paginacaoElement = document.getElementById('paginacao');
    paginacaoElement.innerHTML = '';

    // Botão anterior
    const liAnterior = document.createElement('li');
    liAnterior.className = `page-item ${estado.paginaAtual === 1 ? 'disabled' : ''}`;
    liAnterior.innerHTML = `
        <a class="page-link" href="#" aria-label="Anterior" onclick="mudarPagina(${estado.paginaAtual - 1})">
            <i class="bi bi-chevron-left"></i>
        </a>
    `;
    paginacaoElement.appendChild(liAnterior);

    // Páginas numéricas
    for (let i = 1; i <= estado.totalPaginas; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === estado.paginaAtual ? 'active' : ''}`;
        li.innerHTML = `
            <a class="page-link" href="#" onclick="mudarPagina(${i})">${i}</a>
        `;
        paginacaoElement.appendChild(li);
    }

    // Botão próximo
    const liProximo = document.createElement('li');
    liProximo.className = `page-item ${estado.paginaAtual === estado.totalPaginas || estado.totalPaginas === 0 ? 'disabled' : ''}`;
    liProximo.innerHTML = `
        <a class="page-link" href="#" aria-label="Próximo" onclick="mudarPagina(${estado.paginaAtual + 1})">
            <i class="bi bi-chevron-right"></i>
        </a>
    `;
    paginacaoElement.appendChild(liProximo);
}

/**
 * Muda a página atual da paginação
 */
function mudarPagina(numeroPagina) {
    if (numeroPagina < 1 || numeroPagina > estado.totalPaginas) {
        return; // Evita páginas inválidas
    }

    estado.paginaAtual = numeroPagina;
    renderizarTabela(estado.movimentacoes);
    atualizarPaginacao(estado.movimentacoes);
}

/**
 * Aplica os filtros selecionados e recarrega os dados
 */
function aplicarFiltros() {
    // Capturar valores dos filtros
    estado.filtros.tipo = document.getElementById('filtroTipo').value;
    estado.filtros.status = document.getElementById('filtroStatus').value;
    estado.filtros.mes = document.getElementById('filtroMes').value;
    estado.filtros.ano = document.getElementById('filtroAno').value;
    estado.filtros.valorMin = document.getElementById('filtroValorMin').value;
    estado.filtros.valorMax = document.getElementById('filtroValorMax').value;

    // Resetar paginação
    estado.paginaAtual = 1;

    // Carregar dados com novos filtros
    carregarMovimentacoes();

    // Mostrar filtros aplicados
    exibirFiltrosAplicados();
}

/**
 * Exibe os filtros ativos em formato de tags
 */
function exibirFiltrosAplicados() {
    const divFiltrosAplicados = document.getElementById('filtrosAplicados');
    const divFiltrosTags = document.getElementById('filtrosTags');

    // Limpar tags anteriores
    divFiltrosTags.innerHTML = '';

    // Array para verificar se existem filtros aplicados
    const filtrosAtivos = [];

    // Verificar e adicionar tags para cada filtro
    if (estado.filtros.tipo) {
        const tag = criarTagFiltro('Tipo: ' + estado.filtros.tipo, 'tipo');
        divFiltrosTags.appendChild(tag);
        filtrosAtivos.push('tipo');
    }

    if (estado.filtros.status) {
        const tag = criarTagFiltro('Status: ' + estado.filtros.status, 'status');
        divFiltrosTags.appendChild(tag);
        filtrosAtivos.push('status');
    }

    if (estado.filtros.mes && estado.filtros.ano) {
        const nomesMeses = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];
        const nomeMes = nomesMeses[parseInt(estado.filtros.mes) - 1];
        const tag = criarTagFiltro('Data: ' + nomeMes + '/' + estado.filtros.ano, 'mes-ano');
        divFiltrosTags.appendChild(tag);
        filtrosAtivos.push('data');
    }

    if (estado.filtros.valorMin) {
        const tag = criarTagFiltro('Valor mínimo: ' + formatarMoeda(estado.filtros.valorMin), 'valorMin');
        divFiltrosTags.appendChild(tag);
        filtrosAtivos.push('valorMin');
    }

    if (estado.filtros.valorMax) {
        const tag = criarTagFiltro('Valor máximo: ' + formatarMoeda(estado.filtros.valorMax), 'valorMax');
        divFiltrosTags.appendChild(tag);
        filtrosAtivos.push('valorMax');
    }

    // Mostrar ou ocultar seção de filtros aplicados
    if (filtrosAtivos.length > 0) {
        divFiltrosAplicados.classList.remove('d-none');
    } else {
        divFiltrosAplicados.classList.add('d-none');
    }
}

/**
 * Cria uma tag de filtro aplicado
 */
function criarTagFiltro(texto, tipo) {
    const tag = document.createElement('span');
    tag.className = 'badge bg-light text-dark d-flex align-items-center';
    tag.innerHTML = `
        ${texto}
        <button type="button" class="btn-close btn-close-sm ms-2" 
            onclick="removerFiltro('${tipo}')" aria-label="Remover"></button>
    `;
    return tag;
}

/**
 * Remove um filtro específico
 */
function removerFiltro(tipo) {
    if (tipo === 'tipo') {
        estado.filtros.tipo = '';
        document.getElementById('filtroTipo').value = '';
    } else if (tipo === 'status') {
        estado.filtros.status = '';
        document.getElementById('filtroStatus').value = '';
    } else if (tipo === 'mes-ano') {
        estado.filtros.mes = '';
        estado.filtros.ano = '';
        document.getElementById('filtroMes').value = '';
        document.getElementById('filtroAno').value = '';
    } else if (tipo === 'valorMin') {
        estado.filtros.valorMin = '';
        document.getElementById('filtroValorMin').value = '';
    } else if (tipo === 'valorMax') {
        estado.filtros.valorMax = '';
        document.getElementById('filtroValorMax').value = '';
    }

    // Recarregar movimentações com os filtros atualizados
    carregarMovimentacoes();

    // Atualizar a exibição dos filtros aplicados
    exibirFiltrosAplicados();
}

/**
 * Limpa todos os filtros aplicados
 * @param {boolean} resetTipo - Se verdadeiro, limpa também o filtro de tipo
 */
function limparFiltros(resetTipo = true) {
    // Resetar os valores no estado
    if (resetTipo) {
        estado.filtros.tipo = '';
    }
    estado.filtros.status = '';
    estado.filtros.mes = '';
    estado.filtros.ano = '';
    estado.filtros.valorMin = '';
    estado.filtros.valorMax = '';

    // Resetar os valores nos elementos do formulário
    if (resetTipo) {
        document.getElementById('filtroTipo').value = '';
    }
    document.getElementById('filtroStatus').value = '';
    document.getElementById('filtroMes').value = '';
    document.getElementById('filtroAno').value = '';
    document.getElementById('filtroValorMin').value = '';
    document.getElementById('filtroValorMax').value = '';

    // A div de filtros aplicados pode ainda estar visível se houver filtro por tipo
    if (!estado.filtros.tipo) {
        document.getElementById('filtrosAplicados').classList.add('d-none');
    } else {
        exibirFiltrosAplicados();
    }

    // Recarregar movimentações com os filtros atualizados
    carregarMovimentacoes();
}

/**
 * Prepara o formulário para uma nova movimentação
 */
function prepararNovaMovimentacao() {
    // Resetar ID de edição
    estado.edicaoAtiva = null;

    // Limpar formulário
    document.getElementById('formMovimentacao').reset();
    document.getElementById('idMovimentacao').value = '';

    // Definir título do modal
    document.getElementById('tituloModal').textContent = 'Nova Movimentação';

    // Inicializar datepicker com data atual
    const dataAtual = new Date().toISOString().split('T')[0];
    const datepicker = flatpickr("#dataMovimentacao");
    datepicker.setDate(dataAtual);

    // Remover validações anteriores
    const form = document.getElementById('formMovimentacao');
    form.classList.remove('was-validated');
}

/**
 * Prepara o formulário para edição de uma movimentação existente
 */
function prepararEdicao(id) {
    // Buscar a movimentação diretamente da API para garantir dados atualizados
    fetch(`${API.GET_ONE}?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao buscar dados da movimentação');
            }
            return response.json();
        })
        .then(movimentacao => {
            if (!movimentacao || movimentacao.error) {
                mostrarNotificacao('Movimentação não encontrada', 'error');
                return;
            }

            // Guardar ID para edição
            estado.edicaoAtiva = id;

            // Preencher o formulário com os dados
            document.getElementById('idMovimentacao').value = movimentacao.id;
            document.getElementById('nome').value = movimentacao.nome;
            document.getElementById('tipo').value = movimentacao.tipo;

            // Verificar se o tipo corresponde a uma das opções predefinidas
            const selectTipo = document.getElementById('tipoPreDefinido');
            let tipoEncontrado = false;
            for (let i = 0; i < selectTipo.options.length; i++) {
                if (selectTipo.options[i].value === movimentacao.tipo) {
                    selectTipo.selectedIndex = i;
                    tipoEncontrado = true;
                    break;
                }
            }

            // Se não for um tipo predefinido, selecione a opção em branco
            if (!tipoEncontrado) {
                selectTipo.selectedIndex = 0;
            }

            document.getElementById('valor').value = movimentacao.valor;
            document.getElementById('statusPagamento').value = movimentacao.statusPagamento || '';

            // Configurar datepicker
            const datepicker = flatpickr("#dataMovimentacao");
            datepicker.setDate(movimentacao.dataMovimentacao);

            // Atualizar título do modal
            document.getElementById('tituloModal').textContent = 'Editar Movimentação';

            // Exibir o modal
            const modal = new bootstrap.Modal(document.getElementById('modalNovaMovimentacao'));
            modal.show();
        })
        .catch(error => {
            console.error('Erro ao carregar movimentação:', error);
            mostrarNotificacao('Não foi possível carregar os dados da movimentação', 'error');
        });
}

/**
 * Salva uma nova movimentação ou atualiza uma existente
 */
function salvarMovimentacao() {
    // Validar o formulário
    const form = document.getElementById('formMovimentacao');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    // Preparar objeto com dados do formulário
    const movimentacao = {
        nome: document.getElementById('nome').value.trim(),
        tipo: document.getElementById('tipo').value,
        valor: parseFloat(document.getElementById('valor').value),
        dataMovimentacao: document.querySelector("#dataMovimentacao").value,
        statusPagamento: document.getElementById('statusPagamento').value || null
    };

    // Verificar se é edição ou inserção
    let url = API.INSERT;
    let method = 'POST';

    if (estado.edicaoAtiva) {
        url = API.UPDATE;
        movimentacao.id = estado.edicaoAtiva;
    }

    // Mostrar indicador de carregamento
    const btnSalvar = document.getElementById('salvarMovimentacao');
    const textoOriginal = btnSalvar.innerHTML;
    btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...';
    btnSalvar.disabled = true;

    // Enviar requisição
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(movimentacao)
    })
        .then(response => {
            // Restaurar botão
            btnSalvar.innerHTML = textoOriginal;
            btnSalvar.disabled = false;

            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.error || 'Erro ao salvar movimentação');
                }
                return data;
            });
        })
        .then(data => {
            // Verificar se a resposta contém um erro
            if (data.error) {
                throw new Error(data.error);
            }

            // Fechar o modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovaMovimentacao'));
            modal.hide();

            // Mostrar notificação de sucesso
            const mensagem = estado.edicaoAtiva ? 'Movimentação atualizada com sucesso!' : 'Movimentação adicionada com sucesso!';
            mostrarNotificacao(mensagem, 'success');

            // Recarregar dados
            carregarMovimentacoes();
        })
        .catch(error => {
            // Restaurar botão se ainda não foi restaurado
            btnSalvar.innerHTML = textoOriginal;
            btnSalvar.disabled = false;

            console.error('Erro ao salvar:', error);
            mostrarNotificacao(error.message || 'Erro ao salvar movimentação. Tente novamente.', 'error');
        });
}

/**
 * Prepara o modal de confirmação para exclusão
 */
function prepararExclusao(id) {
    estado.idParaExclusao = id;
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarExclusao'));
    modal.show();
}

/**
 * Exclui uma movimentação após confirmação
 */
function excluirMovimentacao() {
    if (!estado.idParaExclusao) return;

    fetch(API.DELETE, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: estado.idParaExclusao })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao excluir');
            }
            return response.json();
        })
        .then(data => {
            // Fechar modal de confirmação
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarExclusao'));
            modal.hide();

            // Mostrar notificação
            mostrarNotificacao('Movimentação excluída com sucesso!', 'success');

            // Recarregar dados
            carregarMovimentacoes();
        })
        .catch(error => {
            console.error('Erro ao excluir:', error);
            mostrarNotificacao('Erro ao excluir movimentação. Tente novamente.', 'error');
        });
}

/**
 * Mostra uma notificação na tela
 */
function mostrarNotificacao(mensagem, tipo = 'success') {
    let background = '#28a745';
    let icon = 'bi-check-circle';

    if (tipo === 'error') {
        background = '#dc3545';
        icon = 'bi-x-circle';
    } else if (tipo === 'warning') {
        background = '#ffc107';
        icon = 'bi-exclamation-triangle';
    } else if (tipo === 'info') {
        background = '#17a2b8';
        icon = 'bi-info-circle';
    }

    Toastify({
        text: mensagem,
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: background,
        className: "d-flex align-items-center",
        stopOnFocus: true,
        escapeMarkup: false,
        avatar: `<i class="bi ${icon} me-2" style="font-size: 1.2rem"></i>`
    }).showToast();
}

/**
 * Formata um valor para exibição como moeda
 */
function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

/**
 * Formata uma data para o formato brasileiro
 */
function formatarData(dataString) {
    // Converter de 'YYYY-MM-DD' para 'DD/MM/YYYY'
    const partes = dataString.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return dataString;
}

/**
 * Mobile enhancements for better user experience
 */
function setupMobileEnhancements() {
    // Make expandable cells in tables work
    const expandableCells = document.querySelectorAll('.expandable-cell');
    expandableCells.forEach(cell => {
        cell.addEventListener('click', function () {
            this.classList.toggle('expanded');
        });
    });

    // Move filter button to header in mobile view
    const mobileHeader = document.querySelector('.mobile-header');
    const filterButton = document.querySelector('.desktop-filter-btn');

    if (mobileHeader && filterButton && window.innerWidth < 768) {
        // Create a clone of the filter button for mobile header
        const mobileFilterBtn = filterButton.cloneNode(true);
        mobileFilterBtn.classList.add('mobile-filter-btn');
        mobileFilterBtn.classList.remove('desktop-filter-btn');
        mobileHeader.appendChild(mobileFilterBtn);

        // Set up event listener for the new button
        mobileFilterBtn.addEventListener('click', function () {
            const filterModal = new bootstrap.Modal(document.getElementById('modalFiltros'));
            filterModal.show();
        });
    }
}

/**
 * Creates expandable cells for mobile view
 * @param {HTMLElement} container - The table container to process
 */
function createExpandableCells(container) {
    if (window.innerWidth < 768) {
        const cells = container.querySelectorAll('td:not(.actions-cell)');
        cells.forEach(cell => {
            // Skip cells that are already configured
            if (cell.classList.contains('expandable-cell')) return;

            // Check if content is longer than 100 characters
            if (cell.textContent.trim().length > 20) {
                cell.classList.add('expandable-cell');
                cell.setAttribute('title', 'Toque para expandir');
            }
        });
    }
}

/**
 * Updates table display for mobile
 * Hides less important columns and creates expandable cells
 */
function updateTableForMobile() {
    if (window.innerWidth < 768) {
        // Hide less important columns
        const hideCols = document.querySelectorAll('.mobile-hide-col');
        hideCols.forEach(col => col.style.display = 'none');

        // Add dropdown functionality for values in mobile view
        const table = document.getElementById('tabelaMovimentacoes');
        if (table) {
            createExpandableCells(table);
        }
    } else {
        // Show all columns on larger screens
        const hideCols = document.querySelectorAll('.mobile-hide-col');
        hideCols.forEach(col => col.style.display = '');
    }
}

// Run when DOM is fully loaded
document.addEventListener('DOMContentLoaded', function () {
    // Set up mobile enhancements
    setupMobileEnhancements();

    // Update table for mobile view
    updateTableForMobile();

    // Update on window resize
    window.addEventListener('resize', updateTableForMobile);

    // Run again when new data is loaded
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'childList' && mutation.target.id === 'listaMovimentacoes') {
                createExpandableCells(document.getElementById('tabelaMovimentacoes'));
            }
        });
    });

    const target = document.getElementById('listaMovimentacoes');
    if (target) {
        observer.observe(target, { childList: true });
    }
});