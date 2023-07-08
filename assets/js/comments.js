// pagination
$('#btnNext').on('click', function() {
    $.ajax({
        url: $(this).data('action'),
        type: 'GET',
        success: function(data) {
            data.action ? $('#btnNext').data('action', data.action) : $('#btnNext').hide();
            html = "";
            data.comments.forEach(comment => {
                html += `<div class="row mb-3 align-items-center" id='comment-${comment.id}'>
                    <div class="col-2 text-center">
                        <img src="${comment.src}" class="img-fluid rounded-circle user_image shadow w-50">
                    </div>
                    <div class="col border border-dark rounded p-2 pt-3 m-0 shadow-sm position-relative">
                        <div class="row justify-content-between">
                            <div class="col-auto">
                                <em>${comment.user}</em>
                            </div>
                            <div class="col-auto">
                                <em>${comment.createdAt}</em>
                            </div>
                        </div>
                        <p>${comment.text}</p>
                        <button 
                            data-bs-toggle="modal"
                            data-bs-target="#deleteCommentModal"
                            data-id="${comment.id}"
                            data-token="{{ csrf_token('deleteTheComment') }}"
                            class="position-absolute top-0 start-100 translate-middle btn btn-outline btn-sm border-0 "
                        >
                            <span id="delete-comment-${comment.id}" class="fa-stack" style="font-size: 1rem; vertical-align: top;">
                                <i class="fa-solid fa-circle fa-stack-2x text-danger"></i>
                                <i class="fa-solid fa-times fa-stack-1x text-white"></i>
                            </span>
                        </button>
                    </div>
                </div>`;
            });
            $('#comments').append(html);
        }
    });
});
