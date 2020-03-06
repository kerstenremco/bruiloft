$("#switchLogin").on("click", () => changeLoginForm("login"));
$("#switchRegister").on("click", () => changeLoginForm("register"));
$("#switchInvite").on("click", () => changeLoginForm("invitecode"));

function changeLoginForm(to) {
  $("#method").val(to);
  $(".input-group").each((index, element) => {
    $(element).addClass("hidden");
  });
  switch (to) {
    case "invitecode":
      fields = ["invitecode"];
      subheader = "Inloggen met uitnodigingscode";
      break;
    case "register":
      fields = [
        "username",
        "password",
        "password2",
        "email"
      ];
      subheader = "Registreren";
      break;
    case "login":
      fields = ["username", "password"];
      subheader = "Inloggen voor bruiden";
      break;
  }
  $(fields).each((index, element) => {
    console.log(element);
    $(`#input-${element}`).removeClass("hidden");
  });
  $("#subheader").html(subheader);
}

$('#loginform').submit(e => {
  e.preventDefault();

  $.ajax({
    url: "login.php",
    type: "POST",
    data: new FormData(e.target),
    contentType: false,
    cache: false,
    processData:false,
  })
    .done(res => {
      if (res.status === "successful") location.replace("/bruiden");
      else showError(res.responseJSON.message);
    })
    .fail(res => showError(res.responseJSON.message));
});

function showError(error)
{
    $('#errorMessage').removeClass('hidden');
    $('#errorMessage').text(error);
}