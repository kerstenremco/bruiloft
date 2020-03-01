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

    $('#uitloggen').on('click', () => {
        $.ajax({
            url: 'login.php',
            method: "POST",
            data: JSON.stringify({method: 'uitloggen'})
        }).done(res => {
            if(res.status === "successful") {
                location.replace('/bruiden');
            } else {
                console.log(res)
            }
        })
    })

    $(document).on('click', '.verwijderKado', (e) => {
        data = new Object();
        data['method'] = 'deleteKado';
        data['id'] = $(e.target).attr("data-index");
        $.ajax({
            url: 'wedding.php',
            method: "POST",
            data: JSON.stringify(data)
        }).done(res => {
            if(res.status === "successful") {
                $('#wishlistTable tr').filter(`[data-index=${data['id']}]`).remove();
            } else {
                console.log(res)
            }
        })
    })

    $('#weddingForm').submit((e) => {
        e.preventDefault();
        data = new Object();
        data['method'] = 'create';
        data['person1'] = $('#person1').val();
        data['person2'] = $('#person2').val();
        data['date'] = $('#date').val();
        $.ajax({
            url: 'wedding.php',
            method: "POST",
            data: JSON.stringify(data)
        }).done(res => {
            if(res.status === "successful") {
                location.reload();
            } else {
                console.log(res)
            }
        })
    })

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
})