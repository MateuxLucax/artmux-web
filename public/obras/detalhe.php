<?php
$titulo = 'Obra';
require('../header.php');
?>

<main class="container">

  <?php require('menu.php') ?>

  <div class="card mb-3">
    <div class="card-header text-right">
      <button class="btn btn-danger">
        <i class="fas fa-trash"></i>
      </button>
      <a id="link-alterar" class="btn btn-primary" href="#">
        <i class="fas fa-edit"></i>
      </a>
    </div>
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
        <label for="title=" class="col-sm-2 form-label">Título</label>
        <div class="col-sm-10">
          <input readonly id="obra-titulo" type="text" name="title" class="form-control-plaintext" placeholder="Título da obra">
        </div>
      </div>

      <div id="obra-observacoes-container" class="mb-3 row d-none">
        <label class="col-sm-2 form-label">Observações</label>
        <div class="col-sm-10">
          <textarea readonly id="obra-observacoes" name="observations" class="form-control-plaintext" placeholder="Sem observações"></textarea>
        </div>
      </div>

      <div class="row">
        <label class="col-sm-2">
          Criada em
        </label>
        <span id="obra-data-criacao" class="col-sm-10 text-muted"></span>
      </div>

      <div id="obra-data-atualizacao-container" class="mt-3 row d-none">
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
    if (res.status != 200 && res.status != 304) {
      throw 'Resposta não-ok'
    }
    return res.json()
  })
  .then(carregarObra)
  .catch(err => {
    console.error(err)
    // TODO agendar alerta swal, redirecionar para página anterior clicando no 'ok'
  })

  // TODO colocar num arquivo acessível p/ todas as páginas


  function carregarObra(obra) {
    q.id('loading').classList.add('d-none')
    q.id('obra-titulo').value = obra.title
    if (obra.observations != '') {
      q.id('obra-observacoes-container').classList.remove('d-none')
      q.id('obra-observacoes').value = obra.observations
    }
    q.id('obra-img').src = 'http://localhost:4000' + obra.imagePaths.medium
    q.id('obra-img-link-full').href = 'http://localhost:4000' + obra.imagePaths.original
    q.id('obra-data-criacao').innerHTML = formatarData(new Date(obra.createdAt))
    q.id('obra-data-atualizacao').innerHTML = formatarData(new Date(obra.updatedAt))
    if (obra.createdAt != obra.updatedAt) {
      q.show(q.id('obra-data-atualizacao-container'))
    }
    q.id('link-alterar').href = `/obras/alterar.php?obra=${slug}`
  }
</script>

<?php require('../footer.php') ?>