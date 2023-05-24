// Change title
$('#trick_name').on('input', function () {
    $('#title-header').text($(this).val());
});

let collection, buttonAdd, span, checkInput, checkDiv, mediaDisplay, mediaBtn, groupSave;
window.onload = function () {
    groupSave = document.querySelector('#group_save');
    collection = document.querySelector('#videos');
    span = collection.querySelector('span');
    checkDiv = collection.querySelectorAll('.form-check');
    mediaDisplay = document.querySelector('#media-display');
    mediaBtn = document.querySelector('#media-btn');

    groupSave.addEventListener('click', function (e) {
        e.preventDefault();
        let name = document.querySelector('#group_name').value;
        let description = document.querySelector('#group_description').value;

        let data = new FormData();
        data.append('name', name);
        data.append('description', description);

        let request = new XMLHttpRequest();
        request.open('POST', this.dataset.action);
        request.send(data);

        request.onreadystatechange = function () {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                let response = JSON.parse(this.responseText);
                if (response.success) {
                    const trick_category = document.querySelector('#trick_category');
                    let option = document.createElement('option');
                    option.value = response.id;
                    option.innerHTML = response.group;
                    trick_category.appendChild(option);
                    trick_category.value = response.id;
                }
            }
        }

    });

    checkDiv.forEach(function (checkForm) {
        checkForm.className = 'form-check form-check-inline';
        label= checkForm.querySelector('label');
        label.innerHTML = '<i class="fa-brands fa-' + label.innerHTML + '"></i>';
    });

    buttonAdd = document.createElement('button');
    buttonAdd.className = 'btn btn-success add-video';
    buttonAdd.innerHTML = '<i class="fas fa-plus"></i> Ajouter une vidéo';

    let newButton = span.appendChild(buttonAdd);

    collection.dataset.index = collection.querySelectorAll('input').length;

    buttonAdd.addEventListener('click', function (e) {
        addButton(collection, newButton);
    });

    let providers = document.querySelectorAll('input[name*="[provider]"]');
    providers.forEach(function (provider) {
        providerListener(provider);
    });

    let videoId = document.querySelectorAll('input[name*="[video_id]"]');
    videoId.forEach(function (input) {
        inputListener(input);
    });
}

function providerListener(provider, add = false) {
    provider.addEventListener('change', function (e) {
        const form = add ? e.target.parentElement.parentElement.parentElement.parentElement :this.parentElement.parentElement.parentElement.parentElement.parentElement.parentElement;
        const video_id = form.querySelector('input[name*="[video_id]"]').value;
        const url = transformURL(e.target.value);
        if (form.nextElementSibling != null) {
            form.nextElementSibling.querySelector('iframe').src = url + video_id;
        }
    });
}

function inputListener(input, add = false) {
    input.addEventListener('input', function (e) {
        const form = add ? this.parentElement.parentElement : this.parentElement.parentElement.parentElement.parentElement;
        provider = form.querySelector('input[name*="[provider]"]:checked');
        const url = transformURL(provider?form.querySelector('input[name*="[provider]"]:checked').value:null);
        form.nextElementSibling.querySelector('iframe').src = url + this.value;
        document.querySelector('#media_' + this.parentElement.parentElement.id + ' iframe').src = url + this.value;
    });
}

function transformURL(provider) {
    let url = '';
    switch (provider) {
        case 'youtube':
            url = 'https://www.youtube.com/embed/';
            break;
        case 'dailymotion':
            url = 'https://www.dailymotion.com/embed/video/';
            break;
        case 'vimeo':
            url = 'https://player.vimeo.com/video/';
            break;
        case 'facebook':
            url = 'https://www.facebook.com/plugins/video.php?href=';
            break;
        default:
            url = ' ';
            break;
    }
    return url;
}

function addButton(collection, newButton) {
    let prototype = collection.dataset.prototype;

    let index = collection.dataset.index;

    prototype = prototype.replace(/__name__/g, index);

    let content = document.createElement('html');
    content.innerHTML = prototype;
    let newForm = content.querySelector('div');
    newForm.className = 'col-md-6 mb-3';
    newForm.id = 'video_' + index;

    let labels = newForm.querySelectorAll('fieldset fieldset label');
    labels.forEach(function (label) {
        label.innerHTML = '<i class="fa-brands fa-' + label.innerHTML + '"></i>';
        label.parentElement.className = 'form-check-inline';
    });

    let deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.className = 'btn btn-sm btn-danger';
    deleteButton.id = 'delete-video' + index;
    deleteButton.innerHTML = '<i class="fas fa-trash"></i> Supprimer';

    let deleteModalButton = document.createElement('a');
    deleteModalButton.className = 'mx-2';
    deleteModalButton.id = 'delete-video' + index;
    deleteModalButton.innerHTML = '<i class="fas fa-trash-alt text-danger"></i>';

    newForm.appendChild(deleteButton);

    iframeHTML = "<iframe width='100%' height='100%' src='' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>";

    let videoPreview = document.createElement('div');
    videoPreview.className = 'col-md-6 mb-3';
    videoPreview.innerHTML = iframeHTML;

    let videoDisplay = document.createElement('div');
    videoDisplay.className = 'col-3 col-md-2';
    videoDisplay.id = 'media_video_' + index;

    mediaBtnSlotRow = document.createElement('div');
    mediaBtnSlotRow.className = 'row preview-div align-items-center';

    mediaBtnSlotCol = document.createElement('div');
    mediaBtnSlotCol.className = 'col-12';

    let divider = document.createElement('hr');
    divider.className = 'my-2';

    let mediaBtnDiv = document.createElement('div');
    mediaBtnDiv.className = 'mx-3 mt-3 p-1 border border-dark rounded';

    mediaBtnSlotCol.innerHTML = iframeHTML;
    mediaBtnSlotRow.appendChild(mediaBtnSlotCol);
    videoDisplay.appendChild(mediaBtnSlotRow);
    mediaBtnDiv.appendChild(deleteModalButton);
    videoDisplay.appendChild(mediaBtnDiv);

    collection.dataset.index++;

    let addButton = collection.querySelector('.add-video');

    newForm.querySelectorAll('input[name*="[provider]"]').forEach(function (e) {
        providerListener(this, true);
    });


    inputListener(newForm.querySelector('input[name*="[video_id]"]'), true);

    span.insertBefore(newForm, addButton);
    span.insertBefore(videoPreview, addButton);
    span.insertBefore(divider, addButton);

    mediaDisplay.appendChild(videoDisplay);
    // videoDisplay.appendChild(mediaBtnSlot);

    deleteButton.addEventListener('click', function (e) {
        e.preventDefault();
        deleteVideo();
    });

    deleteModalButton.addEventListener('click', function (e) {
        e.preventDefault();
        deleteVideo();
    });

    function deleteVideo() {
            mediaBtnDiv.remove();
            videoDisplay.remove();
            newForm.remove();
            videoPreview.remove();
            divider.remove();
    }
}
