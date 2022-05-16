/**
 * Creates a pagination element, appends it to the given container, and calls the
 * given callback each time you click a page, sending as argument the corresponding
 * page number. Returns whether the pagination was really appended to the container; it
 * will not do that if it's not necessary (i.e. totalResults <= resultPerPage).
 * 
 * @param {HTMLElement} container 
 * @param {number} currentPage 
 * @param {number} resultsPerPage 
 * @param {number} totalResults 
 * @param {function} callback 
 * @returns boolean
 */
 function appendPagination(container, currentPage, resultsPerPage, totalResults, callback) {
  if (totalResults <= resultsPerPage) {
    return false
  }

  const ul = document.createElement('ul')
  ul.classList.add('pagination')
  ul.classList.add('justify-content-end')
  ul.classList.add('mb-0')

  const lastPage = Math.ceil(totalResults / resultsPerPage)

  const pageToHtml = Object.assign(Object.create(null), {
    'first': '<i class="fas fa-angle-double-left"></i>',
    'previous': '<i class="fas fa-angle-left"></i>',
    'next': '<i class="fas fa-angle-right"></i>',
    'last': '<i class="fas fa-angle-double-right"></i>'
  })

  const pageToPagenumFunction = Object.assign(Object.create(null), {
    'first': () => 1,
    'previous': p => p-1,
    'next': p => p+1,
    'last': () => lastPage
  })

  const makeLi = page => {
    const a = document.createElement('a')
    a.href = '#'
    a.classList.add('page-link')
    a.innerHTML = pageToHtml[page] ?? page
    a.onclick = ev => {
      if (ev.button != 0) return true
      if (callback) {
        callback(page in pageToPagenumFunction ? pageToPagenumFunction[page](currentPage) : Number(page))
      }
      return false
    }
    const li = document.createElement('li')
    li.classList.add('page-item')
    if (page == currentPage) {
      li.classList.add('active')
    }
    li.append(a)
    return li
  }

  if (currentPage > 1) {
    ul.append(makeLi('previous'))
    if (currentPage > 2) {
      ul.append(makeLi('first'))
    }
  }

  for (let i = Math.max(1, currentPage-2); i <= Math.min(currentPage+2, lastPage); i++) {
    ul.append(makeLi(i))
  }

  if (currentPage < lastPage) {
    ul.append(makeLi('next'))
    if (currentPage + 1 < lastPage) {
      ul.append(makeLi('last'))
    }
  }

  container.append(ul)
  return true
}


class SearchFilter {

  constructor(labelText, operatorOptions, inputType, defaultValue) {
    const row = q.create('div', ['row'])
    const operatorCol = q.create('div', ['col-lg-4'], row)
    q.create('label', ['form-label'], operatorCol, { innerText: labelText })

    this.operatorSelect = q.create('select', ['form-control'], operatorCol)
    for (const operator in operatorOptions) {
      const attributes = {
        value: operator,
        innerText: operatorOptions[operator]
      }
      if (defaultValue.operator == operator) {
        attributes.selected = 'selected'
      }
      q.create('option', [], this.operatorSelect, attributes)
    }

    this.inputCol = q.create('div', ['col-lg-8'], row)
    q.create('label', ['form-label'], this.inputCol, { innerText: ' ' })

    this.input = q.create('input', ['form-control'], this.inputCol, {
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
    return {
      operator: this.operatorSelect.value,
      value: this.input.value
    }
  }
}

class StringSearchFilter extends SearchFilter {
  constructor(label) {
    super(
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
  constructor(label) {
    super(
      label,
      { 'equalTo': 'Igual a',
        'before': 'Antes de',
        'after': 'Depois de',
        'between': 'Entre', },
      'date',
      { value: '',
        operator: 'equalTo' }
    )

    this.intervalInputGroup = q.create('div', ['input-group', 'd-none'], this.inputCol)
    q.create('span', ['input-group-text'], this.intervalInputGroup, { innerText: 'de' })
    this.inputFrom = q.create('input', ['form-control'], this.intervalInputGroup, { type: 'date' })
    q.create('span', ['input-group-text'], this.intervalInputGroup, { innerText: 'até' })
    this.inputTo = q.create('input', ['form-control'], this.intervalInputGroup, { type: 'date' })

    this.operatorSelect.onchange = () => {
      this.setValue(this.value())
    }
  }

  setValue(value) {
    if (value.operator == 'between') {
      q.hide(this.input)
      q.show(this.intervalInputGroup)
      this.inputFrom.value = value.value[0]
      this.inputTo.value = value.value[1]
    } else {
      q.show(this.input)
      q.hide(this.intervalInputGroup)
      this.input.value = value.value
    }
  }

  value() {
    if (this.operatorSelect.value != 'between') {
      return super.value();
    }
    return {
      value: [ this.inputFrom.value, this.inputTo.value ],
      operator: 'between'
    }
  }
}