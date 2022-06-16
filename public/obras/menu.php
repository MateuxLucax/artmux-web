<ul class="nav mb-3">
  <li class="nav-item">
    <a href="/obras/nova.php"
       class="nav-link <?= $pagMenu == 'nova' ? 'disabled': '' ?>"
    >
      <!-- TODO: migrate to bootstrap icons -->
      <i class="fas fa-plus-circle"></i>&nbsp;
      Nova obra
    </a>
  </li>
  <li class="nav-item">
    <a href="/obras/listar.php"
       class="nav-link <?= $pagMenu == 'listar' ? 'disabled' : '' ?>"
    >
      <!-- TODO: migrate to bootstrap icons -->
      <i class="fas fa-image"></i>&nbsp;
      Listar obras
    </a>
  </li>
  <li class="nav-item">
    <a href="/obras/tags.php"
       class="nav-link <?= $pagMenu == 'tags' ? 'disabled' : '' ?>"
    >
      <!-- TODO: migrate to bootstrap icons -->
      <i class="fas fa-tags"></i>&nbsp;
      Tags
    </a>
  </li>
</ul>