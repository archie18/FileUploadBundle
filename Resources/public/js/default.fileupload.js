/* 
 * JavaScript for jQuery-File-Upload integration.
 * 
 * @author Andreas Schueller <aschueller@bio.puc.cl>
 */

/**
 * jQuery File Upload configuration
 */
var maxFileSize = 5 * 1024 * 1024; // 5 MB
var fileUploadOptions = {
//    autoUpload: true,
//    url: url,
    dataType: 'json',
    maxFileSize: maxFileSize,
    acceptFileTypes: /(\.|\/)(pdf|png|jpg)$/i,
    maxNumberOfFiles: 1,
    messages: {
        maxFileSize: Translator.trans('jquery-fileupload.max_file_size', {size: maxFileSize / 1024 / 1024}),
        minFileSize: Translator.trans('jquery-fileupload.min_file_size'),
        acceptFileTypes: Translator.trans('jquery-fileupload.accept_file_types'),
        maxNumberOfFiles: Translator.trans('jquery-fileupload.max_number_of_files'),
        uploadedBytes: Translator.trans('jquery-fileupload.uploaded_bytes'),
        emptyResult: Translator.trans('jquery-fileupload.empty_result'),
        unknownError: Translator.trans('jquery-fileupload.unknown_error')
    },
    completed: function (e, data) {
        // Remove error box if all is good
        if (data.result.ok) {
            $(this).removeClass('form-error');
            $(this).find('ul').remove();
        }
                
        // Update status icon
        if (data.result.ok) {
            $(e.target).find('.status-menu-icon').removeClass('status-nope').addClass('status-ok');
        }

        // Show "Download | Replace file" div and update download link
        if (data.result.ok) {
            $(this).find('.file-download-link').prop('href', data.result.files[0].url);
            if (!$(this).find('.file-download-link').is(':visible')) {
                $(this).find('.file-download-link').parent().slideDown(200);
            }
        }

        // Add entity ID to form action in case of newly created entity
        if (data.result.ok && data.result.formAction) {
            $(this).parents('form').prop('action', data.result.formAction);
        }
    },
    formData: function (form) {
        return form.find('input[id$=__token]').serializeArray(); // Only include CRSF token, ignore other form fields
    }
}

$(document).ready(function() {

    // Bind MeloLabBioGestionFileUpload widget
    $(function () {
        'use strict';
        $('.fileupload-anchor').fileupload(fileUploadOptions);
        
        // Dynamically add mapping info to action URL
        $(document).on('fileuploadsubmit', function (e, data) {
//            console.log(e);
//            console.log(data);
            var mapping = data.fileInput.data('mapping');
//            console.log(mapping);
            if (!mapping) {
                console.log('ERROR: The file input element requires a \'data-mapping\' attribute.');
            } else {
                var action = data.form.attr('action');
                var sep = (action.indexOf('?') > -1 ? '&' : '?');
                data.url = action + sep + 'mapping=' + mapping; // Set action URL of the upload widget
            }
//            console.log(data.url);
        });
    });   
});