<?php
$titulo = "alterar obra";
require_once('../components/head.php');
require_once('../components/header.php');
?>

<main class="container px-4">
  <section class="page-title py-5">
    <h3 class="text-primary mb-0"><?= $titulo ?></h3>
  </section>
  <div class="card">
    <form id="form-alterar-obra" onsubmit="submitFormAlterarObra">
      <div class="card-body">
        <div class="mb-3 row">
          <label for="titulo" class="col-sm-2 form-label">Título</label>
          <div class="col-sm-10">
            <input type="text" name="title" class="form-control" id="titulo" placeholder="Sem título" />
          </div>
        </div>
        <div class="mb-3 row">
          <label for="tags" class="col-sm-2 form-label">Tags</label>
          <div class="col-sm-10">
            <input type="text" name="tags" id="tags" class="form-control" autocomplete="off">
          </div>
        </div>
        <div class="mb-3 row">
          <label for="observacoes" class="col-sm-2 form-label">Observações</label>
          <div class="col-sm-10">
            <textarea name="observations" class="form-control" id="observacoes" placeholder="Sem observações"></textarea>
          </div>
        </div>
        <div class="mb-3 row">
          <div class="col-sm-2">
            <label for="input-imagem"> Imagem </label><br>
            <small><a id="link-remover-imagem" class="text-muted" href="#">Remover</a></small>
            <small><a id="link-restaur-imagem" class="text-muted d-none" href="#">Restaurar</a></small>
          </div>
          <div class="col-sm-10">
            <a href="#" id="link-obra-full" title="Ver no tamanho original">
              <img id="img-obra" />
            </a>
            <input type="file" id="input-imagem" class="form-control d-none" />
          </div>
        </div>
      </div>
      <div class="card-footer text-center">
        <button class=" btn btn-secondary me-4" onclick="history.back()">Cancelar</button>
        <button type="submit" class="btn btn-primary">Alterar</button>
      </div>
    </form>
  </div>

</main>

<footer class="mx-auto my-4 nunito text-center">
  <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
</footer>

<?php require('../components/scripts.php') ?>

<script>
  const params = new URLSearchParams(location.search)
  const slug = params.get('obra')
  if (!slug) {
    $message.error('Erro ao carregar a obra. A URL não foi acessada corretamente. Tente novamente mais tarde.')
      .then(() => history.back());
  }

  const tagInput = new TagInput(q.id('tags'));

  request
    .authFetch('tags')
    .then(res => res.json())
    .then(tags => tagInput.whitelist = tags);


  request
    .authFetch(`artworks/${slug}`)
    .then(res => {
      if (res.status != 200 && res.status != 304) throw ['Resposta não-ok', res];
      return res.json();
    })
    .then(carregarObra)
    .catch(err => {
      console.error(err)
      $message.error('Erro ao carregar a obra. Tente novamente mais tarde.')
        .then(() => history.back());
    })

  async function carregarObra(obra) {
    q.id('titulo').value = obra.title
    q.id('observacoes').value = obra.observations
    q.id('img-obra').src = await imageBlobUrl(obra.imagePaths.thumbnail);
    q.id('link-obra-full').href = await imageBlobUrl(obra.imagePaths.original);
    tagInput.value = obra.tags;
  }

  const lnRemoverImagem = q.id('link-remover-imagem')
  const lnRestaurImagem = q.id('link-restaur-imagem')
  const inputImagem = q.id('input-imagem')
  const elemImagem = q.id('img-obra')

  lnRemoverImagem.onclick = ev => {
    if (ev.button != 0) return
    ev.preventDefault()
    q.hide(lnRemoverImagem);
    q.hide(elemImagem)
    q.show(lnRestaurImagem);
    q.show(inputImagem)
    inputImagem.setAttribute('name', 'image');
    inputImagem.setAttribute('required', 'required')
  }

  lnRestaurImagem.onclick = ev => {
    if (ev.button != 0) return
    ev.preventDefault()
    q.show(lnRemoverImagem);
    q.show(elemImagem)
    q.hide(lnRestaurImagem);
    q.hide(inputImagem)
    inputImagem.removeAttribute('name');
    inputImagem.removeAttribute('required')
  }

  const form = q.id('form-alterar-obra')
  form.onsubmit = ev => {
    ev.preventDefault();
    const fd = new FormData(form);
    if (fd.get('title').trim() == '') {
      fd.delete('title');
      fd.append('title', 'Sem título');
    }
    fd.delete('tags');
    fd.append('tags', JSON.stringify(tagInput.value))
    submitAlterarObra(fd);
  }

  function submitAlterarObra(formData) {
    request
      .authFetch(`artworks/${slug}`, {
        method: 'PATCH',
        body: formData,
      })
      .then(res => {
        if (res.status != 200) throw ['Resposta não-ok', res];
        return res.json();
      })
      .then(json => {
        const {
          slug
        } = json;
        agendarAlertaSucesso('A obra foi alterada com sucesso.');
        location.assign(`/obras/detalhe.php?obra=${slug}`)
      })
      .catch(err => {
        console.error(err)
        $message.error('Ocorreu um erro e não foi possível alterar a obra. Tente novamente mais tarde.');
      })
  }
</script>

<?php require('../components/footer.php') ?>