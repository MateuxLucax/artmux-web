function agendarAlertaSwal(obj) {
  sessionStorage.setItem('artmux-alerta-swal', JSON.stringify(obj));
}

const _alertaSwal = JSON.parse(sessionStorage.getItem('artmux-alerta-swal'));
if (_alertaSwal !== null) {
  sessionStorage.removeItem('artmux-alerta-swal');
  document.addEventListener('DOMContentLoaded', () => Swal.fire(_alertaSwal));
}