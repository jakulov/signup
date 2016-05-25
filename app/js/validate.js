/**
 * Form validator
 * @param form
 * @param rules
 * @param messages
 * @constructor
 */
var Validate = function(form, rules, messages)
{
    this.messages = messages;
    this.form = $(form);
    var self = this;

    this.init = function(rules) {
        for(var k in rules) {
            if(rules.hasOwnProperty(k)) {
                var filters = rules[k];
                if(!Array.isArray(filters)) {
                    filters = [filters];
                }
                for(var i = 0; i < filters.length; i++) {
                    var filter = filters[i];
                    if(typeof filter === 'function') {
                        self.addHandler(k, filter);
                    }
                    else {
                        self.addHandler(k, self[filter]);
                    }
                }
            }
        }

        this.form.on('submit', function() {
            self.form.find('input').each(function () {
                $(this).trigger('blur');
            });
            if(self.form.find('.has-error').length) {
                return false;
            }

            return true;
        });
    };

    this.addHandler = function(field, callback) {
        self.getField(field).on('blur', callback);
    };

    this.getField = function(field) {
        return self.form.find('[name="' + field + '"]');
    };

    this.showError = function(field, message) {
        var text = message;
        if(Array.isArray(message)) {
            text = message.join(', ');
        }
        self.getField(field).next('.help-block').show().text(text);
        self.getField(field).closest('.form-group').addClass('has-error')
    };

    this.hideError = function(field) {
        self.getField(field).next('.help-block').hide().text('');
        self.getField(field).closest('.form-group').removeClass('has-error').addClass('has-success');
    };

    this.filterNotEmpty = function(field) {
        field = $(field.currentTarget);
        var val = $(field).val().replace(' ', '');
        if(val === '') {
            self.showError($(field).attr('name'), self.messages['FIELD_IS_REQUIRED']);
        }
        else {
            self.hideError($(field).attr('name'));
        }
    };

    this.filterValidEmail = function (field) {
        field = $(field.currentTarget);
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        var email = $(field).val();
        if(!re.test(email)) {
            self.showError($(field).attr('name'), self.messages['NOT_VALID_EMAIL']);
        }
        else {
            self.hideError($(field).attr('name'));
        }
    };

    self.init(rules);
};