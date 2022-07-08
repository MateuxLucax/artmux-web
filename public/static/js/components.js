'use strict';

class Pagination {

  #pageToHtml =  {
    'first': '<i class="bi bi-chevron-double-left"></i>',
    'prev':  '<i class="bi bi-chevron-left"></i>',
    'next':  '<i class="bi bi-chevron-right"></i>',
    'last':  '<i class="bi bi-chevron-double-right"></i>'
  };

  #onClickPage;
  #ulPagination;

  constructor(onClickPage) {
    this.#onClickPage = onClickPage;
    this.#ulPagination = q.make(
      'ul', ['pagination', 'justify-content-end', 'mb-0']
    );
  }

  refresh(currentPage, resultsPerPage, totalResults) {
    if (totalResults <= resultsPerPage) {
      q.hide(this.#ulPagination);
      return;
    } else {
      q.show(this.#ulPagination);
    }

    q.empty(this.#ulPagination);

    const lastPage = Math.ceil(totalResults / resultsPerPage);

    const makeLi = (page, disabled) => {
      const liClass = ['page-item'];
      if (page == currentPage) liClass.push('active');
      if (disabled) liClass.push('disabled');
      const li = q.make('li', liClass);
      q.make('a', ['page-link'], li, {
        href: '#',
        innerHTML: this.#pageToHtml[page] ?? page,
        onclick: ev => {
          if (ev.button != 0) return;
          ev.preventDefault();
          if (!this.#onClickPage) return;
          let pagenum;
          switch (page) {
            case 'first': pagenum = 1; break;
            case 'prev':  pagenum = currentPage - 1; break;
            case 'next':  pagenum = currentPage + 1; break;
            case 'last':  pagenum = lastPage; break;
            default:      pagenum = Number(page); break;
          }
          this.#onClickPage(pagenum);
        }
      });
      return li;
    };

    this.#ulPagination.append(makeLi('first', currentPage <= 1));
    this.#ulPagination.append(makeLi('prev',  currentPage <= 1));

    for (
      let i = Math.max(1, currentPage - 2);
      i <= Math.min(currentPage + 2, lastPage);
      i++
    ) this.#ulPagination.append(makeLi(i));

    this.#ulPagination.append(makeLi('next', currentPage >= lastPage));
    this.#ulPagination.append(makeLi('last', currentPage >= lastPage));
  }

  get element() {
    return this.#ulPagination;
  }

}


class SearchForm {

  #container;
  #filtersContainer;
  #pagination;

  #filters = [];
  #currentPerPage;  // Stays the same even if the user changes the <input> text
  #direction = 'desc';
  #orderSelect;
  #perPageInput;
  #searchFunction;

  constructor(orderOptions, perPageLabel, searchFunction) {
    this.#container = q.make('div');

    const paramsRow = q.make('div', ['row'], this.#container);

    this.#filtersContainer = q.make('div', ['d-none'], this.#container);

    const orderCol = q.make('div', ['col-10', 'col-lg-5', 'mb-3', 'mb-lg-0'], paramsRow);
    q.make('label', ['form-label'], orderCol, { innerText: 'Ordenar por '});
    const orderSelect = q.make('select', ['form-control'], orderCol);
    for (const option of orderOptions) {
      q.make('option', [], orderSelect, {
        value: option.value,
        innerText: option.title
      });
    }

    const directionCol = q.make('div', ['col-2', 'col-lg-1'], paramsRow);
    q.make('label', ['form-label'], directionCol, { innerText: '\xA0' });
    const directionButton = q.make(
      'button',
      ['btn', 'btn-outline-secondary', 'form-control'], 
      directionCol,
      { title: 'Descrescente', type: 'button' }
    );

    const iconAsc = q.make('i', ['d-none', 'bi','bi-sort-up'], directionButton);
    const iconDesc = q.make('i', ['bi', 'bi-sort-down'], directionButton);
    directionButton.onclick = _ev => {
      if (this.#direction == 'asc') {
        q.hide(iconAsc);
        q.show(iconDesc);
        this.#direction = 'desc';
        directionButton.title = 'Decrescente';
      } else {
        q.show(iconAsc);
        q.hide(iconDesc);
        this.#direction = 'asc';
        directionButton.title = 'Crescente';
      }
    };

    const perPageCol = q.make('div', ['col-lg-4'], paramsRow);
    q.make('label', ['form-label'], perPageCol, { innerText: perPageLabel });
    const perPageInput = q.make(
      'input',
      ['form-control'],
      perPageCol,
      { type: 'number', min: 3, max: 999, step: 3, value: 6 }
    );
    this.#currentPerPage = 6;
    perPageInput.onchange = () => {
      perPageInput.value = clamp(3 * Math.floor(Number(perPageInput.value) / 3), 3, 999)
    };

    // 
    // Botão de buscar + de mostrar/esconder filtros
    //

    const btnCol = q.make('div', ['col-lg-2'], paramsRow);
    q.make('label', ['form-label', 'd-block'], btnCol, { innerText: '\xA0' });
    const btnGroup = q.make('div', ['btn-group'], btnCol);
    btnGroup.style['width'] = '100%';

    const searchButton = q.make('button', ['btn', 'btn-primary'], btnGroup, { type: 'button', innerText: 'Buscar' });
    const toggleFiltersButton = q.make('button', ['btn', 'btn-primary'], btnGroup, { type: 'button', title: 'Mostrar filtros' });
    const iconPlus = q.make('i', ['bi', 'bi-plus-lg'], toggleFiltersButton);
    const iconMinus = q.make('i', ['bi', 'bi-dash-lg', 'd-none'], toggleFiltersButton);

    toggleFiltersButton.onclick = ev => {
      if (iconPlus.classList.contains('d-none')) {
        q.show(iconPlus);
        q.hide(iconMinus);
        toggleFiltersButton.title = "Mostrar filtros";
        q.hide(this.#filtersContainer);
      } else {
        q.show(iconMinus);
        q.hide(iconPlus);
        toggleFiltersButton.title = "Esconder filtros";
        q.show(this.#filtersContainer);
      }

    }

    searchButton.onclick = ev => {
      if (ev.buttons != 0) return;
      const perPage = Number(perPageInput.value);
      searchFunction(
        orderSelect.value,
        this.#direction,
        perPage,
        1,
        this.#filters.flatMap(({value}) => value ?? []),
        numResults => { this.#pagination.refresh(1, perPage, numResults) }
      );
      this.#currentPerPage = perPage;
    };

    this.#pagination = new Pagination(newPageNum => {
      searchFunction(
        orderSelect.value,
        this.#direction,
        this.#currentPerPage,
        newPageNum,
        this.#filters.flatMap(({value}) => value ?? []),
        numResults => { this.#pagination.refresh(newPageNum, this.#currentPerPage, numResults) }
      );
    });

    this.#orderSelect = orderSelect;
    this.#perPageInput = perPageInput;
    this.#searchFunction = searchFunction;
  }

  addFilter(filter) {
    this.#filters.push(filter);
    filter.element.classList.add('mt-3');
    this.#filtersContainer.append(filter.element);
    return this;
  }

  get element() {
    return this.#container;
  }

  get paginationElement() {
    return this.#pagination.element;
  }

  triggerFirstSearch() {
    const perPage = Number(this.#perPageInput.value);
    this.#searchFunction(
      this.#orderSelect.value,
      this.#direction,
      perPage,
      1,
      this.#filters.flatMap(({value}) => value ?? []),
      numResults => { this.#pagination.refresh(1, perPage, numResults); }
    );
  }

}

class TagInput {

  // working around a silly naming convention mismatch
  #our2tagify(tag) {
    const obj = { value: tag.name };
    if ('id' in tag) obj.id = tag.id;
    return obj;
  }
  #tagify2our(tag) {
    const obj = { name: tag.value };
    if ('id' in tag) obj.id = tag.id;
    return obj;
  }

  constructor(input, options={}) {
    this.tagify = new Tagify(input, { ...options });
  }

  set whitelist(tags) {
    this.tagify.whitelist = tags.map(this.#our2tagify);
  }

  set value(tags) {
    this.tagify.addTags(tags.map(this.#our2tagify));
  }

  get value() {
    return this.tagify.value.map(this.#tagify2our);
  }
}

class ArtworkGrid {

  #element;
  #artworksContainer;
  #emptyMessage;
  #artworks = [];
  #artworkCallback;

  constructor(artworkCallback = () => undefined, options = {}) {
    this.#artworkCallback = artworkCallback;
    this.#element = q.make('div');
    this.#artworksContainer = q.make('div', [], this.#element);
    this.#artworksContainer.style['display'] = 'grid';
    this.#artworksContainer.style['grid-template-columns'] = 'repeat(auto-fill, minmax(256px, 1fr))';
    this.#artworksContainer.style['grid-auto-rows'] = '256px';
    this.#artworksContainer.style['grid-gap'] = '20px';
    this.#emptyMessage = q.make('div', ['alert', 'alert-info', 'd-none', 'mb-0'], this.#element, {
      innerText: options.emptyMessage ?? 'Nenhuma das obras cadastradas satisfaz os critérios de busca informados.'
    });
  }

  display(artworks) {
    q.empty(this.#artworksContainer);
    this.#artworks = [];
    if (artworks.length == 0) {
      q.show(this.#emptyMessage);
    } else {
      q.hide(this.#emptyMessage);
      for (const artwork of artworks) {
        const element = this.#makeArtworkElement(artwork);
        this.#artworks.push({ artwork, element });
        this.#artworksContainer.append(element);
        this.#artworkCallback(artwork, element);
      }
    }
  }

  // TODO: deixar igual aos outros cards de imagens
  #makeArtworkElement(artwork) {
    const element = q.make('div', [], null);
    element.style['min-width'] = '256px';
    element.style['max-width'] = '100%';
    element.style['min-height'] = '256px';
    element.style['max-height'] = '100%';
    element.style['position'] = 'relative';
    element.style['overflow'] = 'hidden';
    element.style['transition'] = 'all .2s ease';
    element.style['border-radius'] = '8px';
    const img = q.make('img', [], element);
    imageBlobUrl(artwork.imagePaths.thumbnail).then(url => img.src = url);
    img.style['width'] = '100%';
    img.style['height'] = '100%';
    img.style['object-fit'] = 'cover';
    img.style['border-radius'] = '4px';
    const bg = q.make('div', [], element, { style: 'position: absolute; width: 100%; height: 100%; top: 0; left: 0;' });
    bg.style['background'] = 'linear-gradient(transparent, rgba(0, 0, 0, 0.5))';
    const p = q.make('p', [], element, { innerText: artwork.title, style: 'margin-bottom: 0;' });
    p.style['position'] = 'absolute';
    p.style['bottom'] = '8px';
    p.style['left'] = '8px';
    p.style['font-size'] = '24px';
    p.style['color'] = '#FFF';
    element.addEventListener('mouseenter', () => {
      element.style['opacity'] = '.75';
    });
    element.addEventListener('mouseleave', () => {
      element.style['opacity'] = '1';
    });
    return element;
  }

  get artworks() {
    return this.#artworks;
  }

  get element() {
    return this.#element;
  }
}

class ArtworksInput {

  #selectedArtworks;
  #element;
  #foundArtworksGrid;

  constructor(value = []) {
    this.#selectedArtworks = new Map();
    for (const artwork of value) {
      this.#selectedArtworks.set(artwork.id, artwork);
    }

    const card = q.make('div', ['card']);
    const head = q.make('div', ['card-header'], card);
    head.style['text-align'] = 'right';
    const body = q.make('div', ['card-body'], card);

    const selectedArtworksGrid = new ArtworkGrid((artwork, element) => {
      element.title = 'Clique para remover a obra';
      element.style['cursor'] = 'pointer';
      element.addEventListener('mouseenter', () => { element.style['background-color'] = 'rgba(196, 64, 0, 0.15)'; })
      element.addEventListener('mouseleave', () => { element.style['background-color'] = ''; });
      element.addEventListener('click', () => {
        this.#selectedArtworks.delete(artwork.id);
        // A little clunky since we don't have a .remove() method, but whatever
        const withoutRemoved = selectedArtworksGrid.artworks.filter(x => x.artwork.id != artwork.id).map(x => x.artwork);
        selectedArtworksGrid.display(withoutRemoved);
      });
    }, { emptyMessage: 'Nenhuma obra selecionada' });
    body.append(selectedArtworksGrid.element);
    selectedArtworksGrid.display(value);

    this.#element = card;

    const modal = q.make('div', ['modal', 'modal-xl'], card, { tabindex: -1 });
    const modalDialog = q.make('div', ['modal-dialog'], modal);
    const modalContent = q.make('div', ['modal-content'], modalDialog);

    const modalHeader = q.make('div', ['modal-header'], modalContent)
    q.make('h5', ['modal-title'], modalHeader).append('Adicionar obras')
    const closeButton = q.make('button', ['btn-close'], modalHeader, { type: 'button' });

    const addButton = q.make('button', ['btn', 'btn-primary'], head, { type: 'button', });
    addButton.append('Adicionar obras');

    const bsModal = new bootstrap.Modal(modal);
    addButton.addEventListener('click', () => { bsModal.show(); });
    closeButton.addEventListener('click', () => { bsModal.hide(); });

    const modalBody = q.make('div', ['modal-body'], modalContent);

    modal.addEventListener('hidden.bs.modal', () => {
      selectedArtworksGrid.display([...this.#selectedArtworks.values()]);
    });

    const inputGroup = q.make('div', ['input-group', 'mb-3'], modalBody)
    const searchIconSpan = q.make('span', ['input-group-text'], inputGroup);
    q.make('i', ['bi', 'bi-search'], searchIconSpan);
    const searchBar = q.make('input', ['form-control'], inputGroup);
    
    this.#foundArtworksGrid = new ArtworkGrid((artwork, element) => {
      element.title = 'Clique para adicionar a obra';
      element.style['cursor'] = 'pointer';
      element.style['background-color'] = this.#selectedArtworks.has(artwork.id) ? 'rgba(0, 196, 128, 0.15)' : '';
      element.onclick = () => {
        if (!this.#selectedArtworks.has(artwork.id)) {
          this.#selectedArtworks.set(artwork.id, artwork);
          element.style['background-color'] = 'rgba(0, 196, 128, 0.15)';
        } else {
          this.#selectedArtworks.delete(artwork.id);
          element.style['background-color'] = '';
        }
      }
    });

    modalBody.append(this.#foundArtworksGrid.element);

    modal.addEventListener('show.bs.modal', () => { this.search('') });

    var searchTimeout;
    searchBar.addEventListener('keydown', () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        this.search(searchBar.value);
      }, 500);
    });
  }

  async search(title) {
    const params = new URLSearchParams();
    params.append('page', 1);
    params.append('perPage', 9);
    params.append('order', 'updated_at');
    params.append('direction', 'desc');

    title = title.trim();
    if (title != '') {
      params.append('filters', JSON.stringify({
        name: 'title',
        operator: 'contains',
        value: title
      }));
    }

    request
    .authFetch(`artworks/?${params.toString()}`)
    .then(res => {
      if (res.status != 200 && res.status != 304) throw ['Non-ok response', res];
      return res.json();
    })
    .then(ret => {
      const { artworks } = ret;
      this.#foundArtworksGrid.display(artworks);
    })
    .catch(console.error);
  }

  get value() {
    return [...this.#selectedArtworks.keys()];
  }

  get element() {
    return this.#element;
  }
}