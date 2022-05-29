//* Na verdade esse agendarAlertaSwal é desnecessário
//* Usamos quando queremos trocar de página e imediatamente mostrar um alerta na nova página
//* Mas em todos os casos podemos mostrar o alerta na mesma página e trocar de página quando o usuário clica no "ok"
//* Pra fazer isso, só colocar um .then() no Swal.fire()
//TODO portanto fazer essa alteração e remover esse arquivo

function agendarAlertaSwal(obj) {
  sessionStorage.setItem('artmux-alerta-swal', JSON.stringify(obj));
}

const _alertaSwal = JSON.parse(sessionStorage.getItem('artmux-alerta-swal'));
if (_alertaSwal !== null) {
  sessionStorage.removeItem('artmux-alerta-swal');
  document.addEventListener('DOMContentLoaded', () => Swal.fire(_alertaSwal));
}