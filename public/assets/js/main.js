$(document).ready(function() {
    // Initialize DataTables for all tables with class 'data-table'
    $('.data-table').each(function() {
        $(this).DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [[0, 'asc']]
        });
    });

    // Generic modal edit button handler
    $('.edit-btn').on('click', function() {
        var id = $(this).data('id');
        var url = $(this).data('url');
        $.ajax({
            url: url,
            type: 'GET',
            data: { id: id, action: 'get' },
            success: function(response) {
                if (response.success) {
                    // Populate modal fields dynamically
                    $.each(response.data, function(key, value) {
                        $('#edit-' + key).val(value);
                    });
                    $('#editModal').modal('show');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error fetching data.');
            }
        });
    });

    // Generic delete button handler
    $('.delete-btn').on('click', function() {
        var id = $(this).data('id');
        $('#deleteId').val(id);
    });

    // Generic AJAX form submission
    $('.ajax-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error submitting form.');
            }
        });
    });
});