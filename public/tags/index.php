<?php
$titulo = 'tags';
require_once('../components/head.php');
require_once('../components/header.php');
?>

<style>
    #tags-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(256px, 1fr));
        grid-gap: 24px;
    }

    .tag-card-header {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    .tag-card-header h5 {
        max-width: 128px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<main class="container-fluid px-5">
    <section class="page-title py-5">
        <h3 class="text-primary mb-0">tags</h3>
    </section>

    <section id="tags-container"></section>
</main>

<footer class="mx-auto my-4 nunito text-center">
    <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
</footer>

<?php require('../components/scripts.php') ?>

<script>
    const tagsContainer = q.id('tags-container');
    window.onload = () => {
        loadTags();
    }

    const loadTags = async () => {
        q.empty(tagsContainer);

        try {
            const tags = await request.auth.get('tags');

            for (const tag of tags) {
                let artworksWithTag;
                if (tag.artworks.length) {
                    artworksWithTag = `
                        <ul class="list-group list-group-flush">
                            ${tag.artworks.map(artwork => `<li class="list-group-item"><a href="/obras/detalhe.php?obra=${artwork.slug}" class="card-link">${artwork.title}</a></li>`).join('')}
                        </ul>`;
                }
                const tagCard = `
                            <div class="card">
                                <div class="card-header tag-card-header">
                                    <h5 class="card-title nunito-black text-primary mb-0">${tag.name}</h5>
                                    <div>
                                        <button class="btn btn-primary" onclick="updateTagName('${tag.name}', ${tag.id})">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn btn-danger" onclick="deleteTag('${tag.name}', ${tag.id})">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    ${tag.artworks.length ? `<h6 class="card-subtitle text-muted">Obras que utilizam a tag:</h6>` : ` <p class="card-text">Essa tag não é utilizada por nenhuma obra.</p>`}
                                </div>
                                ${artworksWithTag || ''}
                            </div>`;
                tagsContainer.insertAdjacentHTML('beforeend', tagCard);
            }
        } catch (error) {
            console.error(error);
            $message.warn('Não foi possível carregar as tags')
        }
    }

    const updateTagName = async (oldName, id) => {
        const {
            value: name
        } = await Swal.fire({
            title: 'Alterar o nome da tag',
            input: 'text',
            inputLabel: 'novo nome:',
            inputPlaceholder: oldName,
            confirmButtonColor: '#0d6efd',
        });

        if (name.length) {
            try {

                const response = await request.auth.patch(`tags/${id}`, {
                    id,
                    name
                });

                if (response.updated) {
                    $message.success('Nome alterado com sucesso!')
                    loadTags();
                };
            } catch (_) {
                console.error(_);
                $message.warn('Não foi possível alterar o nome da tag :(');
            }
        }
    }

    const deleteTag = async (tagName, id) => {
        const {
            isConfirmed
        } = await $message.confirm(`Você quer mesmo remover a tag <${tagName}>`)
        if (isConfirmed) {
            try {
                const response = await request.auth.delete(`tags/${id}`);

                if (response.deleted) {
                    $message.success('Tag removida com sucesso!')
                    loadTags();
                };
            } catch (_) {
                console.error(_);
                $message.warn('Não foi possível remover a tag :(');
            }
        }
    }
</script>

<?php require('../components/footer.php') ?>