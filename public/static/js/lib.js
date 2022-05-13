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

  // Note that these functions cannot be used generally
  // show, in particular, won't work for elements that just have display: none in their style,
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
  }
}

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