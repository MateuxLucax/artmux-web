<?php
$titulo = 'Obra';
require('../header.php');
?>

<main class="container">

  <?php require('menu.php') ?>

  <div class="card mb-3">
    <div class="card-header" style="text-align: right;">
      <button id="btn-excluir" class="btn btn-danger">
        <i class="fas fa-trash"></i>
      </button>
      <a id="link-alterar" class="btn btn-primary" href="#">
        <i class="fas fa-edit"></i>
      </a>
    </div>
    <div class="card-body">

      <!-- TODO animação? -->
      <p id="loading">Carregando...</p>

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
          <textarea readonly id="obra-observacoes" name="observations" class="form-control" placeholder="Sem observações"></textarea>
        </div>
      </div>

      <div id="obra-tags-container" class="mb-3 row d-none">
        <label class="col-sm-2 form-label">Tags</label>
        <div class="col-sm-10">
          <h5 id="tags-container"></h5>
        </div>
      </div>

      <div class="row">
        <label class="col-sm-2">
          Criada em
        </label>
        <div class="col-sm-10">
          <input readonly id="obra-data-criacao" class="form-control-plaintext text-muted">
        </div>
      </div>

      <div id="obra-data-atualizacao-container" class="mt-3 row d-none">
        <label class="col-sm-2">
          Atualizada em
        </label>
        <div class="col-sm-10">
          <input readonly id="obra-data-atualizacao" class="form-control-plaintext text-muted">
        </div>
      </div>

    </div>
  </div>

</main>

<?php require('../scripts.php') ?>

<script>
  const params = new URLSearchParams(location.search)
  const slug = params.get('obra')
  if (!slug) {
    agendarAlertaSwal({
      title: 'Erro',
      icon: 'warning',
      text: 'Não conseguimos abrir a página de detalhe da obra porque a URL estava incompleta'
    })
    history.back()
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
    Swal.fire({
      title: 'Erro do sistema',
      icon: 'error',
      text: 'Erro ao carregar a obra. Tente novamente mais tarde.'
    }).then(() => history.back())
  })

  function carregarObra(obra) {
    q.hide(q.id('loading'));
    q.id('obra-titulo').value = obra.title
    if (obra.observations != '') {
      q.show(q.id('obra-observacoes-container'))
      q.id('obra-observacoes').value = obra.observations
    }
    if (obra.tags.length > 0) {
      q.show(q.id('obra-tags-container'))
      carregarTags(obra.tags)
    }
    q.id('obra-img').src = 'http://localhost:4000' + obra.imagePaths.medium
    q.id('obra-img-link-full').href = 'http://localhost:4000' + obra.imagePaths.original
    q.id('obra-data-criacao').value = formatarData(new Date(obra.createdAt))
    q.id('obra-data-atualizacao').value = formatarData(new Date(obra.updatedAt))
    if (obra.createdAt != obra.updatedAt) {
      q.show(q.id('obra-data-atualizacao-container'));
    }

    // if (obra.editable) {
      habilitarBotoes(slug);
    // } else {
      // desabilitarBotoes();
    // }
  }

  function carregarTags(tags) {
    const containerTags = q.id('tags-container');
    for (const { name } of tags) {
      q.elem('span', ['badge', 'text-bg-primary', 'me-1'], containerTags, { innerText: name });
    }
  }

  function habilitarBotoes(slug) {
    q.id('link-alterar').href = `/obras/alterar.php?obra=${slug}`

    q.id('btn-excluir').onclick = ev => {
      if (ev.button != 0) return;
      Swal.fire({
        text: 'Tem certeza de que deseja excluir essa obra?',
        icon: 'question',
        showConfirmButton: true,
        confirmButtonText: 'Excluir',
        showCancelButton: true,
        cancelButtonText: 'Cancelar'
      })
      .then(result => {
        if (result.isConfirmed) {
          fazerExclusao(slug)
        }
      })
    }
  }

  function desabilitarBotoes() {
    const linkAlterar = q.id('link-alterar')
    linkAlterar.setAttribute('disabled', 'disabled')
    linkAlterar.classList.add('disabled')
    q.id('btn-excluir').setAttribute('disabled', 'disabled');
    // TODO colocar tooltip em volta explicando pq: tem publicação associada E que foi postada efetivamente em alguma rede social
  }

  function fazerExclusao(slug) {
    fetch(`http://localhost:4000/artworks/${slug}`, { method: 'DELETE' })
    .then(res => {
      if (!res.ok) throw 'Resposta não-ok'
      agendarAlertaSwal({
        text: 'Obra excluída com sucesso',
        icon: 'success'
      })
      location.assign('/obras/listar.php')
    })
    .catch(err => {
      console.error(err)
      Swal.fire({
        title: 'Erro do sistem',
        text: 'Ocorreu um erro ao excluir essa obra. Tente novamente mais tarde.',
        icon: 'error'
      })
    })
  }
</script>

<?php require('../footer.php') ?>