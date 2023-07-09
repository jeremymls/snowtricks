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
                    $('#addGroupModal').modal('hide');
                }
            } else if (this.readyState === XMLHttpRequest.DONE && this.status === 400) {
                let response = JSON.parse(this.responseText);
                if (response.success === false) {
                    let fieldName = document.querySelector('#group_name');
                    fieldName.classList.add('is-invalid');

                    $parent = fieldName.parentElement;
                    if ($parent.querySelectorAll('.invalid-feedback').length > 0) {
                        $parent.querySelectorAll('.invalid-feedback').forEach(function (feedback) {
                            feedback.remove();
                        });
                    }

                    response.errors.forEach(function (error) {
                        let div = document.createElement('div');
                        div.className = 'invalid-feedback';
                        div.innerHTML = error;
                        fieldName.parentElement.appendChild(div);
                    }                    );
                }
            }
        }

    });

    checkDiv.forEach(function (checkForm) {
        checkForm.className = 'form-check form-check-inline';
        label= checkForm.querySelector('label');
        label.innerHTML = '<i class="fa-brands fa-' + label.innerHTML + '"></i>';
    });

    collection.dataset.index = collection.querySelectorAll('input').length;

    span.querySelector('button').addEventListener('click', () => addButton(collection));

    let providers = document.querySelectorAll('input[name*="[provider]"]');
    providers.forEach((provider) => providerListener(provider));

    let videoId = document.querySelectorAll('input[name*="[video_id]"]');
    videoId.forEach((input) => inputListener(input));
}

function providerListener(provider, add = false) {
    provider.addEventListener('change', function (e) {
        const form = add ? e.target.parentElement.parentElement.parentElement.parentElement :this.parentElement.parentElement.parentElement.parentElement.parentElement.parentElement;
        const video_id = form.querySelector('input[name*="[video_id]"]').value;
        setIframes(form, e.target.value, video_id);
    });
}

function inputListener(input, add = false) {
    input.addEventListener('input', function (e) {
        const form = add ? this.parentElement.parentElement : this.parentElement.parentElement.parentElement.parentElement;
        provider = form.querySelector('input[name*="[provider]"]:checked');
        if (provider == null) {
            return;
        }

        var video_id;

        if (provider.value == 'youtube') {
            if (this.value.split('v=')[1]) {
                video_id = this.value.split('v=')[1]
            } else if (this.value.split('youtu.be/')[1]) {
                video_id = this.value.split('youtu.be/')[1];
            } else if (this.value.split('embed/')[1]) {
                video_id = this.value.split('embed/')[1];
            }
        } else if (provider.value == 'dailymotion') {
            if (this.value.split('video/')[1]) {
                video_id = this.value.split('video/')[1];
            } else if (this.value.split('dai.ly/')[1]) {
                video_id = this.value.split('dai.ly/')[1];
            }
        } else if (provider.value == 'vimeo') {
            if (this.value.split('vimeo.com/')[1]) {
                video_id = this.value.split('vimeo.com/')[1];
            }
        }



        if (video_id) {
            var ampersandPosition = video_id.indexOf('&');
            if(ampersandPosition != -1) {
                video_id = video_id.substring(0, ampersandPosition);
            }
        } else {
            video_id = this.value;
        }
        setIframes(form, provider.value, video_id);
    });
}

function setIframes(form, provider = null, video_id = null) {
    const url = transformURL(provider?form.querySelector('input[name*="[provider]"]:checked').value:null);
    if (form.nextElementSibling != null) {
        form.nextElementSibling.querySelector('iframe').src = url + video_id;
    }
    document.querySelector('#media_' + form.querySelector('fieldset>div').id + ' iframe').src = url + video_id;
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
        default:
            url = ' ';
            break;
    }
    return url;
}

function addButton(collection) {
    let prototype = collection.dataset.prototype;

    let index = collection.dataset.index;

    prototype = prototype.replace(/__name__/g, index);

    let content = document.createElement('html');
    content.innerHTML = prototype;
    let newForm = content.querySelector('div');
    newForm.className = 'col-12 col-lg-6 mb-3';
    newForm.id = 'video_' + index;

    let labels = newForm.querySelectorAll('fieldset fieldset label');
    labels.forEach(function (label) {
        label.innerHTML = '<i class="fa-brands fa-' + label.innerHTML + '"></i>';
        label.parentElement.className = 'form-check-inline';
    });

    let editButton = document.createElement('a');
    editButton.type = 'button';
    editButton.className = 'mx-3';
    editButton.dataset.bsToggle = 'modal';
    editButton.dataset.bsTarget = '#addVideoModal';
    editButton.innerHTML = '<i class="fas fa-pencil-alt text-warning"></i>';

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

    iframeHTML = "<iframe width='100%' height='100%' src='https://www.youtube.com/embed/' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>";

    let videoPreview = document.createElement('div');
    videoPreview.className = 'col-12 col-lg-6 mb-3';
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
    mediaBtnDiv.className = 'mx-3 mt-3 p-1 border border-dark rounded d-flex justify-content-center';

    mediaBtnSlotCol.innerHTML = iframeHTML;
    mediaBtnSlotRow.appendChild(mediaBtnSlotCol);
    videoDisplay.appendChild(mediaBtnSlotRow);
    mediaBtnDiv.appendChild(editButton);
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
