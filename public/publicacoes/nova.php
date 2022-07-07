<?php
$titulo = "nova publicação";
require_once('../components/head.php');
require_once('../components/header.php');
?>

<main class="container px-4">
    <section class="page-title py-5">
        <h3 class="text-primary mb-0"><?= $titulo ?></h3>
    </section>
    <form method="" action="">
        <div class="card">
            <div class="card-body">

                <div class="row mb-3">
                    <label class="form-label col-sm-2">Título</label>
                    <div class="col-sm-10">
                        <input type="text" name="title" placeholder="Sem título" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="form-label col-sm-2">Conteúdo</label>
                    <div class="col-sm-10">
                        <textarea required name="text" class="form-control"></textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="form-label col-sm-2">Obras</label>
                    <div class="col-sm-10" id="container-input-obras">
                    </div>
                </div>

            </div>
            <div class="card-footer text-center">
                <button class="btn btn-secondary me-4" onclick="history.back()">Cancelar</button>
                <button onclick="save()" class="btn btn-primary me-4">Salvar</button>
                <button onclick="save(true)" class="btn btn-primary">Salvar e Publicar</button>
            </div>
        </div>
    </form>

</main>

<footer class="mx-auto my-4 nunito text-center">
    <h6 class="text-black-50 mb-0">© <?= date("Y") ?> - artmux</h5>
</footer>

<?php require('../components/scripts.php') ?>

<script>
    const artworksInput = new ArtworksInput();
    q.id('container-input-obras').append(artworksInput.element);

    const form = q.sel('form');

    form.onsubmit = (e) => {
        e.preventDefault();
    }

    const save = async (publish) => {
        let title = form.title.value.trim();
        if (title == '') title = 'Sem título';
        const text = form.text.value.trim();
        const artworks = artworksInput.value;
        const body = {
            title,
            text,
            artworks
        };

        try {
            const {
                response,
                json
            } = await request.auth.post('publications', body);

            if (response.status !== 201) throw ['Resposta não-ok', response];

            agendarAlertaSucesso('Publicação criada com sucesso');
            window.location.assign(`/publicacoes/detalhe.php?publicacao=${json.slug}&publicar=${publish}`);
        } catch (err) {
            console.error(err);
            $message.error('Ocorreu um erro ao criar a publicação. Tente novamente mais tarde.');
        }
    }
</script>

<?php require('../components/footer.php') ?>