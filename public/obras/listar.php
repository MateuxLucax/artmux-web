<?php
$titulo = 'Listar obras';
require('../header.php');
?>

<body>
  <main class="container">

    <?php
    $pagMenu = 'listar';
    require('menu.php');
    ?>

    <div class="card">
      <div id="container-obras" class="card-body">

        <div id="loading">
          Carregando...
          <!-- TODO colocar uma animação aqui -->
        </div>

        <div id="obra-prototipo" class="obra card d-none">
          <img class="obra-img" src=""/>
          <p class="obra-title"></p>
        </div>

      </div>
    </div>

  </main>
</body>

<script>
  // fetch('http://localhost:4040/artworks/')
</script>

<?php require('../footer.php') ?>