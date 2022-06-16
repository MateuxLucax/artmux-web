<?php $titulo = 'cadastrar'; require('../components/head.php'); ?>

<main class="container-fluid min-vh-100 d-flex flex-column max-width-480">
    <section class="mt-auto mb-4 mx-auto">
        <img src="/static/img/artmux.svg" alt="artmux logo" />
        <h1 class="text-primary text-center nunito-black">cadastrar</h1>
    </section>
    <form class="mb-auto" method="POST" novalidate>
        <div class="form-group my-auto">
            <label for="username" class="form-label">usuário</label>
            <input class="form-control" id="username" aria-describedby="zecaurubu" placeholder="zecaurubu" required />
        </div>
        <div class="form-group mt-2">
            <label for="email" class="form-label">email</label>
            <input class="form-control" id="email" type="email" aria-describedby="zecaurubu" placeholder="zecaurubu" required />
            <div class="invalid-feedback">
                Seu email está incorreto.
            </div>
        </div>
        <div class="form-group mt-2">
            <label for="password" class="form-label">senha</label>
            <input type="password" class="form-control" id="password" placeholder="********" required />
            <div class="invalid-feedback">
                As senhas não são compatíveis.
            </div>
        </div>
        <div class="form-group mt-2 mb-4">
            <label for="passwordConfirmation" class="form-label">confirme a senha</label>
            <input type="password" class="form-control" id="passwordConfirmation" placeholder="********" required />
            <div class="invalid-feedback">
                As senhas não são compatíveis.
            </div>
        </div>
        <div class="d-grid gap-2 mx-auto">
            <button class="btn btn-primary" type="submit" id="submit-btn">cadastrar</button>
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

<?php require('../components/scripts.php'); ?>

<script>
    const form  = q.sel('form');
    const submitButton = q.id('submit-btn');
    let loading = false;

    form.addEventListener('submit', async event => {
        event.preventDefault();

        if (!loading) {
            loading = true;
            let valid = true;

            const { username, email, password, passwordConfirmation } = form;

            if (password.value !== passwordConfirmation.value) {
                passwordConfirmation.classList.add('is-invalid');
                password.classList.add('is-invalid');
                event.stopPropagation();
                valid = false;
            } else {
                passwordConfirmation.classList.remove('is-invalid');
                password.classList.remove('is-invalid');
            }

            if (!email.validity.valid) {
                email.classList.add('is-invalid');
                event.stopPropagation();
                valid = false;
            } else {
                email.classList.remove('is-invalid');
            }

            if (valid) {
                await sendForm(username.value, email.value, password.value);
            }

            loading = false;
        }
    });

    const sendForm = async (username, email, password) => {
        submitButton.disabled = true;
        submitButton.innerText = 'cadastrando...';

        try {
            const response = await request.post('auth/signup', {
                username,
                email,
                password
            });
            const body = await response.json();
            if (response.status === 200) {
                window.location.href = '/entrar';
            } else {
                Swal.fire({
                    title: 'Não foi possível cadastrar',
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
        } finally {
            submitButton.disabled = false;1
            submitButton.innerText = 'cadastrar';
        }
    }
</script>

<?php require('../components/footer.php') ?>