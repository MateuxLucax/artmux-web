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


  <div id="msg-sem-obras" class="alert alert-info d-none">
    Você ainda não cadastrou nenhuma obra. <br>
    Isso é um erro? <a href="#" id="link-reset-busca">Clique aqui</a>.<br>
    Não deu certo? Tente novamente mais tarde ou entre em contato.
  </div>

  <!-- TODO filtros:
       o único filtro no card principal é o de título
       os outros deverão ir num card abaixo, de filtros adicionais, que deve ficar recolhido
       esse card também deverá ter abas para separar os filtros, tipo
       datas | tags | redes-->

  <div class="mb-3 card d-none" id="card-busca">
    <div class="card-body">
      <form id="form-busca">

        <input type="hidden" id="pagina" name="pagina">

        <div class="row mb-3">
          <div class="col-lg-4">
            <label for="titulo-operador" class="form-label">Título</label>
            <select name="titulo-operador" id="titulo-operador" class="form-control">
              <option value="contains" selected="selected">Contém</option>
              <option value="equalTo">Igual a</option>
              <option value="startsWith">Inicia com</option>
              <option value="endsWith">Termina com</option>
            </select>
          </div>
          <div class="col-lg-8">
            <label for="titulo-busca" class="form-label">&nbsp;</label>
            <input type="text" name="titulo-busca" id="titulo-busca" class="form-control">
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-lg-4">
            <label for="data-criacao-operador" class="form-label">Data de criação</label>
            <select name="data-criacao-operador" id="data-criacao-operador" class="form-control">
              <option value="after" selected="selected">Depois de</option>
              <option value="before">Antes de</option>
              <option value="equalTo">Igual a</option>
              <option value="between">Entre</option>
            </select>
          </div>
          <div class="col-lg-8">
            <label for="data-criacao-busca" class="form-label">&nbsp;</label>
            <input type="date" name="data-criacao-busca" id="data-criacao-busca" class="form-control">
            <div class="input-group d-none" id="data-criacao-intervalo">
              <span class="input-group-text">de</span>
              <input type="date" name="data-criacao-busca-1" class="form-control">
              <span class="input-group-text">até</span>
              <input type="date" name="data-criacao-busca-2" class="form-control">
            </div>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-lg-4">
            <label for="data-atualizacao-operador" class="form-label">Data de atualização</label>
            <select name="data-atualizacao-operador" id="data-atualizacao-operador" class="form-control">
              <option value="after" selected="selected">Depois de</option>
              <option value="before">Antes de</option>
              <option value="equalTo">Igual a</option>
              <option value="between">Entre</option>
            </select>
          </div>
          <div class="col-lg-8">
            <label for="data-atualizacao-busca" class="form-label">&nbsp;</label>
            <input type="date" name="data-atualizacao-busca" id="data-atualizacao-busca" class="form-control">
            <div class="input-group d-none" id="data-atualizacao-intervalo">
              <span class="input-group-text">de</span>
              <input type="date" name="data-atualizacao-busca-1" class="form-control">
              <span class="input-group-text">até</span>
              <input type="date" name="data-atualizacao-busca-2" class="form-control">
            </div>
          </div>
        </div>

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
            <input type="hidden" id="direcao" name="direcao">
            <button id="direcao-btn" class="btn btn-outline-secondary form-control" onclick="trocarDirecao()" title="" type="button">
              <i id="icon-asc" class="fas fa-sort-amount-down-alt d-none"></i>
              <i id="icon-desc" class="fas fa-sort-amount-down d-none"></i>
            </button>
          </div>
          <div class="col-lg-4">
            <label for="obras-por-pagina" class="form-label">Obras por página</label>
            <input id="obras-por-pagina" name="obras-por-pagina" class="form-control"
                   type="number" min="3" max="999" step="3">
            <input type="hidden" name="obras-por-pagina-anterior" id="obras-por-pagina-anterior">
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

  <div class="card d-none" id="card-obras">
    <div class="card-body">
      <div id="container-obras">
        <div id="obra-prototipo" class="obra card text-center d-none" style="padding: 10px">
          <div class="text-center">
            <a class="obra-link" href="">
              <img style="max-width: 256px; max-height: 256px; object-fit: contain;" class="obra-img" src=""/>
            </a>
          </div>
          <p class="obra-title"></p>
        </div>
      </div>
      <nav id="paginacao" class="d-none mt-3">
        <ul class="pagination justify-content-end mb-0">
        </ul>
      </nav>
    </div>
  </div>

</main>

<script>
  function carregarObra(obra) {
    const elemObra = q.id('obra-prototipo').cloneNode(true)
    q.show(elemObra)
    elemObra.removeAttribute('id')
    q.classIn('obra-img', elemObra)[0].src = 'http://localhost:4000' + obra.imagePaths.thumbnail
    q.classIn('obra-title', elemObra)[0].innerText = obra.title
    q.classIn('obra-link', elemObra)[0].href = '/obras/detalhe.php?obra=' + obra.slug
    q.id('container-obras').append(elemObra)
  }

  function ajustarIconeDirecao(dir) {
    if (dir == 'asc') {
      q.show(q.id('icon-asc'))
      q.hide(q.id('icon-desc'))
    } else {
      q.hide(q.id('icon-asc'))
      q.show(q.id('icon-desc'))
    }
  }

  q.id('direcao-btn').onclick = ev => {
    ev.preventDefault()
    const dir = q.id('direcao')
    const btn = q.id('direcao-btn')
    if (dir.value == 'asc') {
      dir.value = 'desc'
      btn.title = 'Decrescente'
    } else {
      dir.value = 'asc'
      btn.title = 'Crescente'
    }
    ajustarIconeDirecao(dir.value)
  }

  q.id('obras-por-pagina').onchange = ev => {
    const input = ev.target
    const val = Number(input.value)
    input.value = clamp(3 * Math.floor(val / 3), input.getAttribute('min'), input.getAttribute('max'))
  }

  q.id('data-criacao-operador').onchange = ev => {
    const val = ev.target.value
    if (val == 'between') {
      q.hide(q.id('data-criacao-busca'))
      q.show(q.id('data-criacao-intervalo'))
    } else {
      q.show(q.id('data-criacao-busca'))
      q.hide(q.id('data-criacao-intervalo'))
    }
  }

  q.id('data-atualizacao-operador').onchange = ev => {
    const val = ev.target.value
    if (val == 'between') {
      q.hide(q.id('data-atualizacao-busca'))
      q.show(q.id('data-atualizacao-intervalo'))
    } else {
      q.show(q.id('data-atualizacao-busca'))
      q.hide(q.id('data-atualizacao-intervalo'))
    }
  }

  const formBusca = q.id('form-busca')

  formBusca.onsubmit = ev => {
    ev.preventDefault()
    const pagina
      = formBusca['obras-por-pagina'].value == formBusca['obras-por-pagina-anterior'].value
      ? Number(formBusca.pagina.value)
      : 1
    const busca = {
      ordenacao: formBusca.ordenacao.value,
      direcao: formBusca.direcao.value,
      obrasPorPagina: Number(formBusca['obras-por-pagina'].value),
      pagina: pagina
    }
    sessionStorage.setItem('busca-obras', JSON.stringify(busca))
    location.reload()
  }

  const callbackPaginacao = pagenum => {
    const busca = {
      ordenacao: formBusca.ordenacao.value,
      direcao: formBusca.direcao.value,
      obrasPorPagina: Number(formBusca['obras-por-pagina-anterior'].value),
      pagina: Number(pagenum)
    }
    // Os links de paginação foram criados conforme a quantidade de obras por página anterior.
    // Se o usuário mudar a quantidade no formulário e usarmos essa quantidade ao trocar de página,
    // podem acontecer erros. Por exemplo: o usuário tem 5 obras e atualmente mostra 3 obras por
    // página, de forma que aparecem páginas 1 e 2 na paginação. Se ele trocar para 6 obras por 
    // página e ir para a página 2, iremos tentar carregar as obras 7ª a 12ª, sendo que não existem.
    // TODO desabilitar a paginação quando o usuário mudar o campo de obras por página, e só reabilitar quando ele deixar na quantidade anterior, porque senão parece que o sistema ignorou ele
    sessionStorage.setItem('busca-obras', JSON.stringify(busca))
    location.reload()
  }

  document.addEventListener('DOMContentLoaded', () => {
    const buscaJSON = sessionStorage.getItem('busca-obras')
    const busca = buscaJSON ? JSON.parse(buscaJSON) : {
      ordenacao: 'created_at',
      direcao: 'desc',
      obrasPorPagina: 6,
      pagina: 1
    }

    const buscaAPI = new URLSearchParams()
    buscaAPI.append('order', busca.ordenacao)
    buscaAPI.append('direction', busca.direcao)
    buscaAPI.append('perPage', busca.obrasPorPagina)
    buscaAPI.append('page', busca.pagina)
    
    fetch(`http://localhost:4000/artworks/?${buscaAPI.toString()}`)
    .then(res => {
      if (res.status != 200 && res.status != 304) {
        throw res
      }
      return res.json()
    })
    .then(ret => {
      const { totalWorks: totalObras, works: obras } = ret
      if (obras.length == 0) {
        q.show(q.id('msg-sem-obras'))
      } else {
        q.show(q.id('card-busca'))
        q.id('pagina').value = busca.pagina
        q.id('ordenacao').value = busca.ordenacao
        q.id('direcao').value = busca.direcao
        ajustarIconeDirecao(busca.direcao)
        q.id('obras-por-pagina').value = busca.obrasPorPagina
        q.id('obras-por-pagina-anterior').value = busca.obrasPorPagina
        

        q.show(q.id('card-obras'))
        obras.forEach(carregarObra)
        const paginacao = q.id('paginacao')
        var paginacaoCriada = appendPagination(
          paginacao,
          busca.pagina,
          busca.obrasPorPagina,
          totalObras,
          callbackPaginacao
        )
        if (paginacaoCriada) {
          q.show(paginacao)
        }
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
  })

  q.id('link-reset-busca').onclick = ev => {
    sessionStorage.removeItem('busca-obras')
    ev.preventDefault()
    location.reload()
  }
</script>

<?php require('../footer.php') ?>