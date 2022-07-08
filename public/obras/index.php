<?php
$titulo = 'obras';
require_once('../components/head.php');
require_once('../components/header.php');
?>

<!-- TODO duas visualizações,
     uma em que só aparecem os nomes, um em cada linha, junto com um ícone que com hover aparece num tooltip a imagem
     e a atual, em grade mostrando cada imagem
     impl: criar ambas no DOM, alternar entre uma ou outra com um botão
     TODO: corrigir padding, ou margin ou sei lá que além que acontece entre as sections
-->

<style>
    #artworks-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(256px, 1fr));
        grid-auto-rows: minmax(256px, auto);
        grid-gap: 24px;
    }

    .artwork-card {
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

    .artwork-card p {
        margin: 0;
        margin-top: auto;
    }

    .artwork-card:hover,
    .artwork-card:focus,
    .artwork-card:active {
        opacity: .75;
    }

    .published-at-tags {
        height: 24px;
    }

    .obra:hover {
        background-color: rgba(128, 128, 128, 0.1);
    }

    .artwork-tags {
        display: flex;
        flex-direction: row;
        overflow: hidden;
    }

    .artwork-tags:hover {
        overflow-x: scroll;
    }

    .artwork-tags span:not(:last-child) {
        margin-right: 8px;
    }
</style>

<main class="container-fluid px-5">
    <section class="page-title py-5">
        <h3 class="text-primary mb-0"><?= $titulo ?></h3>

        <div>
            <a class="btn btn-outline-primary me-3" href="/tags/">tags</a>
            <button onclick="toggleArtworksFilterBtn()" class="btn btn-outline-primary me-3">filtrar obras</button>
            <a class="btn btn-primary" href="/obras/nova.php">nova obra</a>
        </div>
    </section>

    <div class="d-none" id="card-busca">
        <div class="mb-3 card">
            <div class="card-body" id="container-parametros">
            </div>
        </div>
    </div>

    <div id="loading" class="loading-container">
        <div class="spinner-border text-primary" role="status"></div>
    </div>

    <section class="d-none" id="card-obras">
        <div id="msg-sem-obras" class="alert alert-info d-none">
            Nenhuma das obras cadastradas satisfaz os critérios de busca informados.
        </div>
        <section>
            <nav id="container-paginacao" class="mb-3"></nav>
            <section id="artworks-container"></section>
        </section>
    </section>

</main>

<footer class="mx-auto my-4 nunito text-center">
    <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
</footer>

<?php require('../components/scripts.php') ?>

<script>
    'use strict';

    //
    // Inicialização dos componentes
    //

    const opcoesOrdenacao = [{
            title: 'Data de criação',
            value: 'created_at'
        },
        {
            title: 'Data de atualização',
            value: 'updated_at'
        },
        {
            title: 'Título',
            value: 'title'
        }
    ];
    const formBusca = new SearchForm(opcoesOrdenacao, 'Obras por página', fazerBusca);

    formBusca
        .addFilter(new StringSearchFilter('title', 'Título'))
        .addFilter(new DateSearchFilter('created_at', 'Data de criação'))
        .addFilter(new DateSearchFilter('updated_at', 'Data de atualização'));

    request.authFetch('tags').then(tags => tags.json()).then(tags => {
        formBusca.addFilter(new TagSearchFilter('tags', 'Tags', tags));
    });

    q.id('container-parametros').append(formBusca.element);
    q.id('container-paginacao').append(formBusca.paginationElement);

    // Busca inicial
    formBusca.triggerFirstSearch();

    /**
     * Carrega uma obra recebida da API no DOM dentro do #container-obras
     * @param {object} obra
     * @return void
     */
    async function carregarObra(artwork) {
        const image = await imageBlobUrl(artwork.imagePaths.medium);
        const card =
            `<div 
              id="artwork-${artwork.id}"
              class="artwork-card"
              title="Observação: ${artwork.observations}"
              onclick="location.href='/obras/detalhe.php?obra=${artwork.slug}'"
              style="background: linear-gradient(transparent, rgba(0, 0, 0, 0.5)), url(${image});"
            >
              <div class="artwork-tags">
                ${artwork.tags.map(tag => `<span class="badge text-bg-primary" title="${tag.name}">${tag.name}</span>`).join('')}
              </div>
              <p>${artwork.title}</p>
           </div>`;
        return card;
    }

    /**
     * Realiza a busca na API conforme os parâmetros fornecido no objeto 'busca' fornecido,
     * atualizando o DOM na página quando obtém uma resposta.
     * 
     * TODO refazer doc parametros
     * @param
     * @return void
     */
    function fazerBusca(ordenacao, direcao, obrasPorPagina, pagina, filtros, callbackNumResultados) {
        q.show(q.id('loading'));
        const buscaAPI = new URLSearchParams();
        buscaAPI.append('order', ordenacao)
        buscaAPI.append('direction', direcao)
        buscaAPI.append('perPage', obrasPorPagina)
        buscaAPI.append('page', pagina)
        filtros.forEach(filtro => buscaAPI.append('filters', JSON.stringify(filtro)))

        const msgSemObras = q.id('msg-sem-obras');
        const cardObras = q.id('card-obras');
        const containerObras = q.id('artworks-container');

        request
            .authFetch(`artworks/?${buscaAPI.toString()}`)
            .then(res => {
                if (res.status != 200 && res.status != 304) throw res;
                return res.json();
            })
            .then(async ret => {
                const {
                    total: totalObras,
                    artworks: obras
                } = ret;
                if (obras.length == 0) {
                    q.hide(cardObras);
                    q.show(msgSemObras);
                } else {
                    q.empty(containerObras);
                    q.show(cardObras);
                    q.hide(msgSemObras);

                    const cards = [];
                    for (const obra of obras) {
                        cards.push(await carregarObra(obra));
                    }
                    console.log(cards);
                    q.hide(q.id('loading'));
                    for (const card of cards) {
                        q.id('artworks-container').insertAdjacentHTML('beforeend', card);
                    }

                    callbackNumResultados(totalObras);
                }
            })
            .catch(err => {
                console.error(err)
                $message.error('Não conseguimos carregar as suas obras. Tente novamente mais tarde.')
                    .then(history.back)
            })
    }

    const toggleArtworksFilterBtn = () => {
        const el = q.id('card-busca');
        if (el.classList.contains('d-none')) q.show(el);
        else q.hide(el);
    }
</script>

<?php require('../components/footer.php') ?>