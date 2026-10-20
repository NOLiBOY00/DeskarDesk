var $jq = jQuery.noConflict();

$(document).ready(function () {

    $(".delete-service").click(function (e) {
        if (!confirm("Are you sure you want to delete this service?")) {
            e.preventDefault();
        }

    });

});