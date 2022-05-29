<?php $titulo = 'entrar'; require('../header.php'); ?>

<main class="container-fluid min-vh-100 d-flex flex-column max-width-480">
    <section class="mt-auto mb-4 mx-auto">
        <img src="/static/img/artmux.svg" alt="artmux logo">
        <h1 class="text-primary text-center nunito-black">cadastrar</h1>
    </section>
    <form class="mb-auto">
        <div class="form-group my-auto">
            <label for="user">usuário</label>
            <input class="form-control" id="user" aria-describedby="zecaurubu" placeholder="zecaurubu">
        </div>
        <div class="form-group mt-2">
            <label for="email">email</label>
            <input class="form-control" id="email" type="email" aria-describedby="zecaurubu" placeholder="zecaurubu">
        </div>
        <div class="form-group mt-2">
            <label for="password">senha</label>
            <input type="password" class="form-control" id="password" placeholder="********">
        </div>
        <div class="form-group mt-2 mb-4">
            <label for="passwordConfirmation">confirme a senha</label>
            <input type="passwordConfirmation" class="form-control" id="passwordConfirmation" placeholder="********">
        </div>
        <div class="d-grid gap-2 mx-auto">
            <button class="btn btn-primary" type="submit">cadastrar</button>
        </div>

        <p class="align-center mt-4 text-center">
            já possui cadastro? 
           <a href="/entrar" class="link-dark"><strong>clique aqui!</strong></a>
        </p>
    </form>

    <section class="mx-auto my-4 nunito">
        <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
    </section>
</main>

<?php require('../footer.php') ?>