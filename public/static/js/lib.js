/**
 * Our own library of utility functions
 */

// Little library of functions to make these common functions easier to type
// q for query, like jQuery
const q = {

  /**
   * Alias for document.getElementById
   * @param {string} id 
   * @returns HTMLElement
   */
  id: function(id) {
    return document.getElementById(id)
  },

  /**
   * Alias for document.querySelector(qry)
   * @param {string} qry 
   * @returns Element
   */
  sel: function(qry) {
    return document.querySelector(qry);
  },

  /**
   * Alias for document.querySelectorAll(qry)
   * @param {string} qry 
   * @returns NodeList
   */
  all: function(qry) {
    return document.querySelectorAll(qry);
  },

  /**
   * Alias for document.querySelectorAll(qry).forEach(fn)
   * @param {string} qry 
   * @param {(value: Element, key: number, parent: Element) => void} fn
   * @returns void
   */
  each: function(qry, fn) {
    document.querySelectorAll(qry).forEach(fn);
  },

  /**
   * Alias for document.getElementsByClassName
   * @param {string} klass 
   * @returns HTMLCollection
   */
  class: function(klass) {
    return document.getElementsByClassName(klass)
  },

  /**
   * Alias for parent.getElementsByClassName
   * @param {string} klass 
   * @param {HTMLElement} parent 
   * @returns HTMLElement
   */
  classIn: function(klass, parent) {
    return parent.getElementsByClassName(klass);
  },

  /**
   * Alias for parent.getElementsByTagName
   * @param {string} tag 
   * @param {HTMLElement} parent 
   * @returns HTMLElement
   */
  tagIn: function(tag, parent) {
    return parent.getElementsByTagName(tag)
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
   * Also returns whether the element ended up being shown or not.
   * 
   * @param {HTMLElement} e
   * @param {boolean} b
   * @return {boolean}
   */
  toggle: function(e, b) {
    const show = b || (b != undefined && e.classList.contains('d-none'));
    if (show) q.show(e); else q.hide(e);
    return show;
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
  make: function(tag, classes=[], parent=null, attributes={}) {
    const elem = document.createElement(tag);
    parent?.append(elem);
    if (classes.length > 0) elem.classList.add(...classes);
    Object.assign(elem, attributes);
    return elem;
  },

  /**
   * Remove todos os nodos filhos do elemento dado
   * @param {HTMLElement} e 
   */
  empty(e) {
    while (e.firstChild) e.firstChild.remove();
  },
  
  /**
   * Replaces 'o' with 'n' in the DOM
   * @param {Node} o 
   * @param {Node} n 
   */
  replace(o, n) {
    o.parentNode.replaceChild(n, o);
  }
}

/**
 * Recebe objeto Date e retorna como string no formato DD/MM/YYYY HH:ii
 * @param {Date|string} data Objeto Date ou string representando data (nvdd qlqr valor aceitado no construtor Date())
 * @returns string
 */
function formatarData(data) {
  data = new Date(data);
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

const imageBlobUrl = (function() {
  // TODO: can be used with localStorage? indexedDb?
  // With the cache, reloading an image inside a page won't create a new blob for it
  const cache = {};
  return async function(imageEndpoint) {
    if (cache.hasOwnProperty(imageEndpoint)) {
      return cache[imageEndpoint];
    } else {
      const res  = await request.authFetch(imageEndpoint);
      const type = res.headers.get('content-type');
      const buf  = await res.arrayBuffer();
      const blob = new Blob([new Uint8Array(buf)], {type});
      const url  = window.URL.createObjectURL(blob);
      cache[imageEndpoint] = url;
      return url;
    }
  }
})();

const $message = {
  // TODO: make params as obj, making possible to pass more shit to obj, and maybe, customize even more if necessary
  warn: (message, title = 'Ops!') => Swal.fire({
    title: title,
    text: message,
    icon: 'warning',
    confirmButtonText: 'ok',
    confirmButtonColor: '#0d6efd'
  }),

  success: (message, title = 'Yaaaay!') => Swal.fire({
    title: title,
    text: message,
    icon: 'success',
    timer: 1500,
    showConfirmButton: false
  }),

  confirm: (message, title = 'Tem certeza?') => Swal.fire({
    title: title,
    text: message,
    icon: 'warning',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#d33',
    showCancelButton: true,
    cancelButtonColor: '#0d6efd',
    confirmButtonText: 'Sim, remover!'
  }),

  error: (message, title = 'Erro!') => Swal.fire({
    title: title,
    text: message,
    icon: 'error',
    confirmButtonText: 'ok',
    confirmButtonColor: '#0d6efd'
  })
}
