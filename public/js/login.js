$(document).ready(() => {
    $('#loginLink').on('click', () => {
    $('#form').data('method', 'inloggen');
    $('#inputCode').addClass('hidden');
    $('#inputGebruikersnaam').removeClass('hidden');
    $('#inputWachtwoord').removeClass('hidden');
    $('#inputWachtwoord2').addClass('hidden');
    $('#inputEmail').addClass('hidden');
    $('#subheader').html('Inloggen');
    });

    $('#regLink').on('click', () => {
        $('#form').data('method', 'registreren');
        $('#inputCode').addClass('hidden');
        $('#inputGebruikersnaam').removeClass('hidden');
        $('#inputWachtwoord').removeClass('hidden');
        $('#inputWachtwoord2').removeClass('hidden');
        $('#inputEmail').removeClass('hidden');
        $('#subheader').html('Registreren');
    });

    $('#uitnodigingLink').on('click', () => {
        $('#form').data('method', 'uitnodigingscode');
        $('#inputCode').removeClass('hidden');
        $('#inputGebruikersnaam').addClass('hidden');
        $('#inputWachtwoord').addClass('hidden');
        $('#inputWachtwoord2').addClass('hidden');
        $('#inputEmail').addClass('hidden');
        $('#subheader').html('Inloggen met uitnodigingscode');
    });

    $('#form').submit((e) => {
        e.preventDefault();
        method = $('#form').data('method');
        data = new Object();
        data['method'] = method;
        switch(method) {
            case 'uitnodigingscode':
                data['code'] = $('#uitnodigingscode').val();
                break;
            case 'registreren':
                data['gebruikersnaam'] = $('#gebruikersnaam').val();
                data['wachtwoord'] = $('#wachtwoord').val();
                data['wachtwoord2'] = $('#wachtwoord2').val();
                data['email'] = $('#email').val();
                break;
            case 'inloggen':
                data['gebruikersnaam'] = $('#gebruikersnaam').val();
                data['wachtwoord'] = $('#wachtwoord').val();
                break;
        }
        $.ajax({
            url: 'login.php',
            method: "POST",
            data: JSON.stringify(data)
        }).done(res => {
            if(res.status === "successful") {
                location.reload();
            } else {
                console.log(res)
            }
        }).fail(res => console.log(res))
    })
})
