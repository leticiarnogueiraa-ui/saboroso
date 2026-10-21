
document.addEventListener('DOMContentLoaded', function () {
    const formularioBusca = document.querySelector('#formBusca');
    const campoBusca = document.querySelector('#campoBusca');

    if (!formularioBusca || !campoBusca) {
        return;
    }

    
    formularioBusca.addEventListener('submit', function (evento) {
        const termo = campoBusca.value.trim();

        if (termo === '') {
            campoBusca.value = '';
        }
    });
});
