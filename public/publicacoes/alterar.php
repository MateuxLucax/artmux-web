<?php
$titulo = "alterar publicação";
require_once('../components/head.php');
require_once('../components/header.php');
?>

<main class="container px-4">
  <section class="page-title py-5">
    <h3 class="text-primary mb-0"><?= $titulo ?></h3>
  </section>
  <form id="form-alterar-publicacao">

    <input type="hidden" name="slug">

    <div class="card">
      <div class="card-body">

        <div class="row mb-3">
          <label class="col-sm-2">Título</label>
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
        <button class="btn btn-secondary me-4" onclick="history.back()">Cancelar</button>
        <button id="btn-alterar" disabled type="submit" class="btn btn-primary">Alterar</button>
      </div>
    </div>

  </form>

</main>

<footer class="mx-auto my-4 nunito text-center">
  <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
</footer>

<?php require('../components/scripts.php') ?>

<script>
  let artworksInput;

  function carregarPublicacao(pub) {
    q.sel('[name=slug]').value = pub.slug;
    q.sel('[name=title]').value = pub.title;
    q.sel('[name=text]').value = pub.text;
    artworksInput = new ArtworksInput(pub.artworks);
    q.id('container-input-obras').append(artworksInput.element);
    q.id('btn-alterar').removeAttribute('disabled');
  }


  const params = new URLSearchParams(location.search);
  const slugPublicacao = params.get('publicacao');
  if (!slugPublicacao) {
    $message.error('Erro ao carregar a publicação. A URL não foi acessada corretamente.')
      .then(() => history.back());
  }

  request.authFetch('publications/' + slugPublicacao)
    .then(res => {
      if (res.status != 200 && res.status != 304) throw ['Resposta não-ok', res];
      return res.json();
    })
    .then(carregarPublicacao)
    .catch(err => {
      console.error(err);
      $message.error('Erro ao carregar a publicação. Tente novamente mais tarde')
        .then(() => history.back());
    });

  const form = q.id('form-alterar-publicacao');

  form.onsubmit = e => {
    e.preventDefault();
    const form = e.target;

    const slug = form.slug.value;
    const title = form.title.value.trim() ?? 'Sem título';
    const text = form.text.value.trim();
    const artworks = artworksInput.value;

    const payload = {
      title,
      text,
      artworks
    };

    request.authFetch('publications/' + slug, {
        method: 'PATCH',
        body: JSON.stringify(payload),
        headers: {
          'Content-Type': 'application/json'
        }
      })
      .then(res => {
        if (!res.ok) throw ['Resposta não-ok', res];
        return res.json();
      })
      .then(ret => {
        const {
          slug
        } = ret;
        agendarAlertaSucesso('A publicação foi alterada com sucesso');
        location.assign('/publicacoes/detalhe.php?publicacao=' + slug);
      })
  };
</script>

<?php require('../components/footer.php') ?>