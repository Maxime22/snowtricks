import $, { htmlPrefilter } from 'jquery';

var trickContainer = $('.trickContainer');

var offset = 10;
$('#getMoreTricks').on("click",function () {
    $.ajax({
        url: "/getMoreTricks",
        type: "POST",
        data: {
            'offset': offset
        },
        success: function (data) {
            var newTricks = data.newTricks
            if(data.user.tricks){
                var tabOfUserTricksIds = []
                for (let i = 0; i < data.user.tricks.length; i++) {
                    tabOfUserTricksIds[i] = data.user.tricks[i]["id"];
                }
            }
            for(var i=0;i<newTricks.length;i++){
                var newDiv = $('<div class="col-md-4 mt-3"></div>')
                var newImg = $('<img class="trickHomeImg">')
                newImg.attr("src", "/images/tricks/uploads/"+ newTricks[i].mainImgName)
                newImg.attr("alt","snowboard figure "+ newTricks[i].id)
                var newTitle = $('<h3></h3>')
                newTitle.html(newTricks[i].title)
                var newLinkShow = $('<a></a>')
                newLinkShow.attr("href","/trick/"+newTricks[i].slug+"/"+newTricks[i].id)
                newLinkShow.html("Voir la figure")
                newDiv.append(newImg)
                newDiv.append(newTitle)
                newDiv.append(newLinkShow)
                // if a user is connected, user is an object
                if(data.user.id){
                    var newLinkEdit = $('<a></a>')
                    newLinkEdit.attr("href","/trick/"+newTricks[i].id+"/edit")
                    newLinkEdit.html("Editer la figure")
                    newDiv.append(newLinkEdit)
                    // if the user created tricks
                    if(tabOfUserTricksIds && tabOfUserTricksIds.includes(newTricks[i].id)){
                        var newIndicationDelete = $('<div></div>')
                        newIndicationDelete.html("Supprimable")
                        newDiv.append(newIndicationDelete)
                    }
                }
                
                trickContainer.append(newDiv)
            }
            if(newTricks.length < 10){
                // display none
                $('#getMoreTricks').hide();
            }
            offset += 10;
        },
        error: function () { // nothing
        }
    })
})