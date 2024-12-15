<?php
// Definir a página atual
$currentPage = basename($_SERVER['PHP_SELF']);
$basePath = str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 1);

// Verificar se o usuário está logado
if (!isset($_SESSION['user'])) {
    header('Location: ' . $basePath . 'login.php');
    exit;
}

// Pegar informações do usuário
$user = $_SESSION['user'];
$userRole = isset($user['role']) ? $user['role'] : '';
$isAdmin = $userRole === 'admin';
?>

<style>
    nav {
        background-color: #000 !important;
    }
    nav .brand-logo, nav ul a {
        color: #fff !important;
    }
    nav i.material-icons {
        color: #ff0000;
    }
    .dropdown-content {
        background-color: #000;
    }
    .dropdown-content li > a {
        color: #fff !important;
    }
    .dropdown-content li > a:hover {
        background-color: rgba(255, 0, 0, 0.1);
    }
    .sidenav {
        background-color: #000;
    }
    .sidenav li > a {
        color: #fff;
    }
    .sidenav li > a > i.material-icons {
        color: #ff0000;
    }
    .sidenav .user-view {
        padding: 20px 20px 0;
        margin-bottom: 0;
    }
    .sidenav .user-view .background {
        background-color: #1a1a1a;
    }
</style>

<!-- Dropdown do Perfil -->
<ul id="user-dropdown" class="dropdown-content">
    <li>
        <a href="<?php echo $basePath; ?>profile.php">
            <i class="material-icons">person</i>Perfil
        </a>
    </li>
    <li class="divider"></li>
    <li>
        <a href="<?php echo $basePath; ?>logout.php" class="red-text">
            <i class="material-icons">exit_to_app</i>Sair
        </a>
    </li>
</ul>

<!-- Menu Mobile -->
<ul class="sidenav" id="mobile-nav">
    <li>
        <div class="user-view">
            <div class="background"></div>
            <a href="<?php echo $basePath; ?>profile.php">
                <span class="white-text name"><?php echo htmlspecialchars($user['name']); ?></span>
            </a>
            <a href="<?php echo $basePath; ?>profile.php">
                <span class="white-text email"><?php echo htmlspecialchars($user['email']); ?></span>
            </a>
        </div>
    </li>
    <li>
        <a href="<?php echo $basePath; ?>services/index.php">
            <i class="material-icons">build</i>Serviços
        </a>
    </li>
    <li>
        <a href="<?php echo $basePath; ?>clients/index.php">
            <i class="material-icons">people</i>Clientes
        </a>
    </li>
    <li>
        <a href="<?php echo $basePath; ?>invoices/index.php">
            <i class="material-icons">receipt</i>Faturas
        </a>
    </li>
    <?php if ($isAdmin): ?>
    <li>
        <a href="<?php echo $basePath; ?>users/index.php">
            <i class="material-icons">group</i>Usuários
        </a>
    </li>
    <?php endif; ?>
    <li><div class="divider"></div></li>
    <li>
        <a href="<?php echo $basePath; ?>profile.php">
            <i class="material-icons">person</i>Perfil
        </a>
    </li>
    <li>
        <a href="<?php echo $basePath; ?>logout.php" class="red-text">
            <i class="material-icons">exit_to_app</i>Sair
        </a>
    </li>
</ul>

<!-- Header Principal -->
<header class="main-header">
    <nav class="black">
        <div class="nav-wrapper">
            <div class="container">
                <a href="<?php echo $basePath; ?>index.php" class="brand-logo">
                    <img src="<?php echo $basePath; ?>assets/images/f52.png" alt="F5 Sistema" style="height: 55px; margin-top: 15px;">
                </a>
                <a href="#" data-target="mobile-nav" class="sidenav-trigger"><i class="material-icons">menu</i></a>
                <ul class="right hide-on-med-and-down">
                    <li>
                        <a href="<?php echo $basePath; ?>services/index.php">
                            <i class="material-icons left">build</i>Serviços
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $basePath; ?>clients/index.php">
                            <i class="material-icons left">people</i>Clientes
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $basePath; ?>invoices/index.php">
                            <i class="material-icons left">receipt</i>Faturas
                        </a>
                    </li>
                    <?php if ($isAdmin): ?>
                    <li>
                        <a href="<?php echo $basePath; ?>users/index.php">
                            <i class="material-icons left">group</i>Usuários
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a class="dropdown-trigger" href="#!" data-target="user-dropdown">
                            <i class="material-icons left">person</i>
                            <?php echo htmlspecialchars($user['name']); ?>
                            <i class="material-icons right">arrow_drop_down</i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dropdowns = document.querySelectorAll('.dropdown-trigger');
    M.Dropdown.init(dropdowns, {
        constrainWidth: false,
        coverTrigger: false
    });

    var sidenavs = document.querySelectorAll('.sidenav');
    M.Sidenav.init(sidenavs);
});
</script>