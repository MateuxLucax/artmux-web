<?php
$titulo = 'Listar obras';
require('../components/head.php');
?>

<!-- TODO duas visualizações,
     uma em que só aparecem os nomes, um em cada linha, junto com um ícone que com hover aparece num tooltip a imagem
     e a atual, em grade mostrando cada imagem
     impl: criar ambas no DOM, alternar entre uma ou outra com um botão
-->

<style>
  .obra:hover {
    background-color: rgba(128, 128, 128, 0.1);
  }
  #container-obras {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    grid-gap: 20px;
  }
</style>

<main class="container">

  <?php
  $pagMenu = 'listar';
  require('menu.php');
  ?>


  <div class="mb-3 card" id="card-busca">
    <div class="card-body">
      <form id="form-busca">
        <div id="container-filtros"></div>
        <div id="container-parametros"></div>
      </form>
    </div>
  </div>

  <div id="msg-sem-obras" class="alert alert-info d-none">
    Nenhuma das obras cadastradas satisfaz os critérios de busca informados.
  </div>

  <div class="card d-none" id="card-obras">
    <div class="card-body" style="min-height: 256px;">

      <nav id="container-paginacao" class="mb-3">
      </nav>

      <!-- Fora do container-obras, porque ele é esvaziado a cada busca -->
      <div id="obra-prototipo" class="obra card text-center d-none" style="padding: 10px">
        <div class="text-center">
          <a class="obra-link" href="">
            <img style="max-width: 256px; max-height: 256px; object-fit: contain;" class="obra-img"/>
          </a>
        </div>
        <p class="obra-title"></p>
      </div>

      <div id="container-obras">
      </div>
    </div>
  </div>

</main>

<?php require('../components/scripts.php') ?>

<script>
  'use strict';

  //
  // Inicialização dos componentes
  //

  const filtroTitulo = new StringSearchFilter('title', 'Título')
  const filtroDataCriacao = new DateSearchFilter('created_at', 'Data de criação')
  const filtroDataAtualizacao = new DateSearchFilter('updated_at', 'Data de atualização')

  const filtrosTodos = [filtroTitulo, filtroDataCriacao, filtroDataAtualizacao];

  const containerFiltros = q.id('container-filtros')

  for (const { element } of filtrosTodos) {
    element.classList.add('mb-3');
    containerFiltros.append(element);
  }

  const opcoesOrdenacao = [
    { title: 'Data de criação', value: 'created_at' },
    { title: 'Data de atualização', value: 'updated_at' },
    { title: 'Título', value: 'title' }
  ];
  const parametrosListagem = new ListingParameters(
    opcoesOrdenacao,
    'Obras por página',
    (ordenacao, direcao, obrasPorPagina, pagina, callbackNumResultados) => {
      fazerBusca(
        ordenacao, direcao, obrasPorPagina, pagina,
        filtrosTodos.flatMap(({ value }) => value ?? []),
        num => callbackNumResultados(num)
      );
    }
  );

  q.id('container-parametros').append(parametrosListagem.parameterRowElement);
  q.id('container-paginacao').append(parametrosListagem.paginationElement);

  //
  // Formulário de busca
  //

  const formBusca = q.id('form-busca');

  // Busca inicial
  parametrosListagem.triggerFirstSearch();

  /**
   * Carrega uma obra recebida da API no DOM dentro do #container-obras
   * @param {object} obra
   * @return void
   */
  async function carregarObra(obra) {
    const elemObra = q.id('obra-prototipo').cloneNode(true)
    q.show(elemObra)
    elemObra.removeAttribute('id')
    q.classIn('obra-img', elemObra)[0].src = await imageBlobUrl(obra.imagePaths.thumbnail);
    q.classIn('obra-title', elemObra)[0].innerText = obra.title
    q.classIn('obra-link', elemObra)[0].href = '/obras/detalhe.php?obra=' + obra.slug
    q.id('container-obras').append(elemObra)
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
    const buscaAPI = new URLSearchParams();
    buscaAPI.append('order', ordenacao)
    buscaAPI.append('direction', direcao)
    buscaAPI.append('perPage', obrasPorPagina)
    buscaAPI.append('page', pagina)
    filtros.forEach(filtro => buscaAPI.append('filters', JSON.stringify(filtro)))
    
    const msgSemObras = q.id('msg-sem-obras');
    const cardObras = q.id('card-obras');
    const containerObras = q.id('container-obras');

    request
    .authFetch(`artworks/?${buscaAPI.toString()}`)
    .then(res => {
      if (res.status != 200 && res.status != 304) throw res;
      return res.json();
    })
    .then(ret => {
      const { total: totalObras, artworks: obras } = ret;
      if (obras.length == 0) {
        q.hide(cardObras);
        q.show(msgSemObras);
      } else {
        q.empty(containerObras);
        q.show(cardObras);
        q.hide(msgSemObras);

        obras.forEach(carregarObra);

        callbackNumResultados(totalObras);
      }
    })
    .catch(err => {
      console.error(err)
      alertarErroSistema('Não conseguimos carregar as suas obras. Tente novamente mais tarde.')
      .then(() => history.back())
    })
  }
</script>

<?php require('../components/footer.php') ?>