import $, { htmlPrefilter } from "jquery";

var trickContainer = $(".trickContainer");

var offset = 10;

$("#getMoreTricks").on("click", function () {
  $.ajax({
    url: "/getMoreTricks",
    type: "POST",
    data: {
      offset: offset,
    },
    success: function (data) {
      var newTricks = data.newTricks;
      if (data.user && data.user.tricks) {
        var tabOfUserTricksIds = [];
        for (let i = 0; i < data.user.tricks.length; i++) {
          tabOfUserTricksIds[i] = data.user.tricks[i]["id"];
        }
      }
      for (var i = 0; i < newTricks.length; i++) {
        var newDiv = $('<div class="col-md-4 mt-3 text-center"></div>');
        var oneTrickContainer = $('<div class="oneTrickContainer"></div>');
        newDiv.append(oneTrickContainer);
        var newImg = $('<img class="trickHomeImg">');
        newImg.attr(
          "src",
          "/images/tricks/uploads/" + newTricks[i].mainImgName
        );
        newImg.attr("alt", "snowboard trick " + newTricks[i].id);
        var newTitle = $('<h3 class="d-inline-block"></h3>');
        newTitle.html(newTricks[i].title);
        var newLinkShowImg = $("<a></a>");
        newLinkShowImg.attr(
          "href",
          "/trick/" + newTricks[i].slug + "/" + newTricks[i].id
        );
        var newDivLinkShow = $("<div class='pt-3 pb-3'></div>");
        var newLinkShow = $("<a></a>");
        newLinkShow.attr(
          "href",
          "/trick/" + newTricks[i].slug + "/" + newTricks[i].id
        );
        newLinkShow.append(newTitle);
        newLinkShowImg.append(newImg);
        oneTrickContainer.append(newLinkShowImg);
        newDivLinkShow.append(newLinkShow);
        // if a user is connected, user is an object
        if (data.user && data.user.id) {
          var newLinkEdit = $("<a></a>");
          newLinkEdit.attr("href", "/trick/" + newTricks[i].id + "/edit");
          newLinkEdit.html("<i class='fas fa-edit ms-3'></i>");
          newDivLinkShow.append(newLinkEdit);
          // if the user created tricks
          if (
            tabOfUserTricksIds &&
            tabOfUserTricksIds.includes(newTricks[i].id)
          ) {
            var newIndicationDelete = $("<span></span>");
            newIndicationDelete.html(
              "<i class='fas fa-trash-alt ms-3 text-danger'></i>"
            );
            newDivLinkShow.append(newIndicationDelete);
          }
        }
        oneTrickContainer.append(newDivLinkShow);
        trickContainer.append(newDiv);
      }
      if (newTricks.length < 10) {
        // display none
        $("#getMoreTricks").hide();
      }
      if (newTricks.length > 4) {
        $(".goUpContainer").show();
      }
      offset += 10;
    },
    error: function () {
      // nothing
    },
  });
});

$(window).on("resize", function () {
  navChange();
});

// when doc is ready
$(navChange());

function navChange() {
  var nav = $("nav");
  if ($(window).width() < 500) {
    if (nav.hasClass("fixed-top")) {
      nav.removeClass("fixed-top");
      nav.addClass("fixed-bottom");
    }
  } else {
    if (nav.hasClass("fixed-bottom")) {
      nav.removeClass("fixed-bottom");
      nav.addClass("fixed-top");
    }
  }
}
