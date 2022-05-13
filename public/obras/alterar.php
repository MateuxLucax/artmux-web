<?php
$titulo = 'Alterar obra';
require('../header.php');
?>

<main class="container">

  <?php require('menu.php') ?>

  <div class="card">
    <form id="form-alterar-obra" onsubmit="submitFormAlterarObra">
      <div class="card-body">
        <div class="mb-3 row">
          <label for="input-titulo" class="col-sm-2 form-label">Título</label>
          <div class="col-sm-10">
            <input required type="text" name="title" class="form-control" id="input-titulo" placeholder="Sem título"/>
          </div>
        </div>
        <div class="mb-3 row">
          <label for="input-observacoes" class="col-sm-2 form-label">Observações</label>
          <div class="col-sm-10">
            <textarea name="observations" class="form-control" id="input-observacoes" placeholder="Sem observações"></textarea>
          </div>
        </div>
        <div class="mb-3 row">
          <div class="col-sm-2">
            <label for="input-imagem"> Imagem </label><br>
            <small><a id="link-remover-imagem" class="text-muted" href="#">Remover</a></small>
            <small><a id="link-repor-imagem" class="text-muted d-none" href="#">Repôr</a></small>
          </div>
          <div class="col-sm-10">
            <a href="#" id="link-obra-full" title="Ver no tamanho original">
              <img id="img-obra" style="cursor: zoom-in"/>
            </a>
            <input type="file" id="input-imagem" class="form-control d-none"/>
          </div>
        </div>
      </div>
      <div class="card-footer text-center">
        <button type="submit" class="btn btn-success btn-lg"">Alterar</button>
        <button class="btn btn-secondary btn-lg" onclick="history.back()">Cancelar</button>
      </div>
    </form>
  </div>

</main>

<script>
  const params = new URLSearchParams(location.search)
  const slug = params.get('obra')
  if (!slug) {
    // TODO alerta swal, redirecionar no botão de 'ok'
  }

  fetch(`http://localhost:4000/artworks/${slug}`)
  .then(res => {
    if (res.status != 200 && res.status != 304) {
      throw 'Resposta não-ok'
    }
    return res.json()
  })
  .then(carregarObra)
  .catch(err => {
    console.error(err)
    // TODO agendar alerta swal, redirecionar no 'ok'
  })

  function carregarObra(obra) {
    q.id('input-titulo').value = obra.title
    q.id('input-observacoes').value = obra.observations
    q.id('img-obra').src = 'http://localhost:4000' + obra.imagePaths.thumbnail
    q.id('link-obra-full').href = 'http://localhost:4000' + obra.imagePaths.original
  }

  const lnRemovImagem = q.id('link-remover-imagem')
  const lnReporImagem = q.id('link-repor-imagem')
  const inputImagem = q.id('input-imagem')
  const elemImagem = q.id('img-obra')

  lnRemovImagem.onclick = ev => {
    if (ev.button != 0) return
    ev.preventDefault()
    q.hide(lnRemovImagem); q.hide(elemImagem)
    q.show(lnReporImagem); q.show(inputImagem)
    inputImagem.setAttribute('name', 'image'); inputImagem.setAttribute('required', 'required')
  }

  lnReporImagem.onclick = ev => {
    if (ev.button != 0) return
    ev.preventDefault()
    q.show(lnRemovImagem); q.show(elemImagem)
    q.hide(lnReporImagem); q.hide(inputImagem)
    inputImagem.removeAttribute('name'); inputImagem.removeAttribute('required')
  }

  const form = q.id('form-alterar-obra')
  form.onsubmit = ev => {
    ev.preventDefault();
    submitAlterarObra(new FormData(form))
  }

  function submitAlterarObra(formData) {
    console.log([...formData])
    fetch(`http://localhost:4000/artworks/${slug}`, { method:'PATCH', body:formData, })
    .then(res => res.json())  // TODO handle different status codes
    .then(json => {
      const { slug } = json;
      agendarAlertaSwal({
        icon: 'success',
        title: 'Sucesso',
        text: 'A obra foi alterada com sucesso.'
      })
      location.assign(`/obras/detalhe.php?obra=${slug}`)
    })
    .catch(err => {
      console.error(err)
      Swal.fire({
        icon: 'error',
        title: 'Erro do sistema',
        text: 'Ocorreu um erro e não foi possível alterar a obra. Tente novamente mais tarde.'
      })
    })
  }
</script>

<?php require('../footer.php') ?>