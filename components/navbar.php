<?php
// Definir a página atual
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Navbar -->
<nav class="black">
    <div class="nav-wrapper container">
        <a href="../index.php" class="brand-logo">F5 GESTÃO</a>
        <a href="#" data-target="mobile-nav" class="sidenav-trigger"><i class="material-icons">menu</i></a>
        <ul class="right hide-on-med-and-down">
            <li <?php echo $currentPage == 'index.php' ? 'class="active"' : ''; ?>>
                <a href="../index.php">Dashboard</a>
            </li>
            <li <?php echo $currentPage == 'clients.php' ? 'class="active"' : ''; ?>>
                <a href="../clients/index.php">Clientes</a>
            </li>
            <li <?php echo $currentPage == 'services.php' ? 'class="active"' : ''; ?>>
                <a href="../services/index.php">Serviços</a>
            </li>
            <li><a href="../logout.php">Sair</a></li>
        </ul>
    </div>
</nav>

<!-- Mobile Navigation -->
<ul class="sidenav" id="mobile-nav">
    <li <?php echo $currentPage == 'index.php' ? 'class="active"' : ''; ?>>
        <a href="../index.php">Dashboard</a>
    </li>
    <li <?php echo $currentPage == 'clients.php' ? 'class="active"' : ''; ?>>
        <a href="../clients/index.php">Clientes</a>
    </li>
    <li <?php echo $currentPage == 'services.php' ? 'class="active"' : ''; ?>>
        <a href="../services/index.php">Serviços</a>
    </li>
    <li><a href="../logout.php">Sair</a></li>
</ul>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.sidenav');
    var instances = M.Sidenav.init(elems);
});
</script>
