$(document).ready(() => {
    $("#wishlistTable tbody").sortable({
        update: function(event, ui) {
            $(this).children().each(function(index) {
                if($(this).attr('data-sequence') != index+1) {
                    $(this).attr('data-sequence', index+1);
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
        $('#wishlistTable tr').filter('[data-updated=true]').each(function() {
            sequence.push([$(this).attr('data-index'), $(this).attr('data-sequence')])
        })
        var data = new Object;
        data['method'] = 'updateSequence';
        data['sequence'] = sequence;
        $.ajax({
            url: 'wedding.php',
            method: "POST",
            data: JSON.stringify(data)
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
