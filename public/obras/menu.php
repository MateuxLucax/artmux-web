<ul class="nav mb-3">
  <li class="nav-item">
    <a href="/obras/nova.php"
       class="nav-link <?= $pagMenu == 'nova' ? 'disabled': '' ?>"
    >
      <i class="fas fa-plus-circle"></i>
      Nova obra
    </a>
  </li>
  <li class="nav-item">
    <a href="/obras/listar.php"
       class="nav-link <?= $pagMenu == 'listar' ? 'disabled' : '' ?>"
    >
      <i class="fas fa-image"></i>
      Listar obras
    </a>
  </li>
  <li class="nav-item">
    <a href="/obras/tags.php"
       class="nav-link <?= $pagMenu == 'tags' ? 'disabled' : '' ?>"
    >
      <i class="fas fa-tags"></i>
      Tags
    </a>
  </li>
</ul>