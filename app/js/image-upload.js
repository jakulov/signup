/**
 * @param input
 * @param uploadPath
 * @param options
 * @constructor
 */
var ImageUpload = function(input, uploadPath, options)
{
    this.input = $(input);
    this.fileInput = null;
    this.uploadButton = null;
    this.uploadPath = uploadPath;
    this.uploadFileName = 'file';
    this.maxFileSize = 10;
    this.uploadLabel = 'Choose image';
    this.maxUploadAlert = 'Max upload size is ';
    this.processMessage = 'Uploading...';

    var self = this;

    this.init = function() {
        self.input.hide();
        var val = self.input.val();
        if(val) {
            self.uploadSuccess(val, '/upload/' + val);
        }
        self.fileInput = $('<input type="file" style="display: none">');
        self.fileInput.on('change', function() {
            var file = self.fileInput.get(0).files;
            file = (file.length) ? file[0] : null;
            if(file) {
                var size = file.size / 100000;
                console.log(size);
                if(size > self.maxFileSize) {
                    self.uploadFail(self.maxUploadAlert + self.maxFileSize + 'Mb');
                }
                else {
                    self.processUpload(file);
                }
            }
            else {
                self.uploadCancelled();
            }
        });
        self.fileInput.insertAfter(self.input);
        self.uploadButton = $('<button type="button" class="btn btn-default"><i class="glyphicon glyphicon-upload"></i> '+ self.uploadLabel +'</button>');
        self.uploadButton.on('click', function() {
            self.fileInput.trigger('click');
        });
        self.uploadButton.insertAfter(self.input);

    };

    this.processUpload = function(file) {
        var formData = new FormData();
        formData.append(self.uploadFileName, file);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', self.uploadPath, true);
        xhr.send(formData);
        this.uploadWaiting();
        xhr.onreadystatechange = function(e) {
            if (xhr.readyState == 4) {
                var json = $.parseJSON(xhr.responseText);
                if (xhr.status != 200 || (json && json.ok == 0)) {
                    self.uploadFail(json ? json.error : 'Upload error');
                }
                else {
                    self.uploadSuccess(json.file, json.url);
                }
            }
        }
    };

    this.uploadWaiting = function() {
        self.getInputContainer().addClass('has-warning');
        self.getInputHelp().show().text(this.processMessage);
    };

    this.uploadSuccess = function(file, url) {
        self.input.val(file);
        self.getInputContainer().addClass('has-success');
        self.getInputHelp().show().html('<img src="'+ url +'" style="max-width: 100px; max-height: 100px;">');
    };

    this.uploadFail = function(error) {
        self.input.val('');
        self.getInputContainer().addClass('has-error');
        self.getInputHelp().show().text(error);
    };

    this.uploadCancelled = function() {
        self.input.val('');
        self.getInputContainer();
        self.getInputHelp().show().text('');
    };

    this.getInputContainer = function() {
        return self.input.closest('.form-group').removeClass('has-warning').removeClass('has-error').removeClass('has-success');
    };

    this.getInputHelp = function() {
        return self.input.prev('.help-block');
    };

    this.setOptions = function(options) {
        if(options.uploadFileName) {
            self.uploadFileName = options.uploadFileName;
        }
        if(options.maxFileSize) {
            self.maxFileSize = options.maxFileSize;
        }
        if(options.uploadLabel) {
            self.uploadLabel = options.uploadLabel;
        }
        if(options.maxUploadAlert) {
            this.maxUploadAlert = options.maxUploadAlert;
        }
        if(options.processMessage) {
            this.processMessage = options.processMessage;
        }
    };

    this.setOptions(options);
    this.init();
};