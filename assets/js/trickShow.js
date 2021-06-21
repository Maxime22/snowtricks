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
                var newDiv = $('<div class="col-md-12 mt-3 commentContainer"></div>')
                var newRow = $('<div class="row"></div>')

                var newImgContainer = $('<div class="col-md-2 mb-3 text-center"></div>')
                var newImg = $('<img class="commentAuthorPhoto">')
                if(newComments[i].author.photo == "avatar.jpeg"){
                    newImg.attr("src", "/images/users/"+ newComments[i].author.photo)
                }else{
                    newImg.attr("src", "/images/users/uploads/"+ newComments[i].author.photo)
                }
                newImg.attr("alt","Photo de l'auteur "+ newComments[i].id)

                var newCol10TitleAndContentContainer = $('<div class="col-md-10"></div>')
                var newTitleAndContentContainer = $('<div class="titleAndContentCommentContainer"></div>')
                var newAddedByAndDate = $('<div class="fst-italic"></div>')
                var d = new Date(newComments[i].createdAt)
                var year = d.getFullYear() 
                var month = ('0' + d.getMonth()).slice(-2)
                var day = ('0' + d.getDay()).slice(-2)
                newAddedByAndDate.html('Ajouté le '+ day+'/'+ month +'/'+ year  +' par '+ newComments[i].author.username +' :')
                var newTitle = $('<div class="fw-bold"></div>')
                newTitle.html(newComments[i].title)

                var newContent = $('<div></div>')
                newContent.html(newComments[i].content)
                
                newImgContainer.append(newImg)
                newTitleAndContentContainer.append(newTitle)
                newTitleAndContentContainer.append(newAddedByAndDate)
                newTitleAndContentContainer.append(newContent)
                newCol10TitleAndContentContainer.append(newTitleAndContentContainer)
                newRow.append(newImgContainer)
                newRow.append(newCol10TitleAndContentContainer)
                newDiv.append(newRow)
                
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