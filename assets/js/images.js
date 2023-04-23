let links = document.querySelectorAll('[data-delete]');

for (let link of links) {
    link.addEventListener('click', function (e) {
        e.preventDefault();

        if (confirm('Voulez-vous supprimer cette image ?')) {
            fetch(this.getAttribute('href'), {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({'_token': this.dataset.token})
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.parentElement.parentElement.remove();
                    document.getElementById('img[' + this.dataset.id + ']').remove();
                } else {
                    alert(data.error);
                }
            }).catch(e => alert(e));
        }
    });
}

let trickImages = document.querySelector('#trick_images');

trickImages.addEventListener('change', function (e) {
    let files = this.files;
    const preview = document.querySelector('#trick_images_preview');
    const thumbnail_slot = document.querySelector('#thumbnail_slot');
    const trick_miniature = document.querySelector('#trick_miniature');
    const already_add = document.querySelector('#already_add');
    const already_add_count = already_add ? already_add.childElementCount : 0;
    preview.innerHTML = '';

    for (let i = 0; i < files.length; i++) {
        let file = files[i];
        let reader = new FileReader();

        reader.addEventListener('load', function (e) {
            let div = document.createElement('div');
            div.classList.add('col-6', 'col-md-3', 'col-lg-2');

            let image = document.createElement('img');
            image.src = e.target.result;
            image.classList.add('img-fluid', 'mb-2', 'img-thumbnail');
            div.appendChild(image);

            preview.appendChild(div);
        });

        reader.readAsDataURL(file);
    }
});
