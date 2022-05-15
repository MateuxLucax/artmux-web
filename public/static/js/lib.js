/**
 * Our own library of utility functions
 */

// Little library of functions to make these common functions easier to type
// q for query, like jQuery
const q = Object.assign(Object.create(null), {

  /**
   * Alias for document.getElementById
   * @param {string} id 
   * @returns HTMLElement
   */
  id: function(id) {
    return document.getElementById(id)
  },

  /**
   * Alias for document.getElementsByClassName
   * @param {string} klass 
   * @returns 
   */
  class: function(klass) {
    return document.getElementsByClassName(klass)
  },

  /**
   * Alias for parent.getElementsByCLassName
   * @param {string} klass 
   * @param {HTMLElement} parent 
   * @returns HTMLElement
   */
  classIn: function(klass, parent) {
    return parent.getElementsByClassName(klass);
  },

  // Note that these functions cannot be used generally.
  // 'show', in particular, won't work for elements that just have display: none in their style,
  // they need to have the bootstrap class d-none

  /**
   * Adds the 'd-none' class to the given element
   * @param {HTMLElement} e
   */
  hide: function(e) {
    e.classList.add('d-none')
  },

  /**
   * Removes the 'd-none' class from the given element
   * @param {HTMLElement} e
   */
  show: function(e) {
    e.classList.remove('d-none')
  },

  /**
   * Toggles element between visible and not visible using the d-none class.
   * @param {HTMLElement} e 
   */
  toggle: function(e) {
    if (e.classList.contains('d-none')) e.classList.remove('d-none');
    else e.classList.add('d-none');
  },

  /**
   * Creates an element with given tag, classes and attributes, and
   * appends it to the given parent element.
   * @param {string} tag 
   * @param {string[]} classes 
   * @param {HTMLElement} parent 
   * @param {object} attributes 
   * @returns HTMLElement
   */
  create: function(tag, classes=[], parent=null, attributes={}) {
    const elem = document.createElement(tag);
    parent?.append(elem);
    if (classes.length > 0)
        elem.classList.add(...classes);
    Object.assign(elem, attributes);
    return elem;
  }
})

/**
 * Recebe objeto Date e retorna como string no formato DD/MM/YYYY HH:ii
 * @param {Date} data 
 * @returns string
 */
function formatarData(data) {
  const pad = (n, s) => String(s).padStart(n, '0');
  const d = pad(2, data.getDate())
  const m = pad(2, data.getMonth())
  const y = data.getFullYear()
  const h = pad(2, data.getHours())
  const i = pad(2, data.getMinutes())
  return `${d}/${m}/${y} ${h}:${i}`
}

function clamp(x, min, max) {
  if (x < min) return min;
  if (x > max) return max;
  return x;
}

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

  const item = page => {
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
    ul.append(item('previous'))
    if (currentPage > 2) {
      ul.append(item('first'))
    }
  }

  for (let i = Math.max(1, currentPage-2); i <= Math.min(currentPage+2, lastPage); i++) {
    ul.append(item(i))
  }

  if (currentPage < lastPage) {
    ul.append(item('next'))
    if (currentPage + 1 < lastPage) {
      ul.append(item('last'))
    }
  }

  container.append(ul)
  return true
}