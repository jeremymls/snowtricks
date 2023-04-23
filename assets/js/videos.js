let videoLinks = document.querySelectorAll('[data-delete-video]');
for (let videoLink of videoLinks) {
    videoLink.addEventListener('click', function (e) {
        e.preventDefault();

        if (confirm('Voulez-vous supprimer cette vidéo ?')) {
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
                    console.log('media_trick_videos_' + this.dataset.loop);
                    document.getElementById('media_trick_videos_' + this.dataset.loop).remove();
                    const inModal = document.getElementById('trick_videos_' + this.dataset.loop);
                    if (inModal) {
                        let parent = inModal.parentElement.parentElement;
                        parent.nextElementSibling.nextElementSibling.remove();
                        parent.nextElementSibling.remove();
                        parent.remove();
                    }
                } else {
                    alert(data.error);
                }
            })
            // .catch(e => alert(e));
        }
    });
}