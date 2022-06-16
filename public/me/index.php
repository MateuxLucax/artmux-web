<?php $titulo = 'me'; require('../components/head.php'); ?>
<?php require('../components/scripts.php'); ?>

<main>
    <h1 class="text-center" id="me"></h1>
</main>

<script>
    window.onload = async ()  => {
        const response = await request.auth.get('users/me');
        q.id('me').innerHTML = response.username + ' - ' + response.email;
    };

</script>