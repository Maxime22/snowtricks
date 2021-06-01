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