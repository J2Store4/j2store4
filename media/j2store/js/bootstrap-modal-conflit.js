if(typeof(j2store) == 'undefined') {
    var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
    j2store.jQuery = jQuery.noConflict();
}

if(typeof(j2storeURL) == 'undefined') {
    var j2storeURL = '';
}
(function ($) {
    // Make sure the DOM elements are loaded and accounted for
    $(document).ready(function() {

        // Match to Bootstraps data-toggle for the modal
        // and attach an onclick event handler
        $('a[data-toggle="modal"]').on('click', function(e) {

            // From the clicked element, get the data-target arrtibute
            // which BS3 uses to determine the target modal
            var target_modal = $(e.currentTarget).data('target');
            // external url content load to modal body
            var method = 1;
            if(target_modal == undefined){
                // old href model id
                method = 2;
            }
            // also get the remote content's URL
            var remote_content = e.currentTarget.href;
            // Find the target modal in the DOM
            var modal = $(target_modal);
            // Find the modal's <div class="modal-body"> so we can populate it
            var modalBody = $(target_modal + ' .modal-body');

            // Capture BS3's show.bs.modal which is fires
            // immediately when, you guessed it, the show instance method
            // for the modal is called
            if(method == 1){
                modal.on('show.bs.modal', function () {
                    if(remote_content){
                        // use your remote content URL to load the modal body

                        modalBody.load(remote_content);


                    }
                }).modal();
            }else{
                return true;
            }

            // and show the modal

            // Now return a false (negating the link action) to prevent Bootstrap's JS 3.1.1
            // from throwing a 'preventDefault' error due to us overriding the anchor usage.
            return false;
        });
    });
})(j2store.jQuery);
