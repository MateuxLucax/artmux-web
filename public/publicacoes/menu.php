<ul class="nav mb-3">
  <li class="nav-item">
    <a href="/publicacoes/nova.php"
       class="nav-link <?= $pagMenu == 'nova' ? 'disabled': '' ?>"
    >
      <i class="fas fa-plus-circle"></i>&nbsp;
      Nova publicação
    </a>
  </li>
  <li class="nav-item">
    <a href="/publicacoes/listar.php"
       class="nav-link <?= $pagMenu == 'listar' ? 'disabled' : '' ?>"
    >
      <i class="fas fa-list"></i>&nbsp;
      Listar publicações
    </a>
  </li>
</ul>