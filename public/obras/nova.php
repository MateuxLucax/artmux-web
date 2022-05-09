<?php
$titulo = 'Nova obra';
require('../header.php');
?>

<body>
  <main class="container">

    <?php
    $pagMenu = 'nova';
    require('menu.php');
    ?>

    <div class="card">
      <form id="form-nova-obra"">
        <div class="card-body">
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
            <label for="image" class="form-label col-sm-2">Imagem</label>
            <div class="col-sm-10">
              <input required type="file" name="image" id="image" class="form-control">
            </div>
          </div>
        </div>
        <div class="card-footer text-center">
          <button type="submit" class="btn btn-lg btn-success">
            Incluir
          </button>
          <button onclick="history.back()" type="button" class="btn btn-lg btn-secondary">
            Cancelar
          </button>
        </div>
      </form>
    </div>
  </main>

</body>

<script>
  const form = document.getElementById('form-nova-obra')
  form.onsubmit = event => {
    event.preventDefault()
    submitNovaObra(new FormData(form))
  }

  function submitNovaObra(formData) {
    fetch('http://localhost:4000/artworks/', {
      method: 'POST',
      body: formData,
    })
    .then(res => {
      console.log(res)
      return res.json()
    })
    .then(console.log)
    .catch(console.error)
  }
</script>

<?php require('../footer.php'); ?>