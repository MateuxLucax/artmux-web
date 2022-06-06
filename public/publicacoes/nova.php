<?php $titulo = 'Nova publicação'; require('../header.php') ?>

<main class="container">
  <?php $pagMenu = 'nova'; require('menu.php'); ?>

  <form method="" action="">
    <div class="card">
      <div class="card-body">

        <div class="row mb-3">
          <label class="form-label col-sm-2">Título</label>
          <div class="col-sm-10">
            <input type="text" name="title" placeholder="Sem título" class="form-control">
          </div>
        </div>

        <div class="row mb-3">
          <label class="form-label col-sm-2">Conteúdo</label>
          <div class="col-sm-10">
            <textarea required name="text" class="form-control"></textarea>
          </div>
        </div>

        <div class="row mb-3">
          <label class="form-label col-sm-2">Obras</label>
          <div class="col-sm-10" id="container-input-obras">
          </div>
        </div>

      </div>
      <div class="card-footer text-center">
        <button type="submit" class="btn btn-success">Incluir</button>
        <button class="btn btn-secondary" onclick="history.back()">Cancelar</button>
      </div>
    </div>
  </form>

</main>

<?php require('../scripts.php') ?>

<script>
  const artworksInput = new ArtworksInput();
  q.id('container-input-obras').append(artworksInput.element);

  const form = q.sel('form');

  form.onsubmit = event => {
    event.preventDefault();
    let title = form.title.value.trim();
    if (title == '') title = 'Sem título';
    const text = form.text.value.trim();
    const artworks = artworksInput.value;
    const body = { title, text, artworks };

    request
    .authFetch('publications', {
      method: 'POST',
      body: JSON.stringify(body),
      headers: { 'Content-Type': 'application/json' }
    })
    .then(res => {
      if (res.status != 201) throw ['Resposta não-ok', res];
      return res.json();
    })
    .then(json => {
      const { slug } = json;
      agendarAlertaSwal({
        text: 'Publicação criada com sucesso',
        icon: 'success'
      });
      location.assign(`/publicacoes/detalhe.php?pub=${slug}`);
    })
    .catch(err => {
      console.error(err);
      alertarErroSistema('Ocorreu um erro ao criar a publicação. Tente novamente mais tarde.');
    })
  }
</script>

<?php require('../footer.php') ?>
