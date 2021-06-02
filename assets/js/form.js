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
    $('.add-another-collection-widget').on("click",function (e) {
        var list = $($(this).attr('data-list-selector'));

        var ulType = list.data('ul-type');

        // Try to find the counter of the list or use the length of the list
        var counter = list.data('widget-counter') || list.children().length;

        // grab the prototype template
        var newWidget = list.attr('data-prototype');
        // replace the "__name__" used in the id and name of the prototype
        newWidget = newWidget.replace(/__name__/g, counter);
        // Increase the counter
        counter++;
        // And store it, the length cannot be used if deleting widgets is allowed
        list.data('widget-counter', counter);
        
        // create a new list element and add it to the list
        var newElem = $('<li id=liNumber'+counter+'></li>').html(newWidget);
        
        // Delete button
        var deleteButton = '<button type="button" class="delete'+ulType+' btn btn-danger mt-2" data-selector="'+ counter +'" >Delete</button>'
        newElem.append(deleteButton);

        newElem.appendTo(list);
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