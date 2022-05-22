<?php
$titulo = 'Nova obra';
require('../header.php');
?>

<main class="container">

  <?php
  $pagMenu = 'nova';
  require('menu.php');
  ?>

  <div class="card">
    <form id="form-nova-obra"">
      <div class="card-body">
        <div class="mb-3 row">
          <label for="image" class="form-label col-sm-2">Imagem</label>
          <div class="col-sm-10">
            <input required type="file" name="image" id="image" class="form-control">
          </div>
        </div>
        <div class="mb-3 row">
          <label for="title" class="form-label col-sm-2">Título</label>
          <div class="col-sm-10">
            <input required name="title" id="title" type="text" class="form-control"/>
          </div>
        </div>
        <div class="mb-3 row">
          <label for="observations" class="form-label col-sm-2">Observações</label>
          <div class="col-sm-10">
            <textarea name="observations" id="observations" class="form-control"></textarea>
          </div>
        </div>
        <div class="row">
          <label for="tags" class="form-label col-sm-2">Tags</label>
          <div class="col-sm-10">
            <input type="text" id="tags" class="form-control"/>
          </div>
        </div>
      </div>
      <div class="card-footer text-center">
        <button type="submit" class="btn btn-success">
          Incluir
        </button>
        <button onclick="history.back()" type="button" class="btn btn-secondary">
          Cancelar
        </button>
      </div>
    </form>
  </div>
</main>

<script>
  const tagInput = new TagInput(q.id('tags'));

  fetch('http://localhost:4000/tags/')
  .then(res => {
    if (res.status != 200 && res.status != 304) throw ['Resposta não-ok', res];
    return res.json();
  })
  .then(tags => tagInput.whitelist = tags)
  .catch(err => {
    console.error(err)
    Swal.fire({
      title: 'Erro do sistema',
      text: 'Não foi possível carregar as suas tags. Tente novamente mais tarde.',
      icon: 'error'
    });
  });

  const form = document.getElementById('form-nova-obra')
  form.onsubmit = event => {
    event.preventDefault()
    const fd = new FormData(form);
    fd.delete('tags');
    fd.append('tags', JSON.stringify(tagInput.value));
    submitNovaObra(fd);
  }

  function submitNovaObra(formData) {
    fetch('http://localhost:4000/artworks/', {
      method: 'POST',
      body: formData,
    })
    .then(res => {
      if (res.status != 201) throw ['Resposta não-ok', res];
      return res.json();
    })
    .then(json => {
      const { slug } = json;
      agendarAlertaSwal({
        title: 'Sucesso',
        text: 'A obra foi cadastrada com sucesso.',
        icon: 'success'
      })
      location.assign(`/obras/detalhe.php?obra=${slug}`);
    })
    .catch(err => {
      console.error(err)
      Swal.fire({
        title: 'Erro do sistema',
        text: 'Não conseguimos incluir a obra. Tente novamente mais tarde.',
        icon: 'error'
      })
    })
  }
</script>

<?php require('../footer.php'); ?>