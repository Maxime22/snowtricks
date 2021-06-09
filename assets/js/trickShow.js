import $, { htmlPrefilter } from 'jquery';

var commentsContainer = $('.commentsContainer');

var offset = 5;
$('#getMoreComments').on("click",function () {
    $.ajax({
        url: "/getMoreComments",
        type: "POST",
        data: {
            'offset': offset
        },
        success: function (data) {
            var newComments = data.newComments
            for(var i=0;i<newComments.length;i++){
                var newDiv = $('<div class="col-md-12 mt-3"></div>')
                var newImg = $('<img class="commentAuthorPhoto">')
                newImg.attr("src", "/images/users/uploads/"+ newComments[i].author.photo)
                newImg.attr("alt","Photo de l'auteur "+ newComments[i].id)

                var newTitle = $('<div></div>')
                newTitle.html(newComments[i].title)

                var newContent = $('<div></div>')
                newContent.html(newComments[i].content)
                
                newDiv.append(newImg)
                newDiv.append(newTitle)
                newDiv.append(newContent)
                
                commentsContainer.append(newDiv)
            }
            if(newComments.length < 10){
                $('#getMoreComments').hide();
            }
            offset += 5;
        },
        error: function () { // nothing
        }
    })
})