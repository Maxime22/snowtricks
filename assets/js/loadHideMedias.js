import $ from 'jquery';

$("#loadMedia").on("click", function (e) {
    e.preventDefault();
    $("#loadMedia").removeClass("d-block");
    $("#loadMedia").removeClass("d-lg-none");
    $("#loadMedia").addClass("d-none");
    $("#hideMedia").removeClass("d-none");
    $("#hideMedia").addClass("d-block");
    $("#hideMedia").addClass("d-lg-none");
    $(".showMedias").removeClass("d-none");
    
})

$("#hideMedia").on("click", function (e) {
    e.preventDefault();
    $("#loadMedia").addClass("d-block");
    $("#loadMedia").addClass("d-lg-none");
    $("#loadMedia").removeClass("d-none");
    $("#hideMedia").addClass("d-none");
    $("#hideMedia").removeClass("d-block");
    $("#hideMedia").removeClass("d-lg-none");
    $(".showMedias").addClass("d-none");
})