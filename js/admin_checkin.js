jQuery(function ($) {
  "use strict";
  $('.eventer-checkin-select-date').appendDtpicker({
    //"inline": true, //This will allow datepicker to always open
    "dateOnly": true,
    "closeOnSelected": true,
    "firstDayOfWeek": dynamicval.week_start
  });

  function eventer_checkin_process_ticket() {
    var event_id = $('.eventer-checkin-event').val();
    var event_date = $('.eventer-checkin-select-date').val();
    var ticket_id = $('.eventer-checkin-scan-here').val();
    var request = $.ajax({
      url: checkin.ajax_url,
      type: "post",
      data: {
        action: 'eventer_checkin_process_ticket',
        event: event_id,
        date: event_date,
        ticket: ticket_id,
      },
      beforeSend: function (xhr) {
        $('#postbox-container-2').find('.main').text('Loading...');
      },
    });
    request.done(function (response) {
      $('.eventer-checkin-info-message').text(response.msg);
      $('.eventer-checkin-scan-here').val('');
      $('.eventer-checkin-scan-here').focus();
      $('#postbox-container-2').find('.main').html(response.ticket);
    });

  }
  $(document).ready(function () {
    $(document).on('change', '.eventer-checkin-scan-here', function () { //can also use input or paste
      eventer_checkin_process_ticket();
    });
    $(document).on('click', '.eventer-checkin-submit', function () {
      eventer_checkin_process_ticket();
    });
  });
});
