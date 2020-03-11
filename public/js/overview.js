// uitloggen button
$("#uitloggen").on("click", () => {
  form = new FormData();
  form.append('method', 'logout');
  $.ajax({
    url: "login.php",
    type: "POST",
    data: form,
    contentType: false,
    cache: false,
    processData:false,
  })
    .done(res => {
      if (res.status == "successful") location.replace('/');
      })
});

// claim cadeau button
$(".btn-claimgift").on("click", e => {
  form = new FormData();
  form.append('method', 'claimGift');
  form.append('name', $(e.target).attr("data-index"));
  $.ajax({
    url: "wedding.php",
    type: "POST",
    data: form,
    contentType: false,
    cache: false,
    processData:false,
  })
    .done(res => {
      if (res.status == "successful") {
        $(e.target).prop("disabled", true);
        $(e.target).html("Cadeau geclaimd");
      }
      })
});

// handle klik op gift afbeelding
$(document).on("click", ".giftimage", e => {
  imagesrc = $(e.target)[0].src;
  if($(e.target).hasClass('ownimage')) {
    $("#imageModal").modal("show");
    $("#imageModal").find('img').attr('src', $(e.target)[0].src);
  }
});

// handle switch formulier bruiloft registreren
$("#weddingFormSwitch").on("click", function() {
  currentMode = $("input[name='method']").val();
  if (currentMode == "create") {
    $("input[name='method']").val("linkpartner");
    $("#weddingFormLink").removeClass("hidden");
    $("#weddingFormCreate").addClass("hidden");
    $("#weddingFormSwitch").html("Ik wil een nieuwe bruiloft aanmaken");
  } else {
    $("input[name='method']").val("create");
    $("#weddingFormCreate").removeClass("hidden");
    $("#weddingFormLink").addClass("hidden");
    $("#weddingFormSwitch").html("Ik heb een koppelcode");
  }
});

// handle submit nieuwe bruiloft
$("#weddingForm").submit(e => {
  e.preventDefault();
  method = $("input[name='method'").val();
  $.ajax({
    url: "wedding.php",
    type: "POST",
    data: new FormData(e.target),
    contentType: false,
    cache: false,
    processData:false,
  })
    .done(res => {
      if (res.status == "successful") {
        if (method == 'update') {
          if(res.message.wedding.image != null) $('#weddingimage').attr('src', `public/img/weddings/${res.message.wedding.image}`)
          $('input[name="image"').val(null);
          showSuccess('Bruiloft bijgewerkt! 💒');
        }
        else location.replace('/');
      }
      else showError(res.responseJSON.message);
    })
    .fail(res => showError(res.responseJSON.message));
});

// handle invite mail
$("#inviteform").on('submit', e => {
  e.preventDefault();
  $.ajax({
    url: "wedding.php",
    type: "POST",
    data: new FormData(e.target),
    contentType: false,
    cache: false,
    processData:false,
  })
    .done(res => {
      if (res.status == "successful") {
        showSuccess("Mail is verstuurd!");
        $("input[name='email']").val("");
      } else showError(res.responseJSON.message);
    })
    .fail(res => showError(res.responseJSON.message));
});

// handle gift toevoegen
$("#addGift").on("click", function() {
  $("#giftModalTitle").text("Cadeau toevoegen");
  $("input[name='name'").val("");
  $("input[name='summary'").val("");
  $("input[name='image'").val(null);
  $("input[name='method']").val("createGift");
  $("#messageModel").addClass("hidden");
  $("#giftModal").modal("show");
});

// handle gift bewerken
$(document).on("click", ".btn-editgift", function(e) {
  name = $(e.target).closest("tr").find(".name").text();
  summary = $(e.target).closest("tr").find(".summary").text();
  $("#giftModalTitle").text("Cadeau bewerken");
  $("input[name='name']").val(name);
  $("input[name='summary']").val(summary);
  $("input[name='image'").val(null);
  $("input[name='method']").val("updateGift");
  $("input[name='oldname']").val(name);
  $("#messageModel").addClass("hidden");
  $("#giftModal").modal("show");
});

// handle submit gift form
$("#giftForm").on("submit", function(e) {
  e.preventDefault();
  method = $("input[name='method']").val();
  $.ajax({
    url: "wedding.php",
    type: "POST",
    data: new FormData(this),
    contentType: false,
    cache: false,
    processData:false,
  })
    .done(res => {
      if (res.status == "successful") {
        $("#giftModal").modal("hide");
        showSuccess("Cadeau opgeslagen 😁");
        if (method == "updateGift") updateRow(res.message.gift);
        else if (method == "createGift") createRow(res.message.gift);
      } else showErrorInModel(res.responseJSON.message);
    })
    .fail(res => showErrorInModel(res.responseJSON.message));
});

// handle remove gift
$(document).on("click", ".btn-deletegift", function(e) {
  giftname = $(this).closest('tr').attr('data-name');
  form = new FormData();
  form.append('method', 'delete');
  form.append('name', giftname);
  $.ajax({
    url: "wedding.php",
    type: "POST",
    data: form,
    contentType: false,
    cache: false,
    processData:false,
  })
    .done(res => {
      if (res.status == "successful") {
        $("#giftModal").modal("hide");
        showSuccess("Cadeau verwijderd ❌");
        $(this).closest('tr').remove();
      } else showErrorInModel(res.responseJSON.message);
    })
    .fail(res => showErrorInModel(res.responseJSON.message));
});

// functions

function updateRow(gift) {
  oldName = $("input[name='oldname']").val();
  row = $(`tr[data-name="${oldName}"]`);
  row.attr("data-name", gift.name);
  row.find(".name").text(gift.name);
  row.find(".summary").text(gift.summary);
  if(gift.image != null) {
    row.find("img").attr('src', `public/img/gifts/${gift.image}`);
    row.find("img").addClass('ownimage');
  }
}

function createRow(gift) {
  clone = $("#exampleRow").clone();
  $("#giftTableBody").append(clone);
  clone.attr("data-name", gift.name);
  clone.attr("data-sequence", gift.sequence);
  clone.find(".name").text(gift.name);
  clone.find(".summary").text(gift.summary);
  if(gift.image != null) {
    clone.find("img").attr('src', `public/img/gifts/${gift.image}`);
    clone.find("img").addClass('ownimage');
  }
  clone.removeClass("hidden");
  clone.attr("id", "");
}

function showErrorInModel(message) {
  $("#messageModel").removeClass("hidden");
  $("#messageModel").text(message);
}

function showError(message) {
  $("#message").removeClass("hidden");
  $("#message").removeClass("alert-success");
  $("#message").addClass("alert-danger");
  $("#message").text(message);
}

function showSuccess(message) {
  $("#message").removeClass("hidden");
  $("#message").addClass("alert-success");
  $("#message").removeClass("alert-danger");
  $("#message").text(message);
  setTimeout(() => $("#message").addClass("hidden"), 5000);
}
