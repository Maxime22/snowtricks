import $, { htmlPrefilter } from 'jquery';

var trickContainer = $('.trickContainer');
console.log(trickContainer)

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
            for(var i=0;i<newTricks.length;i++){
                var newDiv = $('<div class="col-md-4 mt-3"></div>')
                var newImg = $('<img class="trickHomeImg">')
                newImg.attr("src", "/images/tricks/uploads/"+ newTricks[i].mainImgName)
                newImg.attr("alt","snowboard figure "+ newTricks[i].id)
                var newTitle = $('<h3></h3>')
                newTitle.html(newTricks[i].title)
                var newLink = $('<a></a>')
                newLink.attr("href","/trick/"+newTricks[i].slug+"/"+newTricks[i].id)
                newLink.html("Voir la figure")
                newDiv.append(newImg)
                newDiv.append(newTitle)
                newDiv.append(newLink)
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