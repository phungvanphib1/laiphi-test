(function ($, pf_config) {

    $(document).ready(function($) {
      $.fn.valid = function() {
        return ! this[0] ? true : this[0].validity.valid;
      };

      // Development
      // var API = 'http://localhost:3000/api/v3/trial_domains';

      // Production
      var API = 'https://www.printfriendly.com/api/v3/trial_domains';

      var $pro = $('.pf-pro');
      var $email = $('#pf-pro-email');
      var $emailError = $('#pf-pro-email-error');
      var $domain = $('#pf-pro-domain');
      var $domainError = $('#pf-pro-domain-error');
      var $statusHeader = $('#pf-pro-status-header');
      var $statusMessage = $('#pf-pro-status-message');
      var $activate = $('#pf-pro-activate');
      var $activateBtn = $('#pf-pro-activate-btn');
      var $buy = $('#pf-pro-buy');
      var $details = $('#pf-pro-details');
      var $buyBtn = $('#pf-pro-buy-btn');
      var $communicationError = $('#pf-pro-communication-error');
      var $communicationErrorMessage = $('#pf-pro-communication-error-message');
      var $loading = $('#pf-pro-loading');
      var $loadingMessage = $('#pf-pro-loading-message');

      setupEvents();
      validate();
      checkStatus();

      function setupEvents() {
        $email.on('input', validate);
        $domain.on('input', validate);
        $activateBtn.click(activate);
        $buyBtn.click(buy);
      }

      function validate() {
        $emailError.toggle(!$email.valid());
        $domainError.toggle(!$domain.valid());
        $activateBtn.prop('disabled', !$email.valid() || !$domain.valid());
        displayStatus('none');
      }

      function checkStatus() {
        if ($email.valid() && $domain.valid()) {
          showLoading(pf_config.i10n.check_status);
          $.getJSON(API + '/status', { domain: $domain.val() })
            .done(handleResponse)
            .fail(handleError)
            .always(hideLoading);
        }
      }

      function activate() {
        if ($email.valid() && $domain.valid()) {
          showLoading(pf_config.i10n.activation);
          $.post(API, { email: $email.val(), domain: $domain.val() })
            .done(handleResponse)
            .fail(handleError)
            .always(hideLoading);
        }
      }

      function buy() {
        var url = 'https://www.printfriendly.com/button/pro?domain=' + $domain.val();
        window.open(url,'_blank');
      }

      function handleResponse(data) {
        $communicationError.hide();
        displayStatus(data.status, data.message);
        saveOptions();
      }

      function handleError(jqxhr, textStatus, error) {
        var message = '';

        if (jqxhr && jqxhr.responseJSON && jqxhr.responseJSON.errors) {
          var errors = jqxhr.responseJSON.errors;

          for(var prop in errors) {
            var item = errors[prop];
            message += prop + ': ' + item.join(', ') + "<br>";
          }
        } else {
          message = textStatus;

          if (error) {
            message = + ', ' + error;
          }

          message += '. ' + pf_config.i10n.connection;
        }

        $communicationErrorMessage.html(message);
        $communicationError.show();
      }

      function showLoading(message) {
        $loading.show();
        $loadingMessage.text(message);
      }

      function hideLoading() {
        $loading.hide();
      }

      function displayStatus(status, message) {
        $statusHeader.removeClass('pf-text-success pf-text-warning pf-text-error');
        $pro.removeClass('pf-notice-success pf-notice-warning pf-notice-error');
        $statusMessage.text(message);

        $activate.hide();
        $buy.hide();
        $details.hide();

        switch(status) {
          case 'none':
            $activate.show();
            $pro.addClass('pf-notice-warning');
            $statusHeader.text(pf_config.i10n.activate).addClass('pf-text-warning');
            $('.password_protected_yes').attr('data-sub-status', 'none');
            break;
          case 'trial':
            $buy.show();
            $pro.addClass('pf-notice-success');
            $statusHeader.text(pf_config.i10n.active_trial).addClass('pf-text-success');
            $('.password_protected_yes').attr('data-sub-status', 'trial');
            break;
          case 'pro':
            $details.show();
            $pro.addClass('pf-notice-success');
            $statusHeader.text(pf_config.i10n.active).addClass('pf-text-success');
            $('.password_protected_yes').attr('data-sub-status', 'pro');
            break;
          case 'expired':
            $buy.show();
            $pro.addClass('pf-notice-error');
            $statusHeader.text(pf_config.i10n.expired).addClass('pf-text-error');
            $('.password_protected_yes').attr('data-sub-status', 'expired');
            break;
        }
        $('#password_protected').change();
        $('#license_status').val(status);
        $('#license_date').val(new Date().getTime());
      }

      function saveOptions() {
        var formData =  $('#pf-options-form').serialize();
        $.post('options.php', formData).error(handleError);
      }
    });

})(jQuery, pf_config);