<?php
$titulo = 'perfil';
require_once('../components/head.php');
require_once('../components/header.php');
?>

<style>
    .nav-item:not(:last-child) {
        margin-right: 24px;
    }

    .social-media-btns {
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }

    .social-media-btns a,
    .social-media-btns button {
        border: none;
    }
</style>

<main class="container-fluid d-flex flex-column max-width-480">

    <ul class="nav nav-pills" role="tablist" style="margin-top: 48px;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="true">Perfil</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="passwords-tab" data-bs-toggle="tab" data-bs-target="#passwords-tab-pane" type="button" role="tab" aria-controls="passwords-tab-pane" aria-selected="false">Senha</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="account-tab" data-bs-toggle="tab" data-bs-target="#accounts-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Contas</button>
        </li>
    </ul>

    <section class="tab-content" style="margin-top: 24px;">
        <section class="tab-pane fade show active" id="profile-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
            <form class="card p-4" method="POST" id="user-info-form">
                <div class=" form-group my-auto">
                    <label for="username" class="form-label">usuário</label>
                    <input class="form-control" id="username" autocomplete="username" aria-describedby="zecaurubu" placeholder="zecaurubu" required />
                </div>
                <div class="form-group mt-2">
                    <label for="email" class="form-label">email</label>
                    <input type="email" class="form-control" id="email" autocomplete="email" placeholder="zecaurubu@artmux.dev" required />
                </div>
                <div class="d-grid mt-4 mx-auto">
                    <button class="btn btn-primary" id="update-info-btn" type="submit">alterar dados</button>
                </div>
            </form>
        </section>
        <section class="tab-pane fade" id="passwords-tab-pane" role="tabpanel" aria-labelledby="passwords-tab" tabindex="0">
            <form class="card p-4" method="POST" id="user-password-form">
                <div class="form-group mt-2">
                    <label for="password" class="form-label">senha atual</label>
                    <input type="password" class="form-control" autocomplete="current-password" id="password" placeholder="********" required />
                </div>
                <div class="form-group mt-2">
                    <label for="newPassword" class="form-label">nova senha</label>
                    <input type="password" class="form-control" autocomplete="new-password" id="newPassword" placeholder="********" required />
                </div>
                <div class="form-group mt-2">
                    <label for="newPasswordConfirmation" class="form-label">confirmação da nova senha</label>
                    <input type="password" class="form-control" autocomplete="new-password" id="newPasswordConfirmation" placeholder="********" required />
                </div>
                <div class="d-grid mt-4 mx-auto">
                    <button class="btn btn-primary" id="update-password-btn" type="submit">alterar senha</button>
                </div>
            </form>
        </section>
        <section class="tab-pane fade" id="accounts-tab-pane" role="tabpanel" aria-labelledby="account-tab" tabindex="0">

        </section>
    </section>
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

    let accountsContainer = q.id('accounts-tab-pane');

    let loading = false;

    window.onload = async () => {
        await loadUser();

        userInfoForm.addEventListener('submit', updateUserInfo);
        userPasswordForm.addEventListener('submit', updateUserPassword);

        if (window.location.hash.substr(1) == 'accounts') openAccountsTab();

        await myAccesses();
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
        } catch (_) {
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

    const openAccountsTab = () => {
        const tabEl = document.querySelector('button[data-bs-target="#accounts-tab-pane"]');
        new bootstrap.Tab(tabEl).show();
    }

    const createAccess = async (socialMediaId) => {
        if (loading) return;
        try {
            loading = true;
            const {
                json
            } = await request.auth.get(`accesses/create/${socialMediaId}`);
            if (json.redirect) window.location.replace(json.redirect);
            else throw new Exception();
        } catch (_) {
            $message.warn('Não é possível conectar uma nova conta no momento. Tente novamente mais tarde.');
        } finally {
            loading = false;
        }
    }

    const removeAccess = async (access, socialMediaId, username) => {
        if (loading) return;
        try {
            loading = true;

            const {
                isConfirmed: remove
            } = await $message.confirm(`Você quer mesmo remover o acesso à ${username}?`)
            if (remove) {
                const {
                    response,
                    json
                } = await request.auth.delete(`accesses/${access}`, {
                    socialMediaId
                });
                if (response.status !== 200) {
                    $message.warn(json.message);
                } else {
                    const {
                        isConfirmed
                    } = await Swal.fire({
                        title: 'Acesso removido!',
                        text: json.message,
                        icon: 'success',
                        cancelButtonText: 'fechar',
                        confirmButtonColor: '#0d6efd',
                        showCancelButton: true,
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'ok!'
                    });
                    if (isConfirmed) {
                        window.open(json.redirect, '_blank').focus();

                        q.id(`access-${access}`).remove();
                    }
                }
            }
        } catch (_) {
            $message.warn('Não foi possível remover seu acesso. Tente novamente mais tarde.');
        } finally {
            loading = false;
        }
    }

    const myAccesses = async () => {
        try {
            const {
                response,
                json
            } = await request.auth.get('accesses/all');

            if (response.status !== 200) {
                $message.warn(json.message);
            } else {
                json.forEach(createCardBySocialMedia)
            }
        } catch (_) {
            $message.warn('Não é possível conectar uma nova conta no momento. Tente novamente mais tarde');
        }
    }

    const createCardBySocialMedia = (socialMedia) => {
        const config = socialMedia.config;
        const accesses = socialMedia.accesses;
        const container = `
            <section class="card p-4 mb-4">
                <h2 class="text-center mb-4" style="color: ${config.btnBgColor}">${socialMedia.name}</h2>

                <div class="social-media-btns">
                    ${accesses.map(access => 
                        `<div class="btn-group mb-4" role="group" id="access-${access.id}">
                            <a href="${access.profilePage}" style="background-color: ${config.btnTextColor}; color: ${config.btnBgColor}; border: 1px solid ${config.btnBgColor}; flex: 20 1 auto;" class="btn btn-primary">${access.username} ${config.btnIcon}</a>
                            <button title="remover acesso" onclick="removeAccess(${access.id}, ${socialMedia.id}, '${access.username}')" type="button" class="btn btn-danger"><i class="bi bi-trash"></i></button>
                        </div>`
                    ).join('')}
                    <button onclick="createAccess(${socialMedia.id})" style="background-color: ${config.btnBgColor}; color: ${config.btnTextColor};" class="btn btn-primary">conectar nova conta ${config.btnIcon}</button>
                </div>
            </section>`;

        accountsContainer.insertAdjacentHTML('beforeend', container);
    }
</script>

<?php require('../components/footer.php') ?>