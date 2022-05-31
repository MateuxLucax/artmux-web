<?php $titulo = 'cadastrar'; require('../header.php'); ?>

<main class="container-fluid min-vh-100 d-flex flex-column max-width-480">
    <section class="mt-auto mb-4 mx-auto">
        <img src="/static/img/artmux.svg" alt="artmux logo">
        <h1 class="text-primary text-center nunito-black">cadastrar</h1>
    </section>
    <form class="mb-auto" method="POST" id="signup-form" novalidate>
        <div class="form-group my-auto">
            <label for="username">usuário</label>
            <input class="form-control" id="username" aria-describedby="zecaurubu" placeholder="zecaurubu" required />
        </div>
        <div class="form-group mt-2">
            <label for="email">email</label>
            <input class="form-control" id="email" type="email" aria-describedby="zecaurubu" placeholder="zecaurubu" required />
            <div class="invalid-feedback">
                Seu email está incorreto.
            </div>
        </div>
        <div class="form-group mt-2">
            <label for="password">senha</label>
            <input type="password" class="form-control" id="password" placeholder="********" required />
            <div class="invalid-feedback">
                As senhas não são compatíveis.
            </div>
        </div>
        <div class="form-group mt-2 mb-4">
            <label for="passwordConfirmation">confirme a senha</label>
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

<?php require('../scripts.php'); ?>

<script>
    const form  = q.id('signup-form');
    const submitButton = q.id('submit-btn');
    let loading = false;

    form.addEventListener('submit', async event => {
        event.preventDefault();
        let valid = true;

        const password = form.password.value;
        const passwordConfirmation = form.passwordConfirmation.value;

        if (password !== passwordConfirmation) {
            form.passwordConfirmation.classList.add('is-invalid');
            form.password.classList.add('is-invalid');
            event.stopPropagation();
            valid = false;
        } 

        if (!form.email.validity.valid) {
            form.email.classList.add('is-invalid');
            event.stopPropagation();
            valid = false;
        } 

        if (valid) {
            form.passwordConfirmation.classList.remove('is-invalid');
            form.password.classList.remove('is-invalid');
            form.email.classList.remove('is-invalid');
            await sendForm();
        }
    })

    const sendForm = async () => {
        if (!loading) {
            loading = true;
            submitButton.disabled = true;
            submitButton.innerText = `cadastrando...`;

            const username = form.username.value;
            const email = form.email.value;
            const password = form.password.value;

            const data = {
                username,
                email,
                password
            };

            try {
                const response = await request.post('auth/signup', data);
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
                loading = false;
                submitButton.disabled = false;
                submitButton.innerText = `cadastrar`;
            }
        }
    }
</script>
<?php require('../footer.php') ?>