<?php
$titulo = 'Listar obras';
require('../header.php');
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

        <div class="row">
          <div class="col-10 col-lg-5 mb-3 mb-lg-0">
            <label class="form-label" for="ordenacao">Ordenar por</label>
            <select name="ordenacao" id="ordenacao" class="form-control">
              <option value="created_at">
                Data de criação
              </option>
              <option value="updated_at">
                Data de atualização
              </option>
              <option value="title">
                Título
              </option>
            </select>
          </div>
          <div class="col-2 col-lg-1">
            <label for="direcao-btn" class="form-label">&nbsp;</label>
            <input type="hidden" id="direcao" name="direcao" value="desc">
            <button id="direcao-btn" class="btn btn-outline-secondary form-control" onclick="trocarDirecao()" title="" type="button">
              <i id="icon-asc" class="fas fa-sort-amount-down-alt d-none"></i>
              <i id="icon-desc" class="fas fa-sort-amount-down"></i>
            </button>
          </div>
          <div class="col-lg-4">
            <label for="obras-por-pagina" class="form-label">Obras por página</label>
            <input id="obras-por-pagina" name="obras-por-pagina" class="form-control"
                   type="number" min="3" max="999" step="3" value="6">
            <input type="hidden" name="obras-por-pagina-anterior" id="obras-por-pagina-anterior" value="6">
          </div>
          <div class="col-lg-2">
            <label for="btn-buscar" class="form-label">&nbsp;</label>
            <button id="btn-buscar" type="submit" class="btn btn-success form-control">
              Buscar
            </button>
          </div>
        </div>

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
            <img style="max-width: 256px; max-height: 256px; object-fit: contain;" class="obra-img" src=""/>
          </a>
        </div>
        <p class="obra-title"></p>
      </div>

      <div id="container-obras">

      </div>
    </div>
  </div>

</main>

<script>
  'use strict';

  //
  // Inicialização dos componentes
  //

  const filtroTitulo = new StringSearchFilter('title', 'Título')
  const filtroDataCriacao = new DateSearchFilter('created_at', 'Data de criação')
  const filtroDataAtualizacao = new DateSearchFilter('updated_at', 'Data de atualização')

  const filtros = [filtroTitulo, filtroDataCriacao, filtroDataAtualizacao];

  const containerFiltros = q.id('container-filtros')

  for (const filtro of filtros) {
    const elem = filtro.element();
    elem.classList.add('mb-3');
    containerFiltros.append(elem);
  }

  const paginacao = new Pagination(q.id('container-paginacao'), newPageNum => {
    const busca = getBuscaFormulario();
    busca.pagina = newPageNum;
    busca.obrasPorPagina = formBusca['obras-por-pagina-anterior'].value
    fazerBusca(busca);
  });

  //
  // Formulário de busca
  //

  const formBusca = q.id('form-busca');

  // Busca inicial
  fazerBusca(getBuscaFormulario());

  /**
   * Retorna representação atual do formulário de busca como objeto
   */
  function getBuscaFormulario() {
    return {
      ordenacao: formBusca.ordenacao.value,
      direcao: formBusca.direcao.value,
      obrasPorPagina: Number(formBusca['obras-por-pagina'].value),
      pagina: 1,
      filtros: filtros.flatMap(filtro => filtro.value() ?? [])
    }
  }

  /**
   * Carrega uma obra recebida da API no DOM dentro do #container-obras
   * @param {object} obra
   * @return void
   */
  function carregarObra(obra) {
    const elemObra = q.id('obra-prototipo').cloneNode(true)
    q.show(elemObra)
    elemObra.removeAttribute('id')
    q.classIn('obra-img', elemObra)[0].src = 'http://localhost:4000' + obra.imagePaths.thumbnail
    q.classIn('obra-title', elemObra)[0].innerText = obra.title
    q.classIn('obra-link', elemObra)[0].href = '/obras/detalhe.php?obra=' + obra.slug
    q.id('container-obras').append(elemObra)
  }

  q.id('direcao-btn').onclick = ev => {
    ev.preventDefault()
    const dir = q.id('direcao')
    const btn = q.id('direcao-btn')
    if (dir.value == 'asc') {
      q.show(q.id('icon-asc'))
      q.hide(q.id('icon-desc'))
      dir.value = 'desc'
      btn.title = 'Decrescente'
    } else {
      q.hide(q.id('icon-asc'))
      q.show(q.id('icon-desc'))
      dir.value = 'asc'
      btn.title = 'Crescente'
    }
  }

  q.id('obras-por-pagina').onchange = ev => {
    const input = ev.target
    const val = Number(input.value)
    input.value = clamp(3 * Math.floor(val / 3), input.getAttribute('min'), input.getAttribute('max'))
  }

  formBusca.onsubmit = ev => {
    ev.preventDefault()
    const busca = getBuscaFormulario();
    if (formBusca['obras-por-pagina'].value != formBusca['obras-por-pagina-anterior'].value) {
      busca.pagina = 1;
      // Por exemplo: usuário tem 5 obras, atualmente listando 3 obras por página, na página 2.
      // Se ele trocar para 6 obras por página, não podemos ficar na página 2, porque só tem uma página agora.
    }
    fazerBusca(busca);
  }

  /**
   * Realiza a busca na API conforme os parâmetros fornecido no objeto 'busca' fornecido,
   * atualizando o DOM na página quando obtém uma resposta.
   * 
   * @param {object} busca
   * @return void
   */
  function fazerBusca(busca) {
    const buscaAPI = new URLSearchParams();
    buscaAPI.append('order', busca.ordenacao)
    buscaAPI.append('direction', busca.direcao)
    buscaAPI.append('perPage', busca.obrasPorPagina)
    buscaAPI.append('page', busca.pagina)
    busca.filtros.forEach(filtro => buscaAPI.append('filters', JSON.stringify(filtro)))
    console.log(buscaAPI.toString());
    
    const msgSemObras = q.id('msg-sem-obras');
    const cardObras = q.id('card-obras');
    const containerObras = q.id('container-obras');

    fetch(`http://localhost:4000/artworks/?${buscaAPI.toString()}`)
    .then(res => {
      if (res.status != 200 && res.status != 304) {
        throw res;
      }
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

        q.id('obras-por-pagina-anterior').value = busca.obrasPorPagina;

        q.show(q.id('card-obras'));
        obras.forEach(carregarObra);

        paginacao.refresh(busca.pagina, busca.obrasPorPagina, totalObras);
      }
    })
    .catch(err => {
      console.error(err)
      Swal.fire({
        title: 'Erro do sistema',
        icon: 'error',
        text: 'Não conseguimos carregar as suas obras. Tente novamente mais tarde.'
      }).then(() => history.back())
    })
  }
</script>

<?php require('../footer.php') ?>