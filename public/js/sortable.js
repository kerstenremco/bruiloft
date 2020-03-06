$(document).ready(() => {
  
    $("#wishlistTable tbody").sortable({
        update: function(event, ui) {
            $(this).children().each(function(index) {
                if($(this).attr('data-sequence') != index) {
                    $(this).attr('data-sequence', index);
                    $(this).attr('data-updated', true);
                } else {
                    $(this).attr('data-updated', false);
                }
            })
            saveNewOrder();
        }
    });

    function saveNewOrder() {
        var sequence = [];
        $('#wishlistTable tr').filter('[data-updated=true]').not('#exampleRow').each(function() {
            sequence.push([$(this).attr('data-name'), $(this).attr('data-sequence')])
        })
        form = new FormData();
        form.append('method', 'updateSequence');
        form.append('sequence', sequence);
        $.ajax({
            url: 'wedding.php',
            type: "POST",
            data: form,
            contentType: false,
            cache: false,
            processData:false
        }).done(res => {
            if(res.status !== "successful") disableSortable();
        }).fail(res => disableSortable());
    }


    function disableSortable()
    {
        $("#wishlistTable tbody").sortable("disable");
        alert('De volgorde kan niet worden verwerkt, refresh de pagina!');
    }
});
