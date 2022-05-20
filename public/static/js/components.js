'use strict';

class Pagination {

  #pageToHtml = Object.assign(Object.create(null), {
    'first': '<i class="fas fa-angle-double-left"></i>',
    'previous': '<i class="fas fa-angle-left"></i>',
    'next': '<i class="fas fa-angle-right"></i>',
    'last': '<i class="fas fa-angle-double-right"></i>'
  });

  #pageToPagenumFunction = Object.assign(Object.create(null), {
    'first': () => 1,
    'previous': p => p-1,
    'next': p => p+1,
    'last': () => lastPage
  });

  constructor(container, onClickPage) {
    this.onClickPage = onClickPage;
    this.ulPagination = q.elem(
      'ul', ['pagination', 'justify-content-end', 'mb-0'], container
    );
  }

  refresh(currentPage, resultsPerPage, totalResults) {
    if (totalResults <= resultsPerPage) {
      q.hide(this.ulPagination);
      return;
    }

    q.empty(this.ulPagination);

    const makeLi = page => {
      const liClass = ['page-item'];
      if (page == currentPage) liClass.push('active')
      const li = q.elem('li', liClass);
      q.elem('a', ['page-link'], li, {
        href: '#',
        innerHTML: this.#pageToHtml[page] ?? page,
        onclick: ev => {
          if (ev.button != 0) return;
          if (this.onClickPage) {
            this.onClickPage(
              page in this.#pageToPagenumFunction
              ? this.#pageToPagenumFunction[page](currentPage)
              : Number(page)
            );
          }
          ev.preventDefault();
        }
      });
      return li;
    };

    if (currentPage > 1) {
      this.ulPagination.append(makeLi('previous'));
      if (currentPage > 2) {
        this.ulPagination.append(makeLi('first'));
      }
    }

    const lastPage = Math.ceil(totalResults / resultsPerPage);

    for (
      let i = Math.max(1, currentPage - 2);
      i <= Math.min(currentPage + 2, lastPage);
      i++
    ) {
      this.ulPagination.append(makeLi(i));
    }

    if (currentPage < lastPage) {
      this.ulPagination.append(makeLi('next'));
      if (currentPage + 1 < lastPage) {
        this.ulPagination.append(makeLi('last'));
      }
    }

    q.show(this.ulPagination);
  }

}


class SearchFilter {

  constructor(name, labelText, operatorOptions, inputType, defaultValue) {
    this.name = name  // Returned in the value() for identification

    const row = q.elem('div', ['row'])
    const operatorCol = q.elem('div', ['col-lg-4'], row)
    q.elem('label', ['form-label'], operatorCol, { innerText: labelText })

    this.operatorSelect = q.elem('select', ['form-control'], operatorCol)
    for (const operator in operatorOptions) {
      const attributes = {
        value: operator,
        innerText: operatorOptions[operator]
      }
      if (defaultValue.operator == operator) {
        attributes.selected = 'selected'
      }
      q.elem('option', [], this.operatorSelect, attributes)
    }

    this.inputCol = q.elem('div', ['col-lg-8'], row)
    q.elem('label', ['form-label'], this.inputCol, { innerText: ' ' })

    this.input = q.elem('input', ['form-control'], this.inputCol, {
      type: inputType,
      value: defaultValue.value,
    })

    this.elem = row
  }

  element() {
    return this.elem
  }

  setValue(value) {
    this.input.value = value.value;
    for (option of q.tagIn('option', this.operatorSelect)) {
      if (option.value == value.operator) {
        option.selected = 'selected'
        break
      }
    }
  }

  value() {
    const val = this.input.value
    if (val.trim() == '') {
      return null
    }
    return {
      name: this.name,
      operator: this.operatorSelect.value,
      value: val
    }
  }
}

class StringSearchFilter extends SearchFilter {
  constructor(name, label) {
    super(
      name,
      label,
      { 'contains': 'Contém',
        'equalTo': 'Igual a',
        'startsWith': 'Inicia com',
        'endsWith': 'Termina com', },
      'text',
      { value: '',
        operator: 'contains' }
    )
  }
}

class DateSearchFilter extends SearchFilter {
  constructor(name, label) {
    super(
      name,
      label,
      { 'equalTo': 'Igual a',
        'lesserOrEqual': 'Antes de',
        'greaterOrEqual': 'Depois de',
        'between': 'Entre', },
      'date',
      { value: '',
        operator: 'equalTo' }
    )

    this.intervalInputGroup = q.elem('div', ['input-group', 'd-none'], this.inputCol)
    q.elem('span', ['input-group-text'], this.intervalInputGroup, { innerText: 'de' })
    this.inputFrom = q.elem('input', ['form-control'], this.intervalInputGroup, { type: 'date' })
    q.elem('span', ['input-group-text'], this.intervalInputGroup, { innerText: 'até' })
    this.inputTo = q.elem('input', ['form-control'], this.intervalInputGroup, { type: 'date' })

    this.operatorSelect.onchange = ev => {
      this._toggleIntervalInput(ev.target.value)
    }
  }

  _toggleIntervalInput(operator) {
    if (operator == 'between') {
      q.hide(this.input)
      q.show(this.intervalInputGroup)
    } else {
      q.show(this.input)
      q.hide(this.intervalInputGroup)
    }
  }

  setValue(value) {
    if (value == null) {
      return;
    }
    this._toggleIntervalInput(value.operator);
    if (value.operator == 'between') {
      this.inputFrom.value = value.value[0]
      this.inputTo.value = value.value[1]
    } else {
      this.input.value = value.value
    }
  }

  value() {
    if (this.operatorSelect.value != 'between') {
      return super.value();
    }
    const from = this.inputFrom.value
    const to = this.inputTo.value
    if (from.trim() == '' && to.trim() == '') {
      return null;
    }
    return {
      name: this.name,
      operator: 'between',
      value: [ this.inputFrom.value, this.inputTo.value ],
    }
  }
}