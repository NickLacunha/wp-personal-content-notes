jQuery(document).ready(function(){
    // make the ajax call when the form was submitted
    jQuery('#personal_notes_form').submit(function(e){
        var post_data = {
            'user': pnConfig.user,
            'video': pnConfig.video,
            'notes': jQuery("textarea[name='user-notes']").val(),
            'security': pnConfig.security
        };
        
        jQuery.post(
            pnConfig.ajaxurl,
            post_data);
        // don't reload the page
        e.preventDefault();
    });
});