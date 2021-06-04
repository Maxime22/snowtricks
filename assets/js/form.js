import $ from 'jquery';

function readURL(input,idImg) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function (e) {
            document.querySelector('#'+idImg).setAttribute('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.querySelector('#trick_mainImg').addEventListener('change',function(){
    readURL(this,'trick_mainImg_img');
});

//when doc is ready
$(function () {

    var listPhotos = $($('#photo_field_list'));
    var listVideos = $($('#video_field_list'));

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

        var container = list.children()
        if(ulType === "Photo"){
            var customFiles = container.children('.form-group').children('div').clone()
        }

        if(ulType === "Video"){
            var customFiles = container.children('.form-group').children('input').clone()
        }

        customFiles.each(function( index ) {
            var newElem = $('<li id=liNumber'+index+ulType+'></li>')
            // each this is a div with the class custom-file
            newElem.append($(this))
            var deleteButton = '<button type="button" class="delete'+ulType+' btn btn-danger mt-2" data-selector="'+ index + ulType +'" >Delete</button>'
            
            // for preview
            if(ulType === "Photo"){
                var newImg = $('<img id="trick_img_'+index+ulType+'" src="/images/tricks/uploads/'+arrayPhotos[index]+'"></img>')
                newElem.append(newImg);
            }

            newElem.append(deleteButton);
            newElem.appendTo(container);

            // for preview, we need to create addEventListener after we create the element in the DOM
            if(ulType === "Photo"){
                $(this).children('.form-group').children('.custom-file').children('input').on('change',function(){
                    readURL(this,'trick_img_'+index+ulType);
                });
            }
          });
          
        container.children('.form-group').remove();
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
        var newElem = $('<li id=liNumber'+counter+ulType+'></li>').html(newWidget);

        // for preview
        if(ulType === "Photo"){
            var newImg = $('<img id="trick_img_'+counter+ulType+'" src=""></img>')
            newElem.append(newImg);
        }
        
        // Delete button
        var deleteButton = '<button type="button" class="delete'+ulType+' btn btn-danger mt-2" data-selector="'+ counter + ulType +'" >Delete</button>'
        newElem.append(deleteButton);

        // we push the element to the DOM
        newElem.appendTo(container);

        // for preview, needs to be after we create the element in the DOM
        if(ulType === "Photo"){
            var previewCounter = list.data('widget-counter');
            document.querySelector('#trick_images_'+previewCounter+' .form-group .custom-file input').addEventListener('change',function(){
                readURL(this,'trick_img_'+previewCounter+ulType);
            });
        }

        // Increase the counter
        counter++;

        // And store it, the length cannot be used if deleting widgets is allowed
        list.data('widget-counter', counter);
    });

    // DELETE
    $('#photo_field_list').on("click", "button.deletePhoto", function(){
        var selector = $(this).data('selector');
        var elementToDelete = "li#liNumber"+selector
        $(elementToDelete).remove();
    });

    $('#video_field_list').on("click", "button.deleteVideo", function(){
        var selector = $(this).data('selector');
        var elementToDelete = "li#liNumber"+selector
        $(elementToDelete).remove();
    });
});