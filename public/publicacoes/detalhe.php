<?php
$titulo = "";
require_once('../components/head.php');
require_once('../components/header.php');
?>

<main class="container py-5 px-4">
  <div class="card">
    <div class="card-header" style="text-align: right">
      <button id="btn-excluir" class="btn btn-danger">
        <i class="bi bi-trash-fill"></i>
      </button>
      <a id="link-alterar" class="btn btn-primary" href="#">
        <i class="bi bi-pencil-fill"></i>
      </a>
    </div>
    <div class="card-body">

      <div class="mb-3 row">
        <label class="col-sm-2 form-label">Título</label>
        <div class="col-sm-10">
          <input readonly type="text" name="titulo" class="form-control-plaintext" />
        </div>
      </div>

      <div class="mb-3 row">
        <label class="col-sm-2 form-label">Conteúdo</label>
        <div class="col-sm-10">
          <textarea readonly type="text" name="conteudo" class="form-control-plaintext"></textarea>
        </div>
      </div>

      <div class="row mb-3">
        <label for="" class="col-sm-2">Obras</label>
        <div class="col-sm-10" id="container-grid-obras"></div>
      </div>

      <div class="row">
        <label class="col-sm-2">Publicada em</label>
        <div class="col-sm-10">
          <input readonly type="text" name="data-publicacao" class="form-control-plaintext text-muted" />
        </div>
      </div>

      <div class="row mt-3 d-none" id="container-data-atualizacao">
        <label class="col-sm-2">Atualizada em</label>
        <div class="col-sm-10">
          <input readonly type="text" name="data-atualizacao" class="form-control-plaintext text-muted" />
        </div>
      </div>

    </div>
  </div>
</main>

<footer class="mx-auto my-4 nunito text-center">
  <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
</footer>

<?php require('../components/scripts.php') ?>

<script>
  function carregarPublicacao(pub) {
    document.title = `${document.title} ${pub.title}`;
    q.id('link-alterar').href = '/publicacoes/alterar.php?publicacao=' + pub.slug;
    q.id('btn-excluir').onclick = e => {
      handleExclusaoPublicacao(e, pub.slug)
    }

    const dataPublicacao = new Date(pub.createdAt);
    const dataAtualizacao = new Date(pub.updatedAt);

    q.sel('[name=titulo]').value = pub.title;
    q.sel('[name=conteudo]').value = pub.text;
    q.sel('[name=data-publicacao]').value = formatarData(dataPublicacao);
    q.sel('[name=data-atualizacao]').value = formatarData(dataAtualizacao);

    if (dataAtualizacao > dataPublicacao) {
      q.show(q.id('container-data-atualizacao'));
    }

    const gridObras = new ArtworkGrid((artwork, element) => {
      element.style['cursor'] = 'pointer';
      const linkObra = q.make('a', [], null, {
        href: '/obras/detalhe.php?obra=' + artwork.slug,
        title: 'Ver no tamanho original'
      });
      const img = q.tagIn('img', element)[0];
      q.replace(img, linkObra)
      linkObra.append(img);
    }, {
      emptyMessage: 'A publicação foi feita com nenhuma obra'
    });

    gridObras.display(pub.artworks);

    q.id('container-grid-obras').append(gridObras.element);
  }

  function handleExclusaoPublicacao(event, slug) {
    if (event.buttons != 0) return;
    event.preventDefault();
    pedirConfirmacaoExclusao(
      'Tem certeza de que deseja excluir essa publicação?',
      () => {
        excluirPublicacao(slug);
      }
    );
  }

  function excluirPublicacao(slug) {
    request.authFetch('publications/' + slug, {
        method: 'DELETE'
      })
      .then(res => {
        if (!res.ok) throw new ['Resposta não-ok', res];
        agendarAlertaSucesso('Publicação excluída com sucesso');
        location.assign('/publicacoes/');
      })
      .catch(err => {
        console.error(err);
        alertarErroSistema('Ocorreu um erro e não foi possível excluir a publicação. Tente novamente mais tarde.');
      })
  }

  const params = new URLSearchParams(location.search);
  if (!params.has('publicacao')) {
    alertarErroSistema('Página de detalhe de publicação acessada incorretamente')
      .then(() => {
        history.back()
      })
  }

  const slugPublicacao = params.get('publicacao');

  request.authFetch('publications/' + slugPublicacao)
    .then(res => {
      if (res.status != 200 && res.status != 304) throw ['Resposta não-ok', res];
      return res.json();
    })
    .then(carregarPublicacao)
    .catch(err => {
      console.error(err);
      alertarErroSistema('Ocorreu um erro ao buscar a publicação. Tente novamente mais tarde.')
        .then(() => {
          history.back();
        })
    });
</script>

<?php require('../components/footer.php') ?>