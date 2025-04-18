// table id for in sigle page extra table
$(document).ready(function() {
    $('#example').DataTable();
});
$(document).ready(function() {
    $('#examplee').DataTable();
});
$(document).ready(function() {
    $('#case').DataTable();
});
$(document).ready(function() {
    $('#casecompany').DataTable();
});
$(document).ready(function() {
    $('#bank').DataTable();
});

$(document).ready(function() {
    $('#transcton-sale').DataTable();
});
$(document).ready(function() {
    $('#transcton-sale2').DataTable();
});
// Navbar Mobile
$('.navbar-menu-toggler').on('click', function () {
    $('.sidebar-overlay').addClass('sidebar-overlay-show');
    $('.left-sidebar').addClass('mobile-sidebar-show');
    $('body').addClass('overflow-hidden');
});
 
$('.sidebar-overlay').on('click', function () {
    $('.left-sidebar').removeClass('mobile-sidebar-show');
    $('body').removeClass('overflow-hidden');
    window.setTimeout(function () {
        $('.sidebar-overlay').removeClass('sidebar-overlay-show');
    }, 40);
});


// other-detail-show

$(document).ready(function () {
    // Toggle visibility on button click
    $('#toggleButton').on('click', function () {
        $('.other-details-show').show();
    });
    // $('#toggleButton').on('click', function () {
    //     $('.other-details-show').hide();
    // });
});