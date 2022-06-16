<header class="header">
    <img src="/static/img/artmux-full-logo.svg" alt="artmux logo" height="48" width="188" />

    <nav>
        <a href="/publicacoes" class="btn btn-outline-primary <?= $titulo == 'publicações' ? 'disabled' : '' ?>">publicações</a>
        <a href="/catalogo" class="btn btn-outline-primary <?= $titulo == 'catálogo' ? 'disabled' : '' ?>">catálogo</a>
        <a href="/perfil" class="btn btn-outline-primary <?= $titulo == 'perfil' ? 'disabled' : '' ?>">perfil</a>
        <button onclick="logout()" type="button" href="/sair" class="btn btn-outline-primary">sair</button>
    </nav>
</header>