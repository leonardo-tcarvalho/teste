<?php
require_once '../backend/auth_check.php';

// Check if user is logged in
$user_id = require_login('login.php');
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Controle Financeiro</title> <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/style.css">
  <!-- Responsive CSS -->
  <link rel="stylesheet" href="css/responsive.css">
  <!-- Mobile Tables CSS -->
  <link rel="stylesheet" href="css/mobile-tables.css">
  <!-- Toastify CSS for notifications -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
  <!-- Date picker CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <!-- Viewport meta tag for responsive design -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <style>
    /* Mobile-specific styles */
    @media (max-width: 767.98px) {
      .mobile-header {
        display: flex !important;
        position: sticky;
        top: 0;
        z-index: 1020;
      }

      .mobile-hidden {
        display: none;
      }

      .table-responsive td.expandable-cell {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100px;
      }

      .expanded {
        white-space: normal;
        max-width: none;
      }

      .user-info {
        font-size: 0.85rem;
      }

      /* Move filter button to header in mobile view */
      .navbar .filter-btn {
        display: block !important;
      }

      .content .filter-btn {
        display: none !important;
      }
    }

    .dropdown-user {
      position: absolute;
      right: 15px;
      top: 15px;
      z-index: 1030;
    }

    /* Improved table cells for mobile */
    .mobile-view-cell {
      position: relative;
      cursor: pointer;
    }

    .mobile-view-cell .content-preview {
      max-width: 100px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .mobile-view-cell.expanded .content-preview {
      white-space: normal;
      max-width: none;
    }

    /* Login status in navbar */
    .logged-user {
      display: flex;
      align-items: center;
      color: white;
    }

    .logged-user .username {
      margin-left: 5px;
    }

    @media (max-width: 767.98px) {
      .logged-user .username {
        display: none;
      }
    }
  </style>
</head>

<body> <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">
        <i class="bi bi-wallet2 me-2"></i>Controle Financeiro
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <!-- Mobile filters button (visible only on mobile) -->
          <li class="nav-item d-lg-none">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalFiltros">
              <i class="bi bi-funnel me-1"></i>Filtros
            </a>
          </li>
          <!-- User dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($username); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalNovaMovimentacao" onclick="prepararNovaMovimentacao()">
                  <i class="bi bi-plus-circle me-2"></i>Nova Movimentação
                </a></li>
              <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Configurações</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="../backend/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- Conteúdo principal -->
  <div class="container-fluid mt-4">
    <div class="row"> <!-- Sidebar de navegação -->
      <div class="col-md-3 col-lg-2 d-md-block sidebar">
        <div class="sidebar-header">
          <h3 class="m-0">
            <i class="bi bi-wallet2 me-2"></i>Finanças
          </h3>
          <button class="sidebar-toggle d-md-none">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="position-sticky pt-3">
          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-muted">
            <span>Movimentações por Tipo</span>
          </h6>
          <ul class="nav flex-column" id="tiposNavegacao">
            <!-- Será preenchido via JavaScript -->
          </ul>
          <hr class="my-3">
          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-muted">
            <span>Relatórios</span>
          </h6>
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link" href="#">
                <i class="bi bi-bar-chart-line"></i>
                <span>Análise Mensal</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">
                <i class="bi bi-graph-up"></i>
                <span>Evolução Anual</span>
              </a>
            </li>
          </ul>
          <hr class="my-3">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link" href="#">
                <i class="bi bi-gear"></i>
                <span>Configurações</span>
              </a>
            </li>
          </ul>
        </div>
      </div> <!-- Conteúdo principal -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <!-- Mobile sidebar toggle button -->
        <button id="sidebar-toggle" class="sidebar-toggle d-md-none mb-2">
          <i class="bi bi-list"></i>
        </button>
        <!-- Cabeçalho específico de visualização -->
        <div id="viewHeader" class="row mb-3 d-none">
          <div class="col-12">
            <div class="alert alert-info d-flex align-items-center">
              <i class="bi bi-info-circle me-2"></i>
              Visualizando apenas: <strong class="ms-2" id="viewTitle"></strong>
              <a href="index.php" class="btn btn-sm btn-outline-secondary ms-auto">
                <i class="bi bi-x"></i> Limpar filtro
              </a>
            </div>
          </div>
        </div>

        <!-- Cabeçalho e filtros -->
        <div class="row mb-4">
          <div class="col-md-6">
            <h1 class="h2 mb-0">Movimentações Financeiras</h1>
            <p class="text-muted">Gerencie suas receitas e despesas</p>
          </div>
          <div class="col-md-6">
            <div class="d-flex justify-content-md-end">
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFiltros">
                <i class="bi bi-funnel me-1"></i>Filtros
              </button>
            </div>
          </div>
        </div>

        <!-- Resumo financeiro cards -->
        <div class="row mb-4">
          <div class="col-md-4 mb-3">
            <div class="card bg-success bg-opacity-25 border-0 h-100 shadow-sm">
              <div class="card-body">
                <h5 class="card-title text-success">
                  <i class="bi bi-graph-up-arrow me-2"></i>Total Receitas
                </h5>
                <h3 class="card-text" id="totalReceitas">R$ 0,00</h3>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="card bg-danger bg-opacity-25 border-0 h-100 shadow-sm">
              <div class="card-body">
                <h5 class="card-title text-danger">
                  <i class="bi bi-graph-down-arrow me-2"></i>Total Despesas
                </h5>
                <h3 class="card-text" id="totalDespesas">R$ 0,00</h3>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="card bg-primary bg-opacity-25 border-0 h-100 shadow-sm">
              <div class="card-body">
                <h5 class="card-title text-primary">
                  <i class="bi bi-wallet2 me-2"></i>Saldo
                </h5>
                <h3 class="card-text" id="saldoAtual">R$ 0,00</h3>
              </div>
            </div>
          </div>
        </div>

        <!-- Filtros aplicados -->
        <div id="filtrosAplicados" class="mb-3 d-none">
          <div class="d-flex align-items-center flex-wrap">
            <span class="me-2 text-muted">Filtros aplicados:</span>
            <div id="filtrosTags" class="d-flex flex-wrap gap-2">
              <!-- Tags de filtros serão inseridas aqui via JS -->
            </div>
            <button id="limparFiltros" class="btn btn-sm btn-light ms-auto">
              <i class="bi bi-x-circle me-1"></i>Limpar filtros
            </button>
          </div>
        </div>

        <!-- Tabela de movimentações -->
        <div class="card shadow-sm">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0" id="tabelaMovimentacoes">
                <thead class="bg-light">
                  <tr>
                    <th scope="col" class="ps-3">#</th>
                    <th scope="col">Descrição</th>
                    <th scope="col">Tipo</th>
                    <th scope="col" class="text-end">Valor</th>
                    <th scope="col">Data</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end pe-3">Ações</th>
                  </tr>
                </thead>
                <tbody id="listaMovimentacoes">
                  <!-- Dados serão carregados aqui via JavaScript -->
                  <tr>
                    <td colspan="7" class="text-center py-4">Carregando dados...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Paginação -->
        <nav aria-label="Navegação de páginas" class="mt-4">
          <ul class="pagination justify-content-center" id="paginacao">
            <!-- Paginação será inserida aqui via JavaScript -->
          </ul>
        </nav>
    </div>

    <!-- Modal de filtros -->
    <div class="modal fade" id="modalFiltros" tabindex="-1" aria-labelledby="modalFiltrosLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalFiltrosLabel">Filtrar Movimentações</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body">
            <form id="formFiltros">
              <div class="mb-3">
                <label for="filtroTipo" class="form-label">Tipo</label>
                <select class="form-select" id="filtroTipo">
                  <option value="">Todos</option>
                  <option value="Receitas">Receitas</option>
                  <option value="Cartões">Cartões</option>
                  <option value="Gastos Variados">Gastos Variados</option>
                  <option value="Gasto Fixo">Gasto Fixo</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="filtroStatus" class="form-label">Status</label>
                <select class="form-select" id="filtroStatus">
                  <option value="">Todos</option>
                  <option value="Pago">Pago</option>
                  <option value="Pendente">Pendente</option>
                  <option value="Cancelado">Cancelado</option>
                </select>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="filtroMes" class="form-label">Mês</label>
                  <select class="form-select" id="filtroMes">
                    <option value="">Todos</option>
                    <option value="1">Janeiro</option>
                    <option value="2">Fevereiro</option>
                    <option value="3">Março</option>
                    <option value="4">Abril</option>
                    <option value="5">Maio</option>
                    <option value="6">Junho</option>
                    <option value="7">Julho</option>
                    <option value="8">Agosto</option>
                    <option value="9">Setembro</option>
                    <option value="10">Outubro</option>
                    <option value="11">Novembro</option>
                    <option value="12">Dezembro</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="filtroAno" class="form-label">Ano</label>
                  <select class="form-select" id="filtroAno">
                    <option value="">Todos</option>
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
                    <option value="2026">2026</option>
                  </select>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="filtroValorMin" class="form-label">Valor Mínimo</label>
                  <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input type="number" class="form-control" id="filtroValorMin" min="0" step="0.01">
                  </div>
                </div>
                <div class="col-md-6">
                  <label for="filtroValorMax" class="form-label">Valor Máximo</label>
                  <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input type="number" class="form-control" id="filtroValorMax" min="0" step="0.01">
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="aplicarFiltros">Aplicar Filtros</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para nova movimentação -->
    <div class="modal fade" id="modalNovaMovimentacao" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="tituloModal">Nova Movimentação</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body">
            <form id="formMovimentacao">
              <input type="hidden" id="idMovimentacao">
              <div class="mb-3">
                <label for="nome" class="form-label">Nome/Descrição</label>
                <input type="text" class="form-control" id="nome" required>
                <div class="invalid-feedback">
                  Por favor, informe uma descrição.
                </div>
              </div>
              <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <div class="input-group">
                  <select class="form-select" id="tipoPreDefinido">
                    <option value="">Selecione ou Digite</option>
                    <!-- Tipos serão carregados do banco via JavaScript -->
                  </select>
                  <input type="text" class="form-control" id="tipo" required placeholder="Digite ou selecione um tipo">
                </div>
                <div class="form-text">Selecione um tipo existente ou crie um novo</div>
                <div class="invalid-feedback">
                  Por favor, informe o tipo da movimentação.
                </div>
              </div>
              <div class="mb-3">
                <label for="valor" class="form-label">Valor (R$)</label>
                <div class="input-group">
                  <span class="input-group-text">R$</span>
                  <input type="number" class="form-control" id="valor" min="0.01" step="0.01" required>
                  <div class="invalid-feedback">
                    Por favor, informe um valor válido.
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label for="dataMovimentacao" class="form-label">Data</label>
                <input type="text" class="form-control" id="dataMovimentacao" required>
                <div class="invalid-feedback">
                  Por favor, selecione uma data.
                </div>
              </div>
              <div class="mb-3">
                <label for="statusPagamento" class="form-label">Status de Pagamento</label>
                <select class="form-select" id="statusPagamento">
                  <option value="">Não definido</option>
                  <option value="Pago">Pago</option>
                  <option value="Pendente">Pendente</option>
                  <option value="Cancelado">Cancelado</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="salvarMovimentacao">Salvar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de exclusão -->
    <div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmar Exclusão</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body">
            <p>Você tem certeza que deseja excluir esta movimentação?</p>
            <p class="text-danger"><strong>Esta ação não pode ser desfeita.</strong></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" id="confirmarExclusao">Excluir</button>
          </div>
        </div>
      </div>
    </div> <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastify JS para notificações -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Flatpickr para date picker -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <!-- Sidebar JS -->
    <script src="js/sidebar.js"></script>
    <!-- Sidebar Filters JS -->
    <script src="js/sidebar-filters.js"></script>
    <!-- Main JS -->
    <script src="js/script.js"></script>
</body>

</html>