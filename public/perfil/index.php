<?php
$titulo = 'perfil';
require_once('../components/head.php');
require_once('../components/header.php');
?>
<main class="container-fluid d-flex flex-column max-width-480">
    <section class="my-2 mt-4 mx-auto">
        <h1 class="text-primary text-center nunito-black">perfil</h1>
    </section>
    <form class="card p-4" method="POST" id="user-info-form">
        <div class="form-group my-auto">
            <label for="username" class="form-label">usuário</label>
            <input class="form-control" id="username" aria-describedby="zecaurubu" placeholder="zecaurubu" required />
        </div>
        <div class="form-group mt-2">
            <label for="email" class="form-label">senha</label>
            <input type="email" class="form-control" id="email" placeholder="zecaurubu@artmux.dev" required />
        </div>
        <div class="d-grid mt-4 mx-auto">
            <button class="btn btn-primary" id="update-btn" type="submit">alterar dados</button>
        </div>
    </form>

    <section class="my-2 mx-auto">
        <h1 class="text-primary text-center nunito-black">senha</h1>
    </section>
    <form class="card p-4" method="POST" id="user-password-form">
        <div class="form-group mt-2">
            <label for="password" class="form-label">senha atual</label>
            <input type="password" class="form-control" id="password" placeholder="********" required />
        </div>
        <div class="form-group mt-2">
            <label for="newPassword" class="form-label">nova senha</label>
            <input type="password" class="form-control" id="newPassword" placeholder="********" required />
        </div>
        <div class="form-group mt-2">
            <label for="newPasswordConfirmation" class="form-label">confirmação da nova senha</label>
            <input type="password" class="form-control" id="newPasswordConfirmation" placeholder="********" required />
        </div>
        <div class="d-grid mt-4 mx-auto">
            <button class="btn btn-primary" id="update-btn" type="submit">alterar senha</button>
        </div>
    </form>
</main>

<footer class="mx-auto mt-4 mb-2 nunito text-center">
    <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
</footer>

<?php require('../components/scripts.php') ?>

<script>
    
</script>

<?php require('../components/footer.php') ?>