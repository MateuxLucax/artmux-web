<?php $titulo = 'entrar';
require('../components/head.php'); ?>

<main class="container-fluid min-vh-100 d-flex flex-column max-width-480">
    <section class="mt-auto mb-4 mx-auto">
        <img src="/static/img/artmux.svg" alt="artmux logo" />
        <h1 class="text-primary text-center nunito-black">entrar</h1>
    </section>
    <form class="mb-auto" method="POST">
        <div class="form-group my-auto">
            <label for="username" class="form-label">usuário</label>
            <input class="form-control" autocomplete="username" id="username" aria-describedby="zecaurubu" placeholder="zecaurubu" required />
        </div>
        <div class="form-group mt-2">
            <label for="password" class="form-label">senha</label>
            <input type="password" autocomplete="current-password" class="form-control" id="password" placeholder="********" required />
        </div>
        <div class="form-check my-4">
            <input type="checkbox" class="form-check-input" id="keepLoggedIn" />
            <label class="form-check-label" for="keepLoggedIn">manter conectado</label>
        </div>
        <div class="d-grid gap-2 mx-auto">
            <button class="btn btn-primary" id="login-btn" type="submit">entrar</button>
        </div>

        <p class="align-center mt-4 text-center">
            não possui cadastro?
            <a href="/cadastrar#" class="link-dark"><strong>clique aqui!</strong></a>
        </p>
    </form>

    <footer class="mx-auto my-4 nunito">
        <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
    </footer>
</main>

<?php require('../components/scripts.php'); ?>

<script>
    window.onload = async () => {
        if (storage.getToken() !== null) {
            window.location = '/publicacoes';
        }
    };

    const form = q.sel('form');
    const loginButton = q.id('login-btn');
    let loading = false;

    form.addEventListener('submit', async e => {
        e.preventDefault();

        if (!loading) {
            loading = true;
            loginButton.disabled = true;
            loginButton.innerText = `entrando...`;

            const {
                username,
                password,
                keepLoggedIn
            } = form;

            try {
                const response = await request.post('auth/signin', {
                    username: username.value,
                    password: password.value,
                    keepLoggedIn: keepLoggedIn.value
                });
                const body = await response.json();
                if (response.status === 200) {
                    storage.setToken(body.token, keepLoggedIn);
                    window.location.href = '/publicacoes';
                } else {
                    $message.warn(body.message, 'Não foi possível autenticar!');
                }
            } catch (error) {
                $message.error(error.message);
            } finally {
                loading = false;
                loginButton.disabled = false;
                loginButton.innerText = `entrar`;
            }
        }
    });
</script>

<?php require('../components/footer.php') ?>