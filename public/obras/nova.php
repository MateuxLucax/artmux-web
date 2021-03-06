<?php
$titulo = "nova obra";
require_once('../components/head.php');
require_once('../components/header.php');
?>

<main class="container px-4">
  <section class="page-title py-5">
    <h3 class="text-primary mb-0"><?= $titulo ?></h3>
  </section>
  <div class="card">
    <form id="form-nova-obra">
      <div class=" card-body">
        <div class="mb-3 row">
          <label for="image" class="form-label col-sm-2">Imagem</label>
          <div class="col-sm-10">
            <input required type="file" name="image" id="image" class="form-control">
          </div>
        </div>
        <div class="mb-3 row">
          <label for="title" class="form-label col-sm-2">Título</label>
          <div class="col-sm-10">
            <input placeholder="Sem título" name="title" id="title" type="text" class="form-control" />
          </div>
        </div>
        <div class="mb-3 row">
          <label for="tags" class="form-label col-sm-2">Tags</label>
          <div class="col-sm-10">
            <input type="text" id="tags" class="form-control" />
          </div>
        </div>
        <div class="row">
          <label for="observations" class="form-label col-sm-2">Observações</label>
          <div class="col-sm-10">
            <textarea name="observations" id="observations" class="form-control"></textarea>
          </div>
        </div>
      </div>
      <div class="card-footer text-center">
        <button onclick="history.back()" type="button" class="btn btn-secondary me-4">
          Cancelar
        </button>
        <button type="submit" class="btn btn-primary">
          Adicionar
        </button>
      </div>
    </form>
  </div>
</main>

<footer class="mx-auto my-4 nunito text-center">
  <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
</footer>

<?php require('../components/scripts.php') ?>

<script>
  const tagInput = new TagInput(q.id('tags'));

  request
    .authFetch('tags')
    .then(res => {
      if (res.status != 200 && res.status != 304) throw ['Resposta não-ok', res];
      return res.json();
    })
    .then(tags => tagInput.whitelist = tags)
    .catch(err => {
      console.error(err)
      $message.error('Não foi possível carregar as suas tags. Tente novamente mais tarde.');
    });

  const form = document.getElementById('form-nova-obra')
  form.onsubmit = event => {
    event.preventDefault()
    const fd = new FormData(form);
    if (fd.get('title').trim() == '') {
      fd.delete('title');
      fd.append('title', 'Sem título');
    }
    fd.delete('tags');
    fd.append('tags', JSON.stringify(tagInput.value));
    submitNovaObra(fd);
  }

  function submitNovaObra(formData) {
    request
      .authFetch('artworks', {
        method: 'POST',
        body: formData,
      })
      .then(res => {
        if (res.status != 201) throw ['Resposta não-ok', res];
        return res.json();
      })
      .then(json => {
        const {
          slug
        } = json;
        agendarAlertaSucesso('A obra foi cadastrada com sucesso.');
        location.assign(`/obras/detalhe.php?obra=${slug}`);
      })
      .catch(err => {
        console.error(err)
        $message.error('Não conseguimos incluir a obra. Tente novamente mais tarde.')
      })
  }
</script>

<?php require('../components/footer.php'); ?>