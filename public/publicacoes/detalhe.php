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
    <h3 class="text-primary mb-0">detalhes da publicação</h3>
  </section>

  <section class="card">
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
    <div class="card-footer text-center">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#publishModal">Publicar</button>
    </div>
  </section>

  <section id="publishedAtContainer">

  </section>

  <div class="modal fade" id="publishModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Publicar em:</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="modal-body">
          <ul class="list-group" id="publishAccessesList"></ul>
        </form>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" style="margin-right: auto;" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" id="publishBtn" class="btn btn-primary" onclick="submitPublish()">Publicar</button>
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
  var publishedAccesses = new Set();
  var publishedSocialMedias = new Set();
  var publication;
  var accesses;

  window.onload = async () => {
    try {
      const params = new URLSearchParams(location.search);
      if (!params.has('publicacao')) {
        q.hide(q.sel('.loading-screen'));
        await $message.error('Página de detalhe de publicação acessada incorretamente')
        history.back();
      }

      const slugPublicacao = params.get('publicacao');

      const {
        response,
        json
      } = await request.auth.get(`publications/${slugPublicacao}`);

      if (response.status != 200 && response.status != 304) {
        throw "Resposta nao ok"
      } else {
        carregarPublicacao(json);
      }

      accesses = await myAccesses();
      await getPublishedAt(publication.id);

      accesses.forEach(createSocialMediaListItem);
      // TODO: abrir modal para fazer publicacao se o parametro publicar existir
    } catch (_) {
      q.hide(q.sel('.loading-screen'));
      await $message.error('Ocorreu um erro ao buscar a publicação. Tente novamente mais tarde.');
      history.back();
    } finally {
      q.hide(q.sel('.loading-screen'));
    }
  }

  function carregarPublicacao(pub) {
    publication = pub;
    document.title = `${document.title} ${pub.title}`;
    q.id('link-alterar').href = `/publicacoes/alterar.php?publicacao=${pub.slug}`;
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
        href: `/obras/detalhe.php?obra=${artwork.slug}`,
        title: 'Ver no tamanho original'
      });
      const div = q.tagIn('div', element)[0];
      q.replace(div, linkObra)
      linkObra.append(div);
    }, {
      emptyMessage: 'A publicação foi feita com nenhuma obra'
    });

    gridObras.display(pub.artworks);

    q.id('container-grid-obras').append(gridObras.element);
  }

  function handleExclusaoPublicacao(event, slug) {
    if (event.buttons != 0) return;
    event.preventDefault();
    $message.confirm('Tem certeza de que deseja excluir essa publicação?')
      .then(result => {
        if (result.isConfirmed) excluirPublicacao(slug);
      });
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
        $message.error('Ocorreu um erro e não foi possível excluir a publicação. Tente novamente mais tarde.');
      })
  }

  const myAccesses = async () => {
    try {
      const {
        response,
        json
      } = await request.auth.get('accesses/all');

      if (response.status !== 200) {
        $message.warn(json.message);
      } else {
        return json;
      }
    } catch (_) {
      $message.warn('Não é possível conectar uma nova conta no momento. Tente novamente mais tarde');
    }
  }

  const createSocialMediaListItem = (socialMedia) => {
    const config = socialMedia.config;
    const accesses = socialMedia.accesses;
    const container = `${accesses.map(access => 
                          `<li class="list-group-item" style="user-select: none;">
                            <div class="form-check" style="color: ${config.btnBgColor};">
                              <input class="form-check-input" type="checkbox" ${publishedAccesses.has(access.id) && 'disabled'} value="${socialMedia.id}-${access.id}" id="checkbox-access-${access.id}">
                              <label class="form-check-label" style="cursor: pointer;" for="checkbox-access-${access.id}">
                                ${access.username} ${config.btnIcon}
                              </label>
                            </div>
                          </li>`
                      ).join('')}`;

    q.id("publishAccessesList").insertAdjacentHTML("beforeend", container);
  }

  const submitPublish = async () => {
    try {
      const checkboxes = q.all('#publishModal form input:checked');
      if (!checkboxes) return;

      q.id('publishBtn').disabled = true;

      for (let i = 0; i < checkboxes.length; i++) {
        let checkbox = checkboxes[i];
        const [socialMediaId, accessId] = checkbox.value.split("-");

        // TODO: avaliar constraints
        await publish(parseInt(socialMediaId),
          parseInt(accessId),
          publication.id,
          publication.text,
          publication.artworks.map(artwork => artwork.slug)
        );
      };

      const publishModal = bootstrap.Modal.getInstance(q.id('publishModal'))
      publishModal.hide();

      q.id('publishBtn').disabled = false;
    } catch (_) {
      $message.warn('Não foi possível realizar a publicação no momento. Tente novamente mais tarde');
    }
  }

  const publish = async (socialMediaId, accessId, publicationId, description, media) => {
    const {
      response,
      json
    } = await request.auth.post(`publications/${publicationId}/publish`, {
      socialMediaId,
      accessId,
      description,
      media
    });

    if (response.status !== 200) {
      $message.warn(json.message);
    } else {
      $message.success(json.message);
    }
  }

  const getPublishedAt = async (publicationId) => {
    try {
      q.empty(q.id('publishedAtContainer'));
      const header = ``
      const {
        response,
        json
      } = await request.auth.get(`publications/${publicationId}/published`);

      if (response.status == 200) {
        json.publishedAt.forEach(publishedAt => {
          publishedAccesses.add(publishedAt.access_id);
          publishedSocialMedias.add(publishedAt.social_media_id);
        });

        if (publishedSocialMedias.size > 0) {
          publishedSocialMedias.forEach(createCardBySocialMedia)
        } else {
          // TODO: adicionar uma msg dizendo que ainda nao foi publicada
        }

      } else throw {
        message: "oopsie"
      }
    } catch (_) {
      $message.warn("Não foi possível consultar em quais redes sociais a publicação foi feita.");
    }
  }

  const createCardBySocialMedia = (socialMediaId) => {
    const socialMedia = accesses.find(socialMedia => socialMedia.id === socialMediaId);
    const config = socialMedia.config;
    const socialMediaAccesses = socialMedia.accesses;
    const container = `
            <section class="card p-4 max-width-480 mt-4 mx-auto">
                <h2 class="text-center mb-4" style="color: ${config.btnBgColor}">${socialMedia.name}</h2>

                ${socialMediaAccesses.map(access => 
                    `<a href="${access.profilePage}" style="background-color: ${config.btnTextColor}; color: ${config.btnBgColor}; border: 1px solid ${config.btnBgColor}; flex: 20 1 auto;" class="btn btn-primary">${access.username} ${config.btnIcon}</a>`
                ).join('')}
            </section>`;

    q.id('publishedAtContainer').insertAdjacentHTML('beforeend', container);
  }
</script>

<?php require('../components/footer.php') ?>