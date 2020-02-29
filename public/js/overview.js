$(document).ready(() => {
    $('#uitloggen').on('click', () => {
        console.log(1);
        $.ajax({
            url: 'login.php',
            method: "POST",
            data: JSON.stringify({method: 'uitloggen'})
        }).done(res => {
            if(res.status === "successful") {
                location.reload();
            } else {
                console.log(res)
            }
        })
    })

    $('#weddingForm').submit((e) => {
        e.preventDefault();
        data = new Object()
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
})