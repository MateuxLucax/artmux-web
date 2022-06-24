<?php
$titulo = 'perfil';
require_once('../components/head.php');
require_once('../components/header.php');
?>
<main class="container-fluid d-flex flex-column max-width-480">
    <section class="my-2 mt-4 mx-auto">
        <h1 class="text-primary text-center">perfil</h1>
    </section>
    <form class="card p-4" method="POST" id="user-info-form">
        <div class=" form-group my-auto">
            <label for="username" class="form-label">usuário</label>
            <input class="form-control" id="username" aria-describedby="zecaurubu" placeholder="zecaurubu" required />
        </div>
        <div class="form-group mt-2">
            <label for="email" class="form-label">email</label>
            <input type="email" class="form-control" id="email" placeholder="zecaurubu@artmux.dev" required />
        </div>
        <div class="d-grid mt-4 mx-auto">
            <button class="btn btn-primary" id="update-info-btn" type="submit">alterar dados</button>
        </div>
    </form>

    <section class="my-2 mx-auto">
        <h1 class="text-primary text-center">senha</h1>
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
            <button class="btn btn-primary" id="update-password-btn" type="submit">alterar senha</button>
        </div>
    </form>
</main>

<footer class="mx-auto mt-4 mb-2 nunito text-center">
    <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
</footer>

<?php require('../components/scripts.php') ?>

<script>
    const userInfoForm = q.id('user-info-form');
    const userPasswordForm = q.id('user-password-form');

    const usernameInput = userInfoForm.username;
    const emailInput = userInfoForm.email;
    const passwordInput = userPasswordForm.password;
    const newPasswordInput = userPasswordForm.newPassword;
    const newPasswordConfirmationInput = userPasswordForm.newPasswordConfirmation;

    const updateInfoBtn = q.id('update-info-btn');
    const updatePassword = q.id('update-password-btn');

    let currentUsername;
    let currentEmail;

    window.onload = async () => {
        await loadUser();

        userInfoForm.addEventListener('submit', updateUserInfo);
        userPasswordForm.addEventListener('submit', updateUserPassword);
    };


    const loadUser = async () => {
        const {
            json: data
        } = await request.auth.get('users/me');
        usernameInput.value = data.username;
        currentUsername = data.username;
        emailInput.value = data.email;
        currentEmail = data.email;
    };

    const updateUserInfo = async (e) => {
        e.preventDefault();

        try {
            disableBtn();
            const newUsername = usernameInput.value.trim();
            const newEmail = emailInput.value.trim();
            let data = {};

            if (newUsername && newUsername !== currentUsername) data.username = newUsername;
            if (newEmail && newEmail !== currentEmail) data.email = newEmail;

            const {
                response,
                json
            } = await request.auth.patch('users/me', data);

            if (response.status === 200) {
                let message;
                if (json.user) {
                    message = 'usuário atualizado!';
                } else if (json.email) {
                    message = 'email atualizado!';
                } else {
                    message = 'usuário e email atualizados!';
                }
                $message.success(message);
            } else if (response.status === 400) {
                $message.warn(json.message);
            } else {
                throw 'Não foi possível alterar o usuário';
            }
        } catch (_) {
            $message.warn('Não foi possível alterar suas informações.');
        } finally {
            enableBtn();
        }
    }

    const updateUserPassword = async (e) => {
        e.preventDefault();

        try {
            disableBtn();
            const oldPassword = passwordInput.value.trim();
            const newPassword = newPasswordInput.value.trim();
            const newPasswordConfirmation = newPasswordConfirmationInput.value.trim();

            if (!oldPassword && !newPassword && !newPasswordConfirmation) throw 'Campos nao preenchidos';

            if (newPassword !== newPasswordConfirmation) {
                $message.warn('Nova senha e confirmação incorretas! Verifique elas e tente novamente!');
            } else {
                const {
                    response,
                    json
                } = await request.auth.patch('users/me/password', {
                    oldPassword,
                    newPassword
                });

                if (response.status === 200) {
                    $message.success('Senha alterada com sucesso!');
                } else if (response.status === 400) {
                    $message.warn(json.message);
                } else {
                    $message.error('Não foi possível alterar sua senha!');
                }
            }
        } catch (e) {
            console.log(e);
            $message.warn('Não foi possível alterar a senha!');
        } finally {
            enableBtn();
        }
    }

    const disableBtn = () => {
        updateInfoBtn.disabled = true;
        updateInfoBtn.innerText = 'alterando...';
        updatePassword.disabled = true;
        updatePassword.innerText = 'alterando...';
    }

    const enableBtn = () => {
        updateInfoBtn.disabled = false;
        updateInfoBtn.innerText = 'alterar dados';
        updatePassword.disabled = false;
        updatePassword.innerText = 'alterar senha';
    }
</script>

<?php require('../components/footer.php') ?>