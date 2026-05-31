jQuery(document).ready(function($) {
    var fileInput = $('#errplugin_user_avatar_for_woo #user_avatar');
    
    var fileNameDisplay = $('#errplugin_user_avatar_for_woo .selected-file-name');

    fileInput.on('change', function(event) {
        var fileName = event.target.files[0] ? event.target.files[0].name : '';
        
        if (fileName) {
            fileNameDisplay.text(errAvatarL10n.selectedText + fileName);
        } else {
            fileNameDisplay.text('');
        }
    });
});