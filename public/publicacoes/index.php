<?php
$titulo = 'publicações';
require_once('../components/head.php');
require_once('../components/header.php');
?>

<style>
    #publications-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(256px, 1fr));
        grid-auto-rows: minmax(256px, auto);
        grid-gap: 24px;
    }

    .publications-card {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        cursor: pointer;
        color: #fff;
        padding: 16px;
        border-radius: 8px;
        font-size: 24px;
        transition: all .25s ease;
        background-size: cover !important;
        background-repeat: no-repeat !important;
        background-position: 50% 50% !important;
    }

    .publications-card p {
        margin: 0;
        margin-top: auto;
    }

    .publications-card:hover,
    .publications-card:focus,
    .publications-card:active {
        opacity: .75;
    }

    .published-at-tags {
        display: flex;
        flex-direction: row;
    }

    .social-media-tag {
        border-radius: 50%;
        font-size: 16px;
        height: 32px;
        width: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .published-at-tags span:not(:last-child) {
        margin-right: 8px;
    }
</style>

<main class="container-fluid px-5">
    <section class="page-title py-5">
        <h3 class="text-primary mb-0"><?= $titulo ?></h3>

        <div>
            <button onclick="togglePublicationsFilterBtn()" class="btn btn-outline-primary me-3">filtrar publicações</button>
            <a class="btn btn-primary" href="/publicacoes/nova.php">nova publicação</a>
        </div>
    </section>

    <section class="d-none" id="section-filtros">
        <div class="mb-3 card">
            <div class="card-body" id="container-parametros">
            </div>
        </div>
    </section>

    <div id="msg-sem-publicacoes" class="alert alert-info d-none">
        Nenhuma das publicações cadastradas satisfaz os critérios de busca informados (ou não há publicações cadastradas).
    </div>

    <section id="card-publicacoes">
        <div id="loading" class="loading-container">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
        <section>
            <div id="container-paginacao" class="mb-3"></div>
            <section id="publications-container"></section>
        </section>
    </section>

</main>

<footer class="mx-auto my-4 nunito text-center">
    <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
</footer>

<?php require_once('../components/scripts.php'); ?>

<script>
    const opcoesOrdenacao = [{
            value: 'created_at',
            title: 'Data de criação'
        },
        {
            value: 'updated_at',
            title: 'Data de atualização'
        },
        {
            value: 'title',
            title: 'Título'
        },
        {
            value: 'text',
            title: 'Conteúdo'
        },
    ];
    const formBusca = new SearchForm(opcoesOrdenacao, 'Publicações por página', fazerBusca);

    formBusca
        .addFilter(new StringSearchFilter('text', 'Conteúdo'))
        .addFilter(new StringSearchFilter('title', 'Título'))
        .addFilter(new DateSearchFilter('created_at', 'Data de Criação'))
        .addFilter(new DateSearchFilter('created_at', 'Data de Atualização'));

    q.id('container-parametros').append(formBusca.element);
    q.id('container-paginacao').append(formBusca.paginationElement);

    formBusca.triggerFirstSearch();

    const msgSemPublicacoes = q.id('msg-sem-publicacoes');
    const cardPublicacoes = q.id('card-publicacoes');

    async function fazerBusca(ordenacao, direcao, pubPorPagina, pagina, filtros, cbNumResultados) {
        q.show(q.id('loading'));
        const busca = new URLSearchParams();
        busca.append('order', ordenacao);
        busca.append('direction', direcao);
        busca.append('perPage', pubPorPagina);
        busca.append('page', pagina);
        filtros.map(JSON.stringify).forEach(f => busca.append('filters', f));
        const url = 'publications?' + busca.toString();

        request.authFetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (res.status != 200 && res.status != 304) throw ['Resposta não-ok', res];
                return res.json()
            })
            .then(async ret => {
                let {
                    publications: publications,
                    total
                } = ret;
                total = Number(total);

                cbNumResultados(total);

                if (total == 0) {
                    q.show(msgSemPublicacoes);
                    q.hide(cardPublicacoes);
                    return;
                }

                q.hide(msgSemPublicacoes);
                q.show(cardPublicacoes);

                const publictaionsContainer = q.id('publications-container');
                q.empty(publictaionsContainer);
                const cards = [];
                for (const publication of publications) {
                    const images = [];
                    for (const artwork of publication.artworks) {
                        images.push(await imageBlobUrl(artwork.imagePaths.medium));
                    }
                    const card =
                        `<div 
                            id="publication-${publication.id}"
                            class="publications-card"
                            title="Conteúdo:  ${publication.text}"
                            onclick="location.href='/publicacoes/detalhe.php?publicacao=${publication.slug}'"
                            style="background: linear-gradient(transparent, rgba(0, 0, 0, 0.5)), url(${images[0]});"
                        >
                            <div class="published-at-tags">
                                ${publication.socialMedias.map(socialMedia => `<span class="social-media-tag" style="background-color:${socialMedia.config.btnBgColor}; color: ${socialMedia.config.btnTextColor};" title="publicada em ${socialMedia.name}">${socialMedia.config.btnIcon}</span>`).join('')}
                            </div>
                            <p>${publication.title}</p>
                        </div>`;
                    cards.push(card);
                    setInterval(() => {
                        const publicationImages = images;
                        const updateCard = q.id(`publication-${publication.id}`);
                        const random = Math.floor(Math.random() * publicationImages.length)
                        updateCard.style.background = `linear-gradient(transparent, rgba(0, 0, 0, 0.5)), url(${publicationImages[random]})`;
                    }, 5000);
                }
                q.hide(q.id('loading'));
                for (const card of cards) {
                    publictaionsContainer.insertAdjacentHTML('beforeend', card);
                }

            })
            .catch(err => {
                console.error(err);
                $message.error('Ocorreu um erro ao fazer a busca pelas publicações');
            });
    }

    const togglePublicationsFilterBtn = () => {
        const el = q.id('section-filtros');
        if (el.classList.contains('d-none')) q.show(el);
        else q.hide(el);
    }
</script>

<?php require_once('../components/footer.php') ?>