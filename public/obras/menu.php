<!-- TODO: refazer componente -->
<ul class="nav mb-3">
  <li class="nav-item">
    <a href="/obras/nova.php" class="nav-link <?= $titulo == 'nova obra' ? 'disabled' : '' ?>">
      <i class="bi bi-plus-circle"></i>&nbsp;
      Nova obra
    </a>
  </li>
  <li class="nav-item">
    <a href="/obras/" class="nav-link <?= $pagMenu == 'listar' ? 'disabled' : '' ?>">
      <i class="bi bi-card-image"></i>&nbsp;
      Listar obras
    </a>
  </li>
  <li class="nav-item">
    <a href="/tags/" class="nav-link <?= $pagMenu == 'tags' ? 'disabled' : '' ?>">
      <i class="bi bi-tag"></i>&nbsp;
      Tags
    </a>
  </li>
</ul>