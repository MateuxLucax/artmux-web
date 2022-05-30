<?php $titulo = 'entrar'; require('../header.php'); ?>

<main class="container-fluid min-vh-100 d-flex flex-column max-width-480">
    <section class="mt-auto mb-4 mx-auto">
        <img src="/static/img/artmux.svg" alt="artmux logo">
        <h1 class="text-primary text-center nunito-black">entrar</h1>
    </section>
    <form class="mb-auto" id="login" method="POST">
        <div class="form-group my-auto">
            <label for="username">usuário</label>
            <input class="form-control" id="username" aria-describedby="zecaurubu" placeholder="zecaurubu">
        </div>
        <div class="form-group mt-2">
            <label for="password">senha</label>
            <input type="password" class="form-control" id="password" placeholder="********">
        </div> 
        <div class="form-check my-4">
            <input type="checkbox" class="form-check-input" id="keepLoggedIn">
            <label class="form-check-label" for="keepLoggedIn">manter conectado</label>
        </div>
        <div class="d-grid gap-2 mx-auto">
            <button class="btn btn-primary" type="submit">entrar</button>
        </div>

        <p class="align-center mt-4 text-center">
            não possui cadastro? 
           <a href="/cadastrar#" class="link-dark"><strong>clique aqui!</strong></a>
        </p>
    </form>

    <section class="mx-auto my-4 nunito">
        <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
    </section>
</main>

<?php require('../scripts.php'); ?>

<script>
    const form  = q.id('login');
    let loading = false;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!loading) {
            loading = true;

            const username = form.username.value;
            const password = form.password.value;
            const keepLoggedIn = form.keepLoggedIn.checked;

            const data = {
                username,
                password,
                keepLoggedIn
            };

            try {
                const response = await request.post('auth/signin', data);
                const body = await response.json();
                console.log(body);
                if (response.status === 200) {
                    storage.setToken(body.token, keepLoggedIn);
                    store.setUser(username, body.token);
                    window.location.href = '/me';
                } else {
                    Swal.fire({
                        title: 'Não foi possível autenticar',
                        text: body.message,
                        icon: 'warning',
                        confirmButtonText: 'ok',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Erro',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: 'ok',
                    confirmButtonColor: '#0d6efd'
                });
            }

            loading = false;
        }
    });
</script>

<?php require('../footer.php') ?>