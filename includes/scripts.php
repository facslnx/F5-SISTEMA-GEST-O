<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script src="https://unpkg.com/imask"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar componentes do Materialize
        var elems = document.querySelectorAll('.sidenav');
        M.Sidenav.init(elems);

        var elems = document.querySelectorAll('.dropdown-trigger');
        M.Dropdown.init(elems);

        var elems = document.querySelectorAll('select');
        M.FormSelect.init(elems);

        var elems = document.querySelectorAll('.tooltipped');
        M.Tooltip.init(elems);
    });
</script>
