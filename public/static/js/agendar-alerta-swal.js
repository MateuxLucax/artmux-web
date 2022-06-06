function agendarAlerta(obj) {
  Object.assign(obj, {
    toast: true,
    position: 'top-end'
  });
  sessionStorage.setItem('artmux-alerta-swal', JSON.stringify(obj));
}

function agendarAlertaSucesso(msg) {
  agendarAlerta({
    icon: 'success',
    text: msg
  });
}

const _alertaSwal = JSON.parse(sessionStorage.getItem('artmux-alerta-swal'));
if (_alertaSwal !== null) {
  sessionStorage.removeItem('artmux-alerta-swal');
  document.addEventListener('DOMContentLoaded', () => Swal.fire(_alertaSwal));
}