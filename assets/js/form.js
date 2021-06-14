import $ from 'jquery';

function readURL(input,idImg) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function (e) {
            document.querySelector('#'+idImg).setAttribute('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
        if(idImg == 'trick_mainImg_img'){
            $("#mainImgSrcData").val("0")
        }
    }
}

if(document.querySelector('#trick_mainImg')){
    document.querySelector('#trick_mainImg').addEventListener('change',function(){
        readURL(this,'trick_mainImg_img');
    });
}
if(document.querySelector('#profile_photoFile')){
    document.querySelector('#profile_photoFile').addEventListener('change',function(){
        readURL(this,'user_photo_img');
    });
}

$('.deleteMainImg').on("click", function(){
    // if i put something in the src, the data is no longer loading in src in symfony form, so i put "" in src to let in work
    $('.mainImgForm').attr("src","")
    $("#mainImgSrcData").val("1")
})

//when doc is ready
$(function () {

    var listPhotos = $($('#photo_field_list'));
    var listVideos = $($('#video_field_list'));

    $('#trick_images').attr("class","row")
    $('#trick_videos').attr("class","row")

    // edit
    // list.children() is div#trick_photosFiles or 
    // list.children().children() are the children of div#trick_photosFiles
    if(listPhotos.children().children().length > 0){
        fillList(listPhotos, "Photo")
    }
    if(listVideos.children().children().length > 0){
        fillList(listVideos, "Video")
    }

    function fillList(list, ulType){
        // for preview
        var arrayPhotos = list.data('array-photos')
        var customFiles = null

        var container = list.children()
        if(ulType === "Photo" && arrayPhotos){
            customFiles = container.children('.mb-3').children('div').clone()
        }

        if(ulType === "Video"){
            var customVideos = container.children().children('input')
        }

        if (customFiles){
            customFiles.each(function( index ) {
                var newElem = $('<div id=elementNumber'+index+ulType+' class="col-4 mb-5"></div>')
                // each this is a div with the class custom-file
                newElem.append($(this))
                var deleteButton = '<a class="delete'+ulType+' mt-2" data-selector="'+ index + ulType +'" ><i class="fas fa-trash-alt ms-3 text-danger"></i></a>'
                
                // for preview
                if(ulType === "Photo"){
                    var newImg = $('<img id="trick_img_'+index+ulType+'" class="imgAddedInForm" src="/images/tricks/uploads/'+arrayPhotos[index]+'"></img>')
                    newElem.append(newImg);
                }

                newElem.append(deleteButton);
                newElem.appendTo(container);
                
                // for preview, we need to create addEventListener after we create the element in the DOM
                if(ulType === "Photo"){
                    $(this).children('.mb-3').children('input').on('change',function(){
                        readURL(this,'trick_img_'+index+ulType);
                    });
                }
            });
        }

        if(customVideos){
            customVideos.each(function( index ) {
                $(this).parent().attr( "id", "elementNumber"+index+ulType );
                $(this).parent().attr( "class", "col-4 mb-5" );
                var deleteButton = '<a class="delete'+ulType+' mt-2" data-selector="'+ index + ulType +'" ><i class="fas fa-trash-alt ms-3 text-danger"></i></a>'
                $(this).after(deleteButton);
            })
        }
        
        if(ulType === "Photo"){
            container.children('.mb-3').remove();
        }
    }

    // add
    $('.add-another-collection-widget').on("click",function (e) {
        var list = $($(this).attr('data-list-selector'));
        var container = list.children()

        var ulType = list.data('ul-type');

        // Try to find the counter of the list or use the length of the list
        var counter = list.data('widget-counter');

        // grab the prototype template
        var newWidget = list.attr('data-prototype');
        // replace the "__name__" used in the id and name of the prototype
        newWidget = newWidget.replace(/__name__/g, counter);
        
        // create a new list element and add it to the list
        var newElem = $('<div id=elementNumber'+counter+ulType+' class="col-4 mb-5"></div>').html(newWidget);

        // for preview
        if(ulType === "Photo"){
            var newImg = $('<img id="trick_img_'+counter+ulType+'" class="imgAddedInForm" src=""></img>')
            newElem.append(newImg);
        }
        
        // Delete button
        var deleteButton = '<a class="delete'+ulType+' mt-2" data-selector="'+ counter + ulType +'" ><i class="fas fa-trash-alt ms-3 text-danger"></i></a>'
        newElem.append(deleteButton);

        // we push the element to the DOM
        newElem.appendTo(container);

        // Increase the counter
        counter++;

        // And store it, the length cannot be used if deleting widgets is allowed
        list.data('widget-counter', counter);

        // for preview, needs to be after we create the element in the DOM
        if(ulType === "Photo"){
            var previewCounter = counter - 1;
            document.querySelector('#trick_images_'+previewCounter+' .mb-3 input').addEventListener('change',function(){
                readURL(this,'trick_img_'+previewCounter+ulType);
            });
        }
    });

    // DELETE
    $('#photo_field_list').on("click", "a.deletePhoto", function(){
        var selector = $(this).data('selector');
        var elementToDelete = "div#elementNumber"+selector
        $(elementToDelete).remove();
    });

    $('#video_field_list').on("click", "a.deleteVideo", function(){
        var selector = $(this).data('selector');
        var elementToDelete = "div#elementNumber"+selector
        $(elementToDelete).remove();
    });
});