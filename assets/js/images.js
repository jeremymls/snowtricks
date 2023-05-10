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
    const preview = document.querySelector('#media-display');
    const thumbnail_slot = document.querySelector('#thumbnail_slot');
    const trick_miniature = document.querySelector('#trick_miniature');
    const already_add = document.querySelector('#already_add');
    const already_add_count = already_add ? already_add.childElementCount : 0;
    const preview_img = document.querySelectorAll('.preview-img');
    const separator = document.querySelector('#media-separator');
    preview_img.forEach(image => {
        image.remove();
    });

    for (let i = 0; i < files.length; i++) {
        let file = files[i];
        let reader = new FileReader();

        reader.addEventListener('load', function (e) {
            let div_preview = document.createElement('div');
            div_preview.classList.add('col-3', 'col-md-2', 'preview-img');

            let div_preview_row = document.createElement('div');
            div_preview_row.classList.add('row', 'preview-div', 'align-items-center');

            let div_preview_col = document.createElement('div');
            div_preview_col.classList.add('col-12');

            let image = document.createElement('img');
            image.src = e.target.result;
            image.classList.add('img-fluid');

            div_preview_col.appendChild(image);
            div_preview_row.appendChild(div_preview_col);
            div_preview.appendChild(div_preview_row);
            preview.insertBefore(div_preview, separator);
        });

        reader.readAsDataURL(file);
    }
});
