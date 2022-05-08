<?php
$titulo = 'Obra';
require('../header.php');
?>

<main class="container">

  <?php require('menu.php') ?>

  <div class="card mb-3">
    <div class="card-body">

      <!-- TODO animação? -->
      <p id="loading">Carregando...</p>

      <!-- TODO nessa mesma tela cada campo é editável, dinamicamente vira input habilitado e no evento
           de blur é feita a request para atualizar o campo
           Exceto na imagem -->

      <div class="mb-4 text-center">
        <a id="obra-img-link-full" title="Ver no tamanho original">
          <img id="obra-img"/>
        </a>
      </div>
    
      <div class="mb-3 row">
        <label for="title" class="col-sm-2 form-label">Título</label>
        <div class="col-sm-10">
          <input id="obra-title" type="text" name="title" class="form-control" placeholder="Título da obra">
        </div>
      </div>

      <div class="mb-3 row">
        <label for="observations" class="col-sm-2 form-label">Observações</label>
        <div class="col-sm-10">
          <textarea id="obra-observations" name="" id="" class="form-control" placeholder="Sem observações"></textarea>
        </div>
      </div>

      <div class="row">
        <label class="col-sm-2">
          Criada em
        </label>
        <span id="obra-data-criacao" class="col-sm-10 text-muted"></span>
      </div>

      <div class="row obra-data-atualizacao-container d-none">
        <label class="col-sm-2">
          Atualizada em
        </label>
        <span id="obra-data-atualizacao" class="col-sm-10 text-muted"></span>
      </div>

    </div>
  </div>

</main>

<script>
  const params = new URLSearchParams(location.search)
  const slug = params.get('obra')
  if (!slug) {
    console.error('Não foi passado parâmetro &obra')
    // TODO alerta swal cujo botão "ok" redireciona para a página anterior
  }

  fetch(`http://localhost:4000/artworks/${slug}`)
  .then(res => {
    console.log(res)
    console.log(res)
    if (res.status != 200 && res.status != 304) {
      throw 'Resposta não-ok'
    }
    return res.json()
  })
  .then(carregarObra)
  .catch(err => {
    console.error(err)
    // TODO agendar alerta swal, redirecionar para página anterior
  })

  // TODO colocar num arquivo acessível p/ todas as páginas
  function formatarData(data) {
    const pad = (n, s) => String(s).padStart(n, '0');
    const d = pad(2, data.getDate())
    const m = pad(2, data.getMonth())
    const y = data.getFullYear()
    const h = pad(2, data.getHours())
    const i = pad(2, data.getMinutes())
    return `${d}/${m}/${y} ${h}:${i}`
  }

  function carregarObra(obra) {
    const elem = (x) => document.getElementById(x)
    elem('loading').classList.add('d-none')
    console.log(obra)
    elem('obra-title').value = obra.title
    elem('obra-observations').value = obra.observations
    elem('obra-img').src = 'http://localhost:4000' + obra.imagePaths.medium
    elem('obra-img-link-full').href = 'http://localhost:4000' + obra.imagePaths.original
    elem('obra-data-criacao').innerHTML = formatarData(new Date(obra.createdAt))
    elem('obra-data-atualizacao').innerHTML = formatarData(new Date(obra.updatedAt))
    if (obra.createdAt != obra.updatedAt) {
      elem('obra-data-atualizacao-container').classList.remove('d-none')
    }
  }
</script>

<?php require('../footer.php') ?>