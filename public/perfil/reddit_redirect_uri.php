<?php
$titulo = 'Acesso ao Reddit';
require_once('../components/head.php');
require_once('../components/header.php');
?>

<?php require('../components/scripts.php') ?>

<script>
  const params = new URLSearchParams(location.search);
  if (!params.has('state') || !params.has('code')) {
    $message.error('Redirect URI acessada incorretamente', 'Erro do sistema')
            .then(() => { location.assign('/perfil') })
  } else {

    const state = params.get('state');
    const code = params.get('code');
    request.auth.post('reddit/callback', { state, code })
    .then(() => {
      $message.successOk('Conta do Reddit vinculada com sucesso')
              .then(() => { location.assign('/perfil') })
    })
    .catch(err => {
      console.error(err)
      $message.error('Não conseguimos vincular a conta do Reddit')
    })

  }
</script>

<?php require('../components/footer.php') ?>