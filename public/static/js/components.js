'use strict';

class Pagination {

  #pageToHtml =  {
    'first': '<i class="fas fa-angle-double-left"></i>',
    'prev':  '<i class="fas fa-angle-left"></i>',
    'next':  '<i class="fas fa-angle-right"></i>',
    'last':  '<i class="fas fa-angle-double-right"></i>'
  };

  constructor(container, onClickPage) {
    this.onClickPage = onClickPage;
    this.ulPagination = q.make(
      'ul', ['pagination', 'justify-content-end', 'mb-0'], container
    );
  }

  refresh(currentPage, resultsPerPage, totalResults) {
    if (totalResults <= resultsPerPage) {
      q.hide(this.ulPagination);
      return;
    }

    q.empty(this.ulPagination);

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
          if (!this.onClickPage) return;
          let pagenum;
          switch (page) {
            case 'first': pagenum = 1; break;
            case 'prev':  pagenum = currentPage - 1; break;
            case 'next':  pagenum = currentPage + 1; break;
            case 'last':  pagenum = lastPage; break;
            default:      pagenum = Number(page); break;
          }
          this.onClickPage(pagenum);
        }
      });
      return li;
    };

    this.ulPagination.append(makeLi('first', currentPage <= 1));
    this.ulPagination.append(makeLi('prev',  currentPage <= 1));

    for (
      let i = Math.max(1, currentPage - 2);
      i <= Math.min(currentPage + 2, lastPage);
      i++
    ) this.ulPagination.append(makeLi(i));

    this.ulPagination.append(makeLi('next', currentPage >= lastPage));
    this.ulPagination.append(makeLi('last', currentPage >= lastPage));

    q.show(this.ulPagination);
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

  constructor(input) {
    this.tagify = new Tagify(input);
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

  constructor(artworkCallback = () => undefined) {
    this.#artworkCallback = artworkCallback;
    this.#element = q.make('div');
    this.#artworksContainer = q.make('div', [], this.#element);
    this.#artworksContainer.style['display'] = 'grid';
    this.#artworksContainer.style['grid-template-columns'] = '1fr 1fr 1fr';
    this.#artworksContainer.style['grid-gap'] = '20px';
    this.#emptyMessage = q.make('div', ['alert', 'alert-info', 'd-none'], null, {
      innerText: 'Nenhuma das obras cadastradas satisfaz os critérios de busca informados.'
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

  #makeArtworkElement(artwork) {
    const element = q.make('div', ['card', 'text-center'], null);
    element.style['padding'] = '10px';
    const div = q.make('div', ['text-center'], element);
    const img = q.make('img', [], div);
    imageBlobUrl(artwork.imagePaths.thumbnail).then(url => img.src = url);
    img.style['max-width'] = 'min(100%, 256px)';
    img.style['max-height'] = 'min(100%, 256px)';
    img.style['object-fit'] = 'contain';
    q.make('p', [], element, { innerText: artwork.title });
    element.addEventListener('mouseenter', () => {
      element.style['filter'] = 'brightness(0.95)';
      img.style['filter'] = 'brightness(calc(1/0.95))';
    });
    element.addEventListener('mouseleave', () => {
      element.style['filter'] = 'brightness(1)';
      img.style['filter'] = 'brightness(1)';
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

  #selectedArtworks = new Map();
  #card;
  #foundArtworksGrid;

  constructor(value) {
    const card = q.make('div', ['card']);
    const head = q.make('div', ['card-header'], card);
    head.style['text-align'] = 'right';
    const body = q.make('div', ['card-body'], card);

    const selectedArtworksGrid = new ArtworkGrid((artwork, element) => {
      element.style['cursor'] = 'pointer';
      element.addEventListener('mouseenter', () => { element.style['background-color'] = 'rgba(196, 64, 0, 0.15)'; })
      element.addEventListener('mouseleave', () => { element.style['background-color'] = ''; });
      element.addEventListener('click', () => {
        this.#selectedArtworks.delete(artwork.id);
        // A little clunky since we don't have a .remove() method, but whatever
        const withoutRemoved = selectedArtworksGrid.artworks.filter(x => x.artwork.id != artwork.id).map(x => x.artwork);
        selectedArtworksGrid.display(withoutRemoved);
      });
    });
    body.append(selectedArtworksGrid.element);

    this.#card = card;

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
      for (const { element } of selectedArtworksGrid.artworks) {
        element.style['cursor'] = 'pointer';
        element.onmouseenter = 
        element.onmouseleave = () => 
        element.onclick = () => {
        }
      }
    });

    const inputGroup = q.make('div', ['input-group', 'mb-3'], modalBody)
    const searchIconSpan = q.make('span', ['input-group-text'], inputGroup);
    q.make('i', ['fas', 'fa-search'], searchIconSpan);
    const searchBar = q.make('input', ['form-control'], inputGroup);
    
    this.#foundArtworksGrid = new ArtworkGrid((artwork, element) => {
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
    return this.#card;
  }
}