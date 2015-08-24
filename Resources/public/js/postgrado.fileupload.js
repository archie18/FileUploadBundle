/* 
 * JavaScript for jQuery-File-Upload integration.
 * 
 * @author Andreas Schueller <aschueller@bio.puc.cl>
 */

/**
 * jQuery File Upload configuration
 */
var maxFileSize = 5 * 1024 * 1024; // 5 MB default, should be overwritten by config
var acceptedFileTypes = /(\.|\/)(pdf)$/i; // PDF default, should be overwritten by config
//function configureBioGestionFileUpload(maxFileSize, acceptedFileTypes) {
//    var fileUploadOptions = {
function FileUploadOptions () {
        this.autoUpload = true;
    //    url: url,
        this.dataType = 'json';
        this.maxFileSize = maxFileSize;
        this.acceptFileTypes = acceptedFileTypes;
        this.maxNumberOfFiles = 1;
        this.limitConcurrentUploads = 1;
        this.messages = {
            maxFileSize: Translator.trans('jquery-fileupload.max_file_size', {size: maxFileSize / 1024 / 1024}),
            minFileSize: Translator.trans('jquery-fileupload.min_file_size'),
            acceptFileTypes: Translator.trans('jquery-fileupload.accept_file_types'),
            maxNumberOfFiles: Translator.trans('jquery-fileupload.max_number_of_files'),
            uploadedBytes: Translator.trans('jquery-fileupload.uploaded_bytes'),
            emptyResult: Translator.trans('jquery-fileupload.empty_result'),
            unknownError: Translator.trans('jquery-fileupload.unknown_error')
        };
        this.completed = function (e, data) {
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
                    toggleFileReplaceLinkIcon($(this).find('.file-replace-link'));
                }
            }

            // Add entity ID to form action in case of newly created entity
            if (data.result.ok && data.result.formAction) {
                $(this).parents('form').prop('action', data.result.formAction);
            }
        };
        this.formData = function (form) {
            return form.find('input[id$=__token]').serializeArray(); // Only include CRSF token, ignore other form fields
        };
        // The add callback is invoked as soon as files are added to the fileupload
        // widget (via file input selection, drag & drop or add API call).
        // See the basic file upload widget for more information:
        this.add = function (e, data) {
            if (e.isDefaultPrevented()) {
                return false;
            }
            var $this = $(this),
                that = $this.data('blueimp-fileupload') ||
                    $this.data('fileupload'),
                options = that.options;
            $this.find('.global-error').text(''); // Remove any previous error messages
            data.context = that._renderUpload(data.files)
                .data('data', data)
                .addClass('processing');
            options.filesContainer.empty(); // Empty files container. We only allow single file uploads
            options.filesContainer[
                options.prependFiles ? 'prepend' : 'append'
            ](data.context);
            console.log(options);
            that._forceReflow(data.context);
            that._transition(data.context);
            data.process(function () {
                return $this.fileupload('process', data);
            }).always(function () {
                data.context.each(function (index) {
                    $(this).find('.size').text(
                        that._formatFileSize(data.files[index].size)
                    );
                }).removeClass('processing');
                that._renderPreviews(data);
            }).done(function () {
                data.context.find('.start').prop('disabled', false);
                if ((that._trigger('added', e, data) !== false) &&
                        (options.autoUpload || data.autoUpload) &&
                        data.autoUpload !== false) {
                    data.submit();
                }
            }).fail(function () {
                if (data.files.error) {
                    data.context.each(function (index) {
                        var error = data.files[index].error;
                        if (error) {
    //                        $(this).find('.error').text(error);
                            $this.find('.global-error').text(error);
                        }
                    });
                }
            });
        };
}

function bindBioGestionFileUpload() {
    $(function () {
        'use strict';
        $('.fileupload-anchor').fileupload(new FileUploadOptions());
        
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
}

/**
 * Toggle file replace link icon. The element is expected to be the clicked link
 * @param {type} elem
 * @returns {undefined}
 */
function toggleFileReplaceLinkIcon(elem) {
        $(elem).find('span').toggleClass('mdi-menu-down').toggleClass('mdi-menu-up');
}

$(document).ready(function() {

    // Bind MeloLabBioGestionFileUpload widget
    bindBioGestionFileUpload();
    
    // Hide global error messages
    $(document).on('click', '.cancel', function (e, data) {
        $('.global-error').text('');
    });
    
    // Show/hide file replace dialog
    $(document).on('click', '.file-replace-link', function (e, data) {
        $(this).parent().nextAll('.file-input').toggle(200);
        toggleFileReplaceLinkIcon(this);
    });
});