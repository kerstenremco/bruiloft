$(document).ready(() => {
    $("#wishlistTable tbody").sortable({
        update: function(event, ui) {
            $(this).children().each(function(index) {
                if($(this).attr('data-positie') != index+1) {
                    $(this).attr('data-positie', index+1);
                    $(this).attr('data-updated', true);
                } else {
                    $(this).attr('data-updated', false);
                }
            })
            saveNewOrder();
        }
    });

    function saveNewOrder() {
        var order = [];
        $('#wishlistTable tr').filter('[data-updated=true]').each(function() {
            order.push([$(this).attr('data-index'), $(this).attr('data-positie')])
        })
        var data = new Object;
        data['method'] = 'updateOrder';
        data['order'] = order;
        $.ajax({
            url: 'wedding.php',
            method: "POST",
            data: JSON.stringify(data)
        }).done(res => {
            if(res.status === "successful") {
                console.log('success');
            } else {
                console.log(res)
            }
        })
    }
});