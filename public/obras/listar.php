<?php
$titulo = 'Listar obras';
require('../header.php');
?>

<!-- TODO duas visualizações,
     uma em que só aparecem os nomes, um em cada linha, junto com um ícone que com hover aparece num tooltip a imagem
     e a atual, em grade mostrando cada imagem
-->

<style>
  .obra:hover {
    background-color: rgba(128, 128, 128, 0.1);
  }
</style>

<body>
  <main class="container">

    <?php
    $pagMenu = 'listar';
    require('menu.php');
    ?>

    <div class="card">
      <div id="container-obras" class="card-body"
           style="display: grid; grid-template-columns: 1fr 1fr 1fr; grid-gap: 20px;"
      >

        <div id="loading">
          Carregando...
          <!-- TODO colocar uma animação aqui -->
        </div>

        <div id="obra-prototipo" class="obra card text-center d-none" style="padding: 10px">
          <div class="text-center">
            <a class="obra-link" href="">
              <img style="max-width: 256px; max-height: 256px; object-fit: contain;" class="obra-img" src=""/>
            </a>
          </div>
          <p class="obra-title"></p>
        </div>

      </div>
    </div>

  </main>
</body>

<script>
  const elem = (id) => document.getElementById(id)

  fetch('http://localhost:4000/artworks/')
  .then(res => {
    if (res.status != 200 && res.status != 304) {
      throw res
    }
    return res.json()
  })
  .then(obras => {
    elem('loading').classList.add('d-none')
    obras.forEach(carregarObra)
  })
  .catch(err => {
    console.error(err)
  })

  const containerObras = elem('container-obras')
  const obraPrototipo = elem('obra-prototipo')

  function carregarObra(obra) {
    const elemObra = obraPrototipo.cloneNode(true)
    elemObra.classList.remove('d-none')
    elemObra.removeAttribute('id')
    elemObra.getElementsByClassName('obra-img')[0].src = 'http://localhost:4000' + obra.imagePaths.thumbnail
    elemObra.getElementsByClassName('obra-title')[0].innerText = obra.title
    elemObra.getElementsByClassName('obra-link')[0].href = '/obras/detalhe.php?obra=' + obra.slug
    containerObras.append(elemObra)
  }

</script>

<?php require('../footer.php') ?>