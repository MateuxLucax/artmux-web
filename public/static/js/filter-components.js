'use strict';

class SearchFilter {

  constructor(name, labelText, operatorOptions, inputType, defaultValue) {
    this.name = name  // Returned in the value() for identification

    const row = q.make('div', ['row'])
    const operatorCol = q.make('div', ['col-lg-4'], row)
    q.make('label', ['form-label'], operatorCol, { innerText: labelText })

    this.operatorSelect = q.make('select', ['form-control'], operatorCol)
    for (const operator in operatorOptions) {
      const attributes = {
        value: operator,
        innerText: operatorOptions[operator]
      }
      if (defaultValue.operator == operator) {
        attributes.selected = 'selected'
      }
      q.make('option', [], this.operatorSelect, attributes)
    }

    this.inputCol = q.make('div', ['col-lg-8'], row)
    q.make('label', ['form-label'], this.inputCol, { innerText: ' ' })

    this.input = q.make('input', ['form-control'], this.inputCol, {
      type: inputType,
      value: defaultValue.value,
    })

    this.elem = row
  }

  get element() {
    return this.elem
  }

  set value(value) {
    this.input.value = value.value;
    for (option of q.tagIn('option', this.operatorSelect)) {
      if (option.value == value.operator) {
        option.selected = 'selected'
        break
      }
    }
  }

  get value() {
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

    this.intervalInputGroup = q.make('div', ['input-group', 'd-none'], this.inputCol)
    q.make('span', ['input-group-text'], this.intervalInputGroup, { innerText: 'de' })
    this.inputFrom = q.make('input', ['form-control'], this.intervalInputGroup, { type: 'date' })
    q.make('span', ['input-group-text'], this.intervalInputGroup, { innerText: 'até' })
    this.inputTo = q.make('input', ['form-control'], this.intervalInputGroup, { type: 'date' })

    this.operatorSelect.onchange = ev => {
      this.#toggleIntervalInput(ev.target.value)
    }
  }

  #toggleIntervalInput(operator) {
    if (operator == 'between') {
      q.hide(this.input)
      q.show(this.intervalInputGroup)
    } else {
      q.show(this.input)
      q.hide(this.intervalInputGroup)
    }
  }

  set value(value) {
    if (value == null) {
      return;
    }
    this.#toggleIntervalInput(value.operator);
    if (value.operator == 'between') {
      this.inputFrom.value = value.value[0]
      this.inputTo.value = value.value[1]
    } else {
      this.input.value = value.value
    }
  }

  get value() {
    if (this.operatorSelect.value != 'between') {
      return super.value;
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

class TagSearchFilter extends SearchFilter {
  constructor(name, label, whitelist) {
    super(
      name,
      label,
      { 'tagsAllOf': 'Contém todas',
        'tagsAnyOf': 'Contém alguma' },
      'text',
      { operator: 'allOf',
        value: '' }
    );

    this.tagInput = new TagInput(this.input, { enforceWhitelist: true });
    this.tagInput.whitelist = whitelist;
  }

  get value() {
    let obj = super.value;
    if (obj != null) obj.value = this.tagInput.value.map(({id}) => id);
    return obj;
  }
}