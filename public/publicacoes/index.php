<?php
$titulo = 'publicações';
require_once('../components/head.php');
require_once('../components/header.php')
?>

<main class="container-fluid">
    <section class="page-title px-4 py-5">
        <h3 class="text-primary mb-0">publicações</h3>

        <div>
            <button onclick="toggleMostrarFiltros()" class="btn btn-outline-primary me-3">filtrar publicações</button>
            <a class="btn btn-primary" href="/publicacoes/nova.php">nova publicação</a>
        </div>
    </section>

    <section class="px-4 d-none" id="section-filtros">
        <div class="mb-3 card">
            <div class="card-body">
                <div id="container-filtros"></div>
                <div id="container-parametros"></div>
            </div>
        </div>
    </section>

    <section>
        <div class="card d-none" id="card-publicacoes">
            <div class="card-body">
                <div id="msg-sem-publicacoes" class="alert alert-info d-none">
                    Nenhuma das publicações cadastradas satisfaz os critérios de busca informados (ou não há publicações cadastradas).
                </div>

                <div id="container-paginacao"></div>

                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Conteúdo</th>
                            <th>Criação</th>
                            <th>Atualização</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-publicacoes"></tbody>
                </table>
            </div>
        </div>
    </section>

</main>

<footer class="mx-auto my-4 nunito text-center">
    <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
</footer>

<?php require_once('../components/scripts.php'); ?>

<script>
    const toggleMostrarFiltros = () => {
        const section = q.id("section-filtros");
        if (section.classList.contains("d-none")) {
            section.classList.remove("d-none");
        } else {
            section.classList.add("d-none");
        }
    }

    const filtroConteudo = new StringSearchFilter('text', 'Conteúdo');
    const filtroTitulo = new StringSearchFilter('title', 'Título');
    const filtroDataCriacao = new DateSearchFilter('created_at', 'Data de Criação');
    const filtroDataAtualizacao = new DateSearchFilter('created_at', 'Data de Atualização');

    const filtrosTodos = [filtroConteudo, filtroTitulo, filtroDataCriacao, filtroDataAtualizacao];

    const containerFiltros = q.id('container-filtros');
    for (const filtro of filtrosTodos) {
        filtro.element.classList.add('mb-3');
        containerFiltros.append(filtro.element);
    }

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
    ]
    const parametrosListagem = new ListingParameters(
        opcoesOrdenacao,
        'Publicações por página',
        (ordenacao, direcao, pubPorPagina, pagina, cbNumResultados) => {
            const filtros = filtrosTodos.flatMap(({
                value
            }) => value ?? []);
            fazerBusca(ordenacao, direcao, pubPorPagina, pagina, filtros, num => cbNumResultados(num));
        }
    );

    q.id('container-paginacao').append(parametrosListagem.paginationElement);
    q.id('container-parametros').append(parametrosListagem.parameterRowElement);

    parametrosListagem.triggerFirstSearch();

    const msgSemPublicacoes = q.id('msg-sem-publicacoes');
    const cardPublicacoes = q.id('card-publicacoes');

    function fazerBusca(ordenacao, direcao, pubPorPagina, pagina, filtros, cbNumResultados) {
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
            .then(ret => {
                let {
                    publications: publicacoes,
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

                const tbody = q.id('tbody-publicacoes');
                q.empty(tbody);
                for (const publicacao of publicacoes) {
                    const tr = q.make('tr', [], tbody);
                    const tdTitulo = q.make('td', [], tr);
                    q.make('a', [], tdTitulo, {
                        href: '/publicacoes/detalhe.php?publicacao=' + publicacao.slug,
                        innerText: publicacao.title
                    });
                    q.make('td', [], tr, {
                        innerText: publicacao.text
                    });
                    q.make('td', [], tr, {
                        innerText: formatarData(publicacao.createdAt)
                    });
                    q.make('td', [], tr, {
                        innerText: formatarData(publicacao.updatedAt)
                    });
                }

            })
            .catch(err => {
                console.error(err);
                alertarErroSistema('Ocorreu um erro ao fazer a busca pelas publicações');
            });
    }
</script>

<?php require_once('../components/footer.php') ?>