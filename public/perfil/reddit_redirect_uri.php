<?php
$titulo = 'acesso ao Reddit';
require_once('../components/head.php');
require_once('../components/header.php');
?>

<?php require('../components/scripts.php') ?>

<div class="loading-screen">
  <div class="spinner-border text-light" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>
</div>

<script>
  const params = new URLSearchParams(location.search);
  if (!params.has('state') || !params.has('code')) {
    $message.error('Redirect URI acessada incorretamente', 'Erro do sistema')
            .then(() => { location.assign('/perfil#accounts') })
  } else {
    const state = params.get('state');
    const code = params.get('code');
    request.auth.post('reddit/callback', { state, code })
    .then((response, json) => {
      if (response.status === 200) {
        $message.successOk('Conta do Reddit vinculada com sucesso')
              .then(() => { location.assign('/perfil#accounts') })  
      } else {
        throw { message: "oopsie doopsie" }
      }
    })
    .catch(err => {
      console.error(err)
      $message.error('Não conseguimos vincular a conta do Reddit')
    })
    .finally(() => {
      q.hide(q.sel('.loading-screen'));
    });
  }
</script>

<?php require('../components/footer.php') ?>