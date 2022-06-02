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


class ArtworksInput {

  // Just so we avoid the arbitrary restriction that there cannot be two in the same page
  // TODO actually ditch the data attribute stuff and use the boostrap Modal class or wtv for doing this (then also remove this instance counter since it'll be useless)
  static _INSTANCE_COUNTER = 0;

  constructor() {
    const card = q.make('div', ['card']);
    const head = q.make('div', ['card-header'], card);
    head.style['text-align'] = 'right';
    const body = q.make('div', ['card-body'], card);

    const modalID = 'artworks-input-modal-' + ArtworksInput._INSTANCE_COUNTER;

    const modal = q.make('div', ['modal', 'modal-xl'], card, { id: modalID, tabindex: -1 });
    const modalDialog = q.make('div', ['modal-dialog'], modal);
    const modalContent = q.make('div', ['modal-content'], modalDialog);

    const modalHeader = q.make('div', ['modal-header'], modalContent)
    q.make('h5', ['modal-title'], modalHeader).append('Adicionar obras')
    const modalClose = q.make('button', ['btn-close'], modalHeader, { type: 'button' });
    modalClose.setAttribute('data-bs-dismiss', 'modal');

    const modalBody = q.make('div', ['modal-body'], modalContent);
    const modalFooter = q.make('div', ['modal-footer'], modalContent);

    const addButton = q.make('button', ['btn', 'btn-primary'], head, { type: 'button', });
    addButton.setAttribute('data-bs-toggle', 'modal');
    addButton.setAttribute('data-bs-target', '#' + modalID);
    q.make('i', ['fas', 'fa-plus-circle'], addButton);
    addButton.append('\xa0Adicionar obras');  // 0xA0 is code for &nbsp;

    this.card = card;
    ArtworksInput._INSTANCE_COUNTER++;
  }

  // Value = array of artwork slugs

  set value(artworks) {
  }

  get value() {

  }

  get element() { return this.card; }
}