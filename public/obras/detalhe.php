<?php
$titulo = "";
require_once('../components/head.php');
require_once('../components/header.php');
?>

<div class="loading-screen">
  <div class="spinner-border text-light" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>
</div>

<main class="container px-4">
  <section class="page-title py-5">
    <h3 class="text-primary mb-0">detalhes da obra</h3>
  </section>

  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between">
      <button id="toggle-artwork-btn" onclick="toggleArtworkImg()" class="btn btn-secondary" title="Ocultar imagem">
        <i class="bi bi-eye-slash"></i>
      </button>
      <div>
        <span id="container-btn-excluir">
          <button id="btn-excluir" class="btn btn-danger me-2">
            <i class="bi bi-trash-fill"></i>
          </button>
        </span>
        <a id="link-alterar" class="btn btn-primary" href="#">
          <i class="bi bi-pencil-fill"></i>
        </a>
      </div>
    </div>
    <div class="card-body">

      <div class="mb-4 text-center" id="artwork-img-container">
        <a id="obra-img-link-full" title="Ver no tamanho original">
          <img id="obra-img" style="border-radius: 4px;" />
        </a>
      </div>

      <div class="mb-3 row">
        <label for="title" class="col-sm-2 form-label">Título</label>
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

  <div class="card mb-3">
    <div class="card-header">
      Publicações em que esta obra aparece
    </div>
    <div class="card-body">
      <div id="msg-sem-publicacoes" class="d-none alert alert-info mb-0">
        Essa obra não aparece em nenhuma publicação.
      </div>
      <table id="table-publicacoes" class="d-none table table-hover">
        <thead>
          <tr>
            <th>Título</th>
            <th>Conteúdo</th>
            <!--TODO quando tivermos publicações nas redes sociais, trocar por data de publicação-->
            <th>Data atualização</th>
          </tr>
        </thead>
        <tbody id="tbody-publicacoes">
        </tbody>
      </table>
    </div>
  </div>

</main>

<footer class="mx-auto my-4 nunito text-center">
  <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
</footer>

<?php require('../components/scripts.php') ?>

<script>
  var slug;

  window.onload = async () => {
    try {
      const params = new URLSearchParams(location.search)
      slug = params.get('obra')

      if (!slug) {
        agendarAlerta({
          title: 'Erro',
          icon: 'warning',
          text: 'Não conseguimos abrir a página de detalhe da obra porque a URL estava incompleta'
        })
        history.back()
      }

      const {
        response,
        json
      } = await request.auth.get(`artworks/${slug}?with=publications`);

      if (response.status != 200 && response.status != 304) {
        throw 'Resposta não-ok';
      } else {
        carregarObra(json);
      }
    } catch (_) {
      await $message.error('Erro ao carregar a obra. Tente novamente mais tarde.')
      history.back();
    } finally {
      q.hide(q.sel('.loading-screen'));
    }
  }

  async function carregarObra(obra) {
    document.title = `${document.title} ${obra.title}`;
    q.id('obra-titulo').value = obra.title;
    if (obra.observations != '') {
      q.show(q.id('obra-observacoes-container'));
      q.id('obra-observacoes').value = obra.observations;
    }
    if (obra.tags.length > 0) {
      q.show(q.id('obra-tags-container'));
      carregarTags(obra.tags);
    }
    q.id('obra-img').src = await imageBlobUrl(obra.imagePaths.medium);
    q.id('obra-img-link-full').href = await imageBlobUrl(obra.imagePaths.original);
    q.id('obra-data-criacao').value = formatarData(new Date(obra.createdAt));
    q.id('obra-data-atualizacao').value = formatarData(new Date(obra.updatedAt));
    if (obra.createdAt != obra.updatedAt) {
      q.show(q.id('obra-data-atualizacao-container'));
    }

    const linkAlterar = q.id('link-alterar');
    const btnExcluir = q.id('btn-excluir');

    linkAlterar.href = `/obras/alterar.php?obra=${slug}`

    if (obra.deletable) {
      btnExcluir.onclick = ev => {
        if (ev.button != 0) return;
        $message.confirm('Tem certeza de que deseja excluir essa obra?')
          .then(result => {
            if (result.isConfirmed) fazerExclusao(slug);
          });
      }
    } else {
      btnExcluir.setAttribute('disabled', 'disabled');
      const container = q.id('container-btn-excluir');
      container.setAttribute('title', 'A obra não pode ser excluída porque faz parte de alguma publicação');
      new bootstrap.Tooltip(container);
    }

    carregarPublicacoes(obra.publications);
  }

  function carregarTags(tags) {
    const containerTags = q.id('tags-container');
    for (const {
        name
      } of tags) {
      q.make('span', ['badge', 'text-bg-primary', 'me-1'], containerTags, {
        innerText: name
      });
    }
  }

  function carregarPublicacoes(pubs) {
    if (pubs.length == 0) {
      q.show(q.id('msg-sem-publicacoes'));
      return;
    }
    q.show(q.id('table-publicacoes'));
    const tbody = q.id('tbody-publicacoes');
    for (const pub of pubs) {
      const tr = q.make('tr', [], tbody);

      const tdTitulo = q.make('td', [], tr);
      q.make('a', [], tdTitulo, {
        innerText: pub.title,
        href: '/publicacoes/detalhe.php?publicacao=' + pub.slug
      });

      q.make('td', [], tr, {
        innerText: pub.text
      });
      // TODO vvv GMT-0300
      const dataAtualizacao = new Date(pub.createdAt);
      q.make('td', [], tr, {
        innerText: formatarData(dataAtualizacao)
      });
    }
  }

  function fazerExclusao(slug) {
    request
      .authFetch(`artworks/${slug}`, {
        method: 'DELETE'
      })
      .then(res => {
        if (!res.ok) throw 'Resposta não-ok'
        agendarAlertaSucesso('Obra excluída com sucesso');
        location.assign('/obras/')
      })
      .catch(err => {
        console.error(err)
        $message.error('Ocorreu um erro ao excluir essa obra. Tente novamente mais tarde.');
      })
  }

  const toggleArtworkImg = () => {
    const btnEl = q.id('toggle-artwork-btn');
    if (q.sel('#toggle-artwork-btn .bi-eye-slash')) {
      q.hide(q.id('artwork-img-container'));
      q.empty(btnEl);
      q.make('i', ['bi', 'bi-eye'], btnEl);
      btnEl.title = 'Mostrar imagem';
    } else {
      q.show(q.id('artwork-img-container'));
      q.empty(btnEl);
      q.make('i', ['bi', 'bi-eye-slash'], btnEl);
      btnEl.title = 'Ocultar imagem';
    }
  }
</script>

<?php require('../components/footer.php') ?>