<?php
$titulo = 'Nova obra';
require('../header.php');
?>

<body>
  <main class="container">
    <div class="card">
      <div class="card-header">
        Nova obra
      </div>
      <form id="form-nova-obra"">
        <div class="card-body">
          <div class="mb-3">
            <label for="title" class="form-label">Título</label>
            <input required name="title" id="title" type="text" class="form-control"/>
          </div>
          <div class="mb-3">
            <label for="observations" class="form-label">Observações</label>
            <textarea name="observations" id="observations" class="form-control"></textarea>
          </div>
          <div class="mb-3">
            <label for="image" class="form-label">Imagem</label>
            <input required type="file" name="image" id="image" class="form-control">
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
      mode: 'no-cors'
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