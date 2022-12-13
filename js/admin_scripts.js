//https://github.com/mugifly/$-simple-datetimepicker
/*jshint sub:true*/
jQuery(function ($) {
  "use strict";
  var EVENTER = window.EVENTER || {};
  var time_format = (dynamicval.time_format == '12') ? true : false;
  $('#eventer_event_start_dt, #eventer_event_end_dt, .eventer_activation_date, .eventer-coupon-validity').appendDtpicker({
    //"inline": true, //This will allow datepicker to always open
    "amPmInTimeList": time_format,
    "minuteInterval": 15,
    "closeOnSelected": true,
    "firstDayOfWeek": parseInt(dynamicval.week_start)
  });
  $('#eventer_event_multiple_dt_inc, #eventer_event_multiple_dt_exc').multiDatesPicker({
    //altField: '#eventer_event_multiple_dt_show'
    dateFormat: "yy-mm-dd",
    minDate: 0, showButtonPanel: true, changeMonth: true, changeYear: true, onSelect: function (dateText, inst) { inst.settings.defaultDate = dateText; }
  });
  $('.eventer-shortcode-series').multiDatesPicker({
    //altField: '#eventer_event_multiple_dt_show'
    dateFormat: "yy-mm-dd",
    maxPicks: 2, showButtonPanel: true, changeMonth: true, changeYear: true, onSelect: function (dateText, inst) { inst.settings.defaultDate = dateText; }
  });
  $('.eventer-bookings-date').multiDatesPicker({
    //altField: '#eventer_event_multiple_dt_show'
    dateFormat: "yy-mm-dd",
    maxPicks: 1, showButtonPanel: true, changeMonth: true, changeYear: true, onSelect: function (dateText, inst) { inst.settings.defaultDate = dateText; }
  });
  //Google Map Venue Field
  if (dynamicval.gmap_api != '' && dynamicval.screen_tax == "eventer-venue") {
    google.maps.event.addDomListener(window, 'load', function () {
      var places = new google.maps.places.Autocomplete(document
        .getElementById('venue_address'));
    });
  }


  if ($('input#eventer_event_weekly_repeat').is(':checked')) {
    $('#eventer_event_day_month').prop('multiple', false);
  }
  $('#eventer_event_weekly_repeat').change(function () {
    if ($(this).is(":checked")) {
      $('#eventer_event_day_month').prop('multiple', false);
    }
    else {
      $('#eventer_event_day_month').prop('multiple', true);
    }
  });
  // Event Recurrence Box
  var eventer_event_frequency_type = $('#eventer_event_frequency_type');
  EVENTER.EVENTRECURREMCEDISPLAY = function () {
    var eventer_event_day_month = $('#eventer_event_day_month').closest('tr');
    var eventer_event_week_day = $('#eventer_event_week_day').closest('tr');
    var eventer_event_frequency = $('#eventer_event_frequency').closest('tr');
    var eventer_event_frequency_count = $('#eventer_event_frequency_count').closest('tr');
    var eventer_event_multiple_dt_exc = $('#eventer_event_multiple_dt_exc').closest('tr');
    var eventer_event_repeat_weekly = $('#eventer_event_weekly_repeat').closest('tr');
    if (eventer_event_frequency_type.val() === 'no') {
      eventer_event_day_month.hide();
      eventer_event_week_day.hide();
      eventer_event_frequency.hide();
      eventer_event_frequency_count.hide();
      eventer_event_multiple_dt_exc.hide();
      eventer_event_repeat_weekly.hide();
    }
    else if (eventer_event_frequency_type.val() === "1") {
      eventer_event_day_month.hide();
      eventer_event_week_day.hide();
      eventer_event_frequency.show();
      eventer_event_frequency_count.show();
      eventer_event_multiple_dt_exc.show();
      eventer_event_repeat_weekly.hide();
    }
    else {
      eventer_event_day_month.show();
      eventer_event_week_day.show();
      eventer_event_frequency.hide();
      eventer_event_frequency_count.show();
      eventer_event_multiple_dt_exc.show();
      eventer_event_repeat_weekly.show();
    }
  };
  eventer_event_frequency_type.change(function () {
    EVENTER.EVENTRECURREMCEDISPLAY();
  });

  // Event Registration Fields
  var eventer_event_registration_swtich = $('#eventer_event_registration_swtich');
  EVENTER.EVENTREGISTRATIONFIELDS = function () {
    var eventer_event_custom_registration_url = $('#eventer_event_custom_registration_url').closest('tr');
    var eventer_event_custom_registration_form = $('#eventer_event_registration_form').closest('tr');
    var eventer_event_registration_target = $('#eventer_event_registration_target').closest('tr');
    var eventer_event_offline_payment = $('#eventer_event_offline_payment').closest('tr');
    if (eventer_event_registration_swtich.val() === 'no') {
      eventer_event_custom_registration_url.hide();
      eventer_event_registration_target.hide();
      eventer_event_custom_registration_form.hide();
      $("#additional-services").hide();
      $("#eventer_event_schedule").hide();
      $("#eventer_event_schedule_datewise").hide();
    }
    else {
      eventer_event_custom_registration_url.show();
      eventer_event_registration_target.show();
      eventer_event_custom_registration_form.show();
      $("#additional-services").show();
      $("#eventer_event_schedule").show();
      $("#eventer_event_schedule_datewise").show();
    }
  };
  eventer_event_registration_swtich.change(function () {
    EVENTER.EVENTREGISTRATIONFIELDS();
  });
  var $frequency_type = $('#eventer_event_frequency_type').val();
  EVENTER.EVENTERMULTIDAYEVENT = function () {
    if ($('#eventer_event_start_dt').length <= 0) return true;
    var set_each_day = $('#eventer_event_each_day_time').closest('tr');
    var start_date = $("#eventer_event_start_dt").val().replace(/\-/g, '/');
    var end_date = $("#eventer_event_end_dt").val().replace(/\-/g, '/');
    var $start_date = new Date(start_date);
    var $end_date = new Date(end_date);
    var diff = new Date($end_date) - new Date($start_date);
    var diff_time = diff / (60 * 60 * 1000);
    /*if ($start_date.toDateString() === $end_date.toDateString()) {
      $("#eventer_event_frequency_type").val($frequency_type);
      eventRecurrenceDisplay();
      $("#eventer_event_frequency_type").prop("disabled", false);
      $("#recurring-msg").empty();
    // Same day - maybe different times
    }*/
    if (diff_time < 24) {
      $("#eventer_event_frequency_type").val($frequency_type);
      EVENTER.EVENTRECURREMCEDISPLAY();
      $("#eventer_event_frequency_type").prop("disabled", false);
      $("#recurring-msg").empty();
      set_each_day.hide();
      // Same day - maybe different times
    }
    else {
      $("#eventer_event_frequency_type").val('no');
      EVENTER.EVENTRECURREMCEDISPLAY();
      $("#eventer_event_frequency_type").prop("disabled", true);
      $("#recurring-msg").remove();
      $("#eventer_event_end_dt").before("<span id=\"recurring-msg\">" + dynamicval.multiplemsg + "</span>");
      set_each_day.show();
      // Different day
    }
  };

  var all_day_switch = $("#eventer_event_all_day");
  EVENTER.EVENTERALLDAYCHECKED = function () {
    var all_day_val = $("#eventer_event_all_day").is(':checked');
    if (all_day_val === true) {
      $("#eventer_event_end_dt").closest('tr').hide();
    }
    else {
      $("#eventer_event_end_dt").closest('tr').show();
    }
  };
  all_day_switch.change(function () {
    EVENTER.EVENTERALLDAYCHECKED();
  });

  /*$(".eventer_select_val").change(function(){
    var id = $(this).attr('id');
    var foo = [];
    $('#'+id+' :selected').each(function(i, selected)
    { 
        if($(selected).val()!=='')
        {
          $("#"+id).find("option").eq(0).hide();
          foo[i] = $(selected).val();
        }
    });
    if(foo.length === 0)
    {
      $("#"+id).find("option").eq(0).show();
    }
  });*/

  $(".generate-shortcode").click(function () {
    var shortcode_generate = $(this).attr("id");
    var attr = shortcode_generate;
    var element = $(this);
    var replaced_val = '';
    $(this).closest("table").find(".eventer_select_val").each(function () {
      if ($(this).is(':visible') || $(this).hasClass('eventer-carousel-field')) {
        var foo = [];
        var id = $(this).attr('id');
        var field_attr = $(this).attr('data-sattr');
        if ($(this).is('input:text')) {
          if (field_attr === "id") {
            replaced_val = $(this).val().replace(" ", "-");
            foo[0] = replaced_val;
          }
          else {
            foo[0] = $(this).val();
          }

        }
        else {
          $('#' + id + ' :selected').each(function (i, selected) {
            if ($(selected).val() !== '') {
              foo[i] = $(selected).val();
              $("#" + id + " option[value*='']").prop('disabled', true);
            }
          });
        }

        if (foo.length !== 0) {
          attr += ' ' + field_attr + '="' + foo + '"';
        }
      }

    });
    $(this).closest("tr").find("td").text("[" + attr + "]");
    var shortcode = $(this).closest("tr").find("td").text();
    $(this).closest("tr").find('.eventer-shortcode-val').val(shortcode);
    EVENTER.COPYSHORTCODE(shortcode);
    $('<p class="description">' + dynamicval.shortcode_copied + '</p>').appendTo($(this).closest("tr").find("td")).slideDown();
    setTimeout(function () {
      element.closest("tr").find("p").slideUp();
    }, 5000);
  });

  $(document).on('submit', '.eventer-shortcode-preview', function () {
    $(this).closest('tr').find(".generate-shortcode").trigger('click');
  });

  var list_view = $('#list_view');
  EVENTER.EVENTERSHOWLISTVIEW = function () {
    if (list_view.val() === '') {
      $("#minimal-design").hide();
      $("#compact-design").show();
    }
    else {
      $("#compact-design").hide();
      $("#minimal-design").show();
    }
  };


  EVENTER.EVENTERGETURLVARS = function () {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
      hash = hashes[i].split('=');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
    }
    return vars;
  };
  var post_id = EVENTER.EVENTERGETURLVARS()["post"];
  EVENTER.EVENTERPRIMARYVENUE = function (new_term) {
    var primary;
    if (typeof new_term !== "undefined") {
      primary = new_term;
    }
    else {
      primary = 'eventer-venue-' + dynamicval.eventer;
    }
    $("#eventer-venuechecklist li").each(function (i) {
      var checkbox_id = $(this).attr("id");
      if ($("#in-" + checkbox_id).is(':checked') && checkbox_id !== primary) {
        $(this).append('<span id="term-' + i + '" class="eventer_primary_venue" data-id="' + post_id + '">' + dynamicval.primary_btn + '</span>');
      }
    });
  };
  EVENTER.EVENTERUPDATEPRIMARYTERM = function (eventer_term_id, current_term, unchecked) {
    var post_id = EVENTER.EVENTERGETURLVARS()['post'];
    var uncheck;
    if (typeof unchecked !== "undefined") {
      uncheck = unchecked;
    }
    $.ajax({
      url: dynamicval.ajax_url,
      type: 'post',
      data: {
        action: 'eventer_make_event_primary_venue',
        post_id: post_id,
        term: eventer_term_id,
        nonce: dynamicval.venue_nonce,
        uncheck: uncheck
      },
      success: function (response) {
        if (response !== "") {
          $("#eventer-venuechecklist li span").remove();
          EVENTER.EVENTERPRIMARYVENUE(response);
          $("#" + current_term).remove();
        }
      }
    });
    return false;
  };
  EVENTER.EVENTERVENUEIMAGE = function () {
    if (jQuery('#eventer_venue_image').attr('src') === '') {
      jQuery('#eventer_venue_image').hide();
      jQuery('#eventer_venue_image_remove').hide();
    }
    jQuery(document).on('click', '#eventer_venue_image_remove', function () {
      jQuery('#eventer_venue_image').attr('src', '');
      jQuery('#venue_image_id').val('');
      jQuery('#eventer_venue_image').hide();
    });
    jQuery(document).on('click', '#eventer_upload_venue_image', function () {
      var fileFrame = wp.media.frames.file_frame = wp.media({
        multiple: false
      });
      fileFrame.on('select', function () {
        var attachment = fileFrame.state().get('selection').first().toJSON();
        var attachment_id = attachment.id;
        var attachment_url = attachment.url;
        jQuery('#venue_image_id').val(attachment_id);
        jQuery('#eventer_venue_image').show();
        jQuery('#eventer_venue_image_remove').show();
        jQuery('#eventer_venue_image').attr('src', attachment_url);
      });
      fileFrame.open();
    });
  };
  $(document).ready(function () {
    EVENTER.EVENTRECURREMCEDISPLAY();
    EVENTER.EVENTREGISTRATIONFIELDS();
    EVENTER.EVENTERMULTIDAYEVENT();
    EVENTER.EVENTERALLDAYCHECKED();
    EVENTER.EVENTERSHOWLISTVIEW();
    EVENTER.EVENTERGETURLVARS();
    EVENTER.EVENTERPRIMARYVENUE();
    EVENTER.EVENTERVENUEIMAGE();

    $(document).on('click', '.eventer-delete-bulk-booking', function (e) {
      if (confirm('Are you sure?')) {
        var bookings = [];
        $('.eventer-remove-bult-bookings').each(function () {
          if ($(this).is(':checked')) {
            bookings.push($(this).val());
          }
        });

        $.ajax({
          url: dynamicval.ajax_url,
          type: "post",
          dataType: 'json',
          data: {
            action: 'eventer_delete_bookings',
            bookings: bookings,
          },
          beforeSend: function (xhr) {
            //element.attr("disabled", true);
          },
          success: function (response) {

          },
          complete: function (result) {
            bookings.forEach(row => {
              $('tr#registrant-' + row).remove();
              $('tr#registrant-details-' + row).remove();
            });
          }
        });
      }
      e.preventDefault();
    });

    $(document).on('click', '.eventer-add-normal-ticket', function (e) {
      var ticket_row = $('.eventer-normal-ticket-row1').clone();
      var last_ticket = $('.eventer-normal-ticket-row1').attr('data-tickets');
      ticket_row.find('.meta_feat_title').val('');
      ticket_row.find('.meta_sch_title').val('');
      ticket_row.find('.eventer-ticket-identification').val('');
      ticket_row.removeClass('eventer-normal-ticket-row1');
      ticket_row.insertAfter('.eventer-normal-ticket-row' + last_ticket);
      e.preventDefault();
    });

    if ($('.eventer-general-tab-remember').length) {
      setTimeout(function () {
        $('[data-tab="' + $('.eventer-general-tab-remember').val() + '"]').trigger('click');
      }, 100);

    }

    $('.general-settings-tab').on('click', function () {
      $('.general-settings').hide();
      $('.general-settings-tab').removeClass('nav-tab-active');
      $(this).addClass('nav-tab-active');
      var target_div = $(this).attr('data-tab');
      $('.eventer-general-tab-remember').val(target_div);
      $(target_div).show();
    });
    /*
    Code for additional services meta box
    START
    */
    $('#add-row').on('click', function () {
      var row = $('.empty-row.screen-reader-text').clone(true);
      row.removeClass('empty-row screen-reader-text');
      row.insertAfter('#eventer_additional_services_fieldset tbody>tr.saved_services:last');
      return false;
    });

    $('.remove-row').on('click', function () {
      if ($('#eventer_additional_services_fieldset').find('.eventer-add-services').length > 2) {
        $(this).closest('tr.eventer-add-services').remove();
      }
      return false;
    });
    /*
    END
    */
    $(".choose_shortcode").change(function () {
      $('#counter-settings').animate({ "opacity": "hide", top: "100" }, 500);
      $('#list-settings').animate({ "opacity": "hide", top: "100" }, 500);
      $('#grid-settings').animate({ "opacity": "hide", top: "100" }, 500);
      $('#calendar-settings').animate({ "opacity": "hide", top: "100" }, 500);
      $('#field-settings').animate({ "opacity": "hide", top: "100" }, 500);
      $('#slider-settings').animate({ "opacity": "hide", top: "100" }, 500);
      $('#form_id').val(Math.floor((Math.random() * 10000000000000000) + 1));
      $('#form-settings').animate({ "opacity": "hide", top: "100" }, 500);
      $('#dashboard-settings').animate({ "opacity": "hide", top: "100" }, 500);
      $('#' + $(this).val() + '-settings').animate({ "opacity": "show", top: "150" }, 1000);
    });
    //$("td[colspan=2]").find("p, strong, button, select").hide();
    $("tr.eventer_woo_expandable").hide();
    $("tr.eventer-admin-registrant-details").click(function (event) {
      $("tr.eventer_woo_expandable").hide();
      event.stopPropagation();
      var $target = $(event.target);
      if ($target.closest("td").attr("colspan") > 1) {
        $target.slideDown();

      } else {
        if (($target.closest("tr")).hasClass("active")) {
          $target.closest("tr").removeClass("active");
        }
        else {
          $target.closest("tr").addClass("active");
        }
        $target.closest("tr").next("tr").toggle();
        //$target.closest("tr").next("tr").find("td").show();
      }
    });
    if (dynamicval.event_cat == 1) {
      $("#category_color").wpColorPicker();
      $(".eventer_default_color").wpColorPicker();
    }

    $("input[name='tax_input[eventer-venue][]']").change(function () {
      $("#eventer-venuechecklist li span").remove();
      if ($(this).is(':checked')) {
        EVENTER.EVENTERPRIMARYVENUE();
      }
      else {
        if ($(this).parent().parent().find(".eventer_primary_venue").length === 0) {
          EVENTER.EVENTERUPDATEPRIMARYTERM($(this).parent().parent().attr("id"), $(this).parent().parent().find("span").attr("id"), '1');
        }
        else {
          EVENTER.EVENTERPRIMARYVENUE();
        }
      }

    });
    var get_venue_length = $("#eventer-venuechecklist li").length;
    $("#eventer-venue-add-submit").click(function () {
      setTimeout(function () {
        if ($("#eventer-venuechecklist li").length > get_venue_length) {
          $("#eventer-venuechecklist li span").remove();
          EVENTER.EVENTERPRIMARYVENUE();
        }
      }, 2000);
    });
    $("#list_view").change(function () {
      EVENTER.EVENTERSHOWLISTVIEW();
    });
    $("#eventer_event_end_dt, #eventer_event_start_dt").on("change", function () {
      EVENTER.EVENTERMULTIDAYEVENT();
    });
  });

  $(document).on('click', '.eventer_primary_venue', function () {
    EVENTER.EVENTERUPDATEPRIMARYTERM($(this).parent().attr("id"), $(this).attr("id"));
  });

  $(".eventer-send-tickets-again").click(function () {
    var registrant_id = $(this).attr('data-registrantid');
    var nonce = $(this).attr('data-nonce');
    var element = $(this);
    request = $.ajax({
      url: dynamicval.ajax_url,
      type: "post",
      dataType: 'json',
      data: {
        action: 'eventer_send_tickets_again',
        id: registrant_id,
        nonce: nonce,
      },
      beforeSend: function (xhr) {
        element.attr("disabled", true);
      },
    });
    request.done(function (response, textStatus, jqXHR) {
      if (response == 1) {
        alert('Tickets successfully sent!');
      }
      else {
        alert('There is a problem sending tickets to the registrant.');
      }

      element.attr("disabled", false);
    });
    request.fail(function (jqXHR, textStatus, errorThrown) {
    });
  });

  $(".update_booking_status").click(function () {
    var prevRow = $(this).closest("tr").prev()[0];
    var element = $(this);
    var changed_status = $(this).parent().find("select").find(':selected').val();
    var registrant_status = $(prevRow).children('td').slice(4, 5).text();
    var registrant_id = $(this).attr('data-registrantid');
    var nonce = $(this).attr('data-nonce');
    request = $.ajax({
      url: dynamicval.ajax_url,
      type: "post",
      dataType: 'json',
      data: {
        action: 'eventer_update_registrant_status',
        id: registrant_id,
        status: changed_status,
        nonce: nonce,
      },
      beforeSend: function (xhr) {
        element.attr("disabled", true);
      },
    });
    request.done(function (response, textStatus, jqXHR) {
      $(prevRow).children('td').slice(4, 5).text(changed_status);
      element.attr("disabled", false);
    });
    request.fail(function (jqXHR, textStatus, errorThrown) {
    });
    request.complete(function (response) {
      $(prevRow).children('td').slice(4, 5).text(changed_status);
      element.attr("disabled", false);
    })
  });

  $("#list_status").closest('tr').show();
  $("#list_pagination").closest("tr").show();
  $("#list_count").closest("tr").show();
  $("#list_calview").closest("tr").hide();
  $("#list_filters").closest("tr").hide();
  $("#list_month_filter").change(function () {
    if (($(this).val()) === "") {
      //$("#list_status").closest('tr').show();
      $("#list_pagination").closest("tr").show();
      $("#list_count").closest("tr").show();
      $("#list_calview").closest("tr").hide();
      $("#list_filters").closest("tr").hide();
    }
    else {
      //$("#list_status").closest('tr').hide();
      $("#list_pagination").closest("tr").hide();
      $("#list_count").closest("tr").hide();
      $("#list_calview").closest("tr").show();
      $("#list_filters").closest("tr").show();
    }
  });

  $("#grid_status").closest('tr').show();
  $("#grid_pagination").closest("tr").show();
  $("#grid_count").closest("tr").show();
  $("#grid_calview").closest("tr").hide();
  $("#grid_filters").closest("tr").hide();
  $("#grid_month_filter").change(function () {
    if (($(this).val()) === "") {
      //$("#list_status").closest('tr').show();
      $("#grid_pagination").closest("tr").show();
      $("#grid_count").closest("tr").show();
      $("#grid_calview").closest("tr").hide();
      $("#grid_filters").closest("tr").hide();
    }
    else {
      //$("#list_status").closest('tr').hide();
      $("#grid_pagination").closest("tr").hide();
      $("#grid_count").closest("tr").hide();
      $("#grid_calview").closest("tr").show();
      $("#grid_filters").closest("tr").show();
    }
  });

  $('#grid_background').closest('tr').show();
  $('#grid_layout').change(function () {
    if (($(this).val()) === "") {
      $('#grid_background').closest('tr').show();
    }
    else {
      $('#grid_background').closest('tr').hide();
    }
  });

  function eventer_remove_default(temp) {
    $(temp).html('<div class="dashicons-before dashicons-no"></div>');
  }
  var request;
  $(".remove-reg").click(function () {
    if ($(this).children().length > 0) {
      $(this).text(dynamicval.remove_btn);
      var temp = $(this);
      setTimeout(function () {
        eventer_remove_default(temp);
      }, 8000);
    }
    else {
      if (request) {
        request.abort();
      }
      var reg_id = $(this).attr("data-reg");
      var reg_email = $(this).attr("data-regemail");
      request = $.ajax({
        url: dynamicval.ajax_url,
        type: "post",
        //dataType:'json',
        data: {
          action: 'eventer_remove_registrant',
          reg_id: reg_id,
          reg_email: reg_email,
          nonce: dynamicval.registrant_remove_nonce,
        },
      });
      request.done(function (response, textStatus, jqXHR) {
        if (response == "deleted") {
          $("tr#registrant-" + reg_id).closest("tr").next()[0].remove();
          $("tr#registrant-" + reg_id).remove();
        }
      });
      request.fail(function (jqXHR, textStatus, errorThrown) {
      });
    }
  });
  EVENTER.COPYSHORTCODE = function (text) {
    var textArea = document.createElement("textarea");

    // Place in top-left corner of screen regardless of scroll position.
    textArea.style.position = 'fixed';
    textArea.style.top = 0;
    textArea.style.left = 0;

    // Ensure it has a small width and height. Setting to 1px / 1em
    // doesn't work as this gives a negative w/h on some browsers.
    textArea.style.width = '2em';
    textArea.style.height = '2em';

    // We don't need padding, reducing the size if it does flash render.
    textArea.style.padding = 0;

    // Clean up any borders.
    textArea.style.border = 'none';
    textArea.style.outline = 'none';
    textArea.style.boxShadow = 'none';

    // Avoid flash of white box if rendered for any reason.
    textArea.style.background = 'transparent';


    textArea.value = text;

    document.body.appendChild(textArea);

    textArea.select();

    try {
      var successful = document.execCommand('copy');
      var msg = successful ? 'successful' : 'unsuccessful';
      //console.log('Copying text command was ' + msg);
    } catch (err) {
      //console.log('Oops, unable to copy');
    }

    document.body.removeChild(textArea);
  }
  EVENTER.REMOVEFIELDS = function (obj) {
    var parent = jQuery(obj).parent();
    //console.log(parent)
    parent.remove();
  };
  EVENTER.ADDFIELDROW = function () {
    var row = jQuery('#master-row').html();
    jQuery('#field_wrap').append(row);
  };
  $(".add_import_field").click(function () {
    EVENTER.ADDFIELDROW();
  });
  $(document).on('click', ".remove_import_field", function () {
    EVENTER.REMOVEFIELDS($(this));
  });
  $(".generate_eventer_bookings, .generate_eventer_bookings_slot").change(function () {
    if ($(this).hasClass('generate_eventer_bookings')) {
      $(this).closest('div').attr('data-date', $(this).val());
    }
    else {
      $(this).closest('div').attr('data-time', $(this).val());
    }

    var selected_date = $(this).closest('div').attr('data-date');
    var selected_time = $(this).closest('div').attr('data-time');
    var eventer_id = $(this).attr("data-eventer");
    var selected_element = $(this);
    if (selected_date === '1') {
      $(this).closest('.eventer-booked-tickets-record').find(".booked_fields").attr("disabled", true);
      $(this).closest('.eventer-booked-tickets-record').find(".save_booked_btn").attr("disabled", true);
      $(this).closest('.eventer-booked-tickets-record').find(".reset_booked_btn").attr("disabled", false);
      $(this).closest('.eventer-booked-tickets-record').find(".booked_fields").each(function () {
        $(this).val('');
      });
      return false;
    }
    else if (selected_date === '') {
      $(this).closest('.eventer-booked-tickets-record').find(".booked_fields").attr("disabled", true);
      $(this).closest('.eventer-booked-tickets-record').find(".save_booked_btn").attr("disabled", true);
      $(this).closest('.eventer-booked-tickets-record').find(".reset_booked_btn").attr("disabled", true);
      $(this).closest('.eventer-booked-tickets-record').find(".booked_fields").each(function () {
        $(this).val('');
      });
      return false;
    }
    request = $.ajax({
      url: dynamicval.ajax_url,
      type: "post",
      dataType: 'json',
      data: {
        action: 'eventer_get_booked_tickets',
        eventer_id: eventer_id,
        booked_date: selected_date,
        booked_time: selected_time,
      },
      beforeSend: function (xhr) {
        selected_element.closest('.eventer-booked-tickets-record').find('.eventer-loading').show();
        selected_element.closest('.eventer-booked-tickets-record').find(".booked_fields").attr("disabled", true);
        selected_element.closest('.eventer-booked-tickets-record').find(".save_booked_btn").attr("disabled", true);
        selected_element.closest('.eventer-booked-tickets-record').find(".reset_booked_btn").attr("disabled", true);
      },
    });
    request.done(function (response, textStatus, jqXHR) {
      $('.booked_record_event_title').val('');
      $('.booked_record_event_number').val('');
      $('.booked_record_event_price').val('');
      if ($('.booked_record_event_id').hasClass('eventer-admin-ticket-woo-id')) {

      }
      else {
        $('.booked_record_event_id').val('');
      }
      var common_counting = $('.eventer-common-ticket-count').val();
      $('.booked_record_event_restrict').prop('checked', false);
      selected_element.closest('.eventer-booked-tickets-record').find('.booked_eventer_section').each(function (i) {
        if (typeof (response.tickets[i]) !== "undefined" && response.tickets[i] !== null) {
          if (response.title && response.title[response.tickets[i]['ticket_id']] != '' && response.title[response.tickets[i]['ticket_id']] != null) {
            $(this).find(".booked_record_event_title").val(response.title[response.tickets[i]['ticket_id']]);
          } else {
            $(this).find(".booked_record_event_title").val(response.tickets[i]['name']);
          }


          if (typeof $('.eventer-common-ticket-count').val() !== 'undefined' && $('.eventer-common-ticket-count').val() !== '') {
            $('.eventer_admin_common_count').val(response.tickets[i]['tickets']);
          }
          $(this).find(".booked_record_event_number").val(response.tickets[i]['tickets']);

          $(this).find(".booked_record_event_price").val(response.tickets[i]['price']);
          $(this).find(".booked_record_event_enabled").val(response.tickets[i]['enabled']);
          if ($(this).find(".booked_record_event_id").hasClass('eventer-admin-ticket-woo-id')) {

          }
          else {
            $(this).find(".booked_record_event_id").val(response.tickets[i]['dynamic']);
            $(this).find(".booked_record_event_pid").val(response.tickets[i]['pid']);
            $(this).find('.eventer-admin-woo-product-info option[value="' + response.tickets[i]['pid'] + '"]').attr("selected", "selected");
          }

          $(".booked_record_event_badge").val(response.tickets[i]['label']);
          if (response.tickets[i]['featured'] === "1") {
            $(".booked_record_event_featured").val('1');
          }
          else {
            $(".booked_record_event_featured").val('');
          }
          if (response.tickets[i]['restricts'] === "1") {
            $(this).find(".booked_record_event_restrict").prop('checked', true);
          }
          else {
            $(this).find(".booked_record_event_restrict").prop('checked', false);
          }
        }
      });
      selected_element.closest('.eventer-booked-tickets-record').find(".booked_fields").attr("disabled", false);
      selected_element.closest('.eventer-booked-tickets-record').find(".save_booked_btn").attr("disabled", false);
      selected_element.closest('.eventer-booked-tickets-record').find(".reset_booked_btn").attr("disabled", true);
      selected_element.closest('.eventer-booked-tickets-record').find('.eventer-loading').hide();
      if (common_counting !== '') {
        $("input.booked_record_event_number").prop('disabled', true);
      }
    });
    request.fail(function (jqXHR, textStatus, errorThrown) {
    });
  });
  $(".update_booked_tickets").click(function () {
    var selected_val = $(this).closest("div.booked_ticket_section").find(".generate_eventer_bookings").val();
    var time_slot = $(this).closest("div.booked_ticket_section").find(".generate_eventer_bookings_slot").val();
    var eventer_id = $(this).attr("data-eventer");
    var position = $(this).attr("data-position");
    var btn_element = $(this);
    var selected_element = $(this).closest("div.booked_ticket_section");
    var Tickets = [];
    var ticket_counting = '';
    var ticket_common_counting = '';
    var badge_val = $('.eventer_admin_badge').val();
    var featured = "";
    if ($(".eventer_admin_featured").val() === '1') {
      featured = "1";
    }
    selected_element.find(".booked_eventer_section").each(function () {
      var data = {};
      if ($(this).find(".booked_record_event_title").val() != '') {

        if ($(this).find(".booked_record_event_restrict").is(":checked")) {
          data.restrict = "1";
        }
        else {
          data.restrict = "";
        }
        ticket_counting = $(this).find(".booked_record_event_number").val();
        ticket_common_counting = $('.eventer_admin_common_count').val();
        ticket_counting = (ticket_common_counting !== '') ? ticket_common_counting : ticket_counting;
        data.name = $(this).find(".booked_record_event_title").val();
        data.number = ticket_counting;
        data.price = $(this).find(".booked_record_event_price").val();
        data.enabled = $(this).find(".booked_record_event_enabled").val();
        data.product = $(this).find(".eventer-admin-woo-product-info").val();

      }
      data.id = $(this).find(".booked_record_event_id").val();
      data.pid = $(this).find(".booked_record_event_pid").val();
      data.badge = badge_val;
      data.featured = featured;
      Tickets.push(data);
    });
    request = $.ajax({
      url: dynamicval.ajax_url,
      type: "post",
      dataType: 'json',
      data: {
        action: 'eventer_update_booked_tickets',
        eventer_id: eventer_id,
        booked_date: selected_val,
        time: time_slot,
        updated_detail: Tickets,
        featured: featured,
        position: position,
      },
      beforeSend: function (xhr) {
        btn_element.text(dynamicval.saving_btn + '...');
        btn_element.prop('disabled', true);
      },
    });
    request.complete(function (response, textStatus, jqXHR) {
      if (position === 'reset') {
        btn_element.remove();
      }
      else {

        btn_element.text(dynamicval.save_btn);
        btn_element.prop('disabled', false);
        var selected_date = $('.generate_eventer_bookings').val();
        $(".generate_eventer_bookings").val(selected_date).trigger("change");
      }

    });
    request.fail(function (jqXHR, textStatus, errorThrown) {
    });
  });

  $(document).on('click', '.eventer-add-carousel', function () {
    var params = [];
    $('.eventer-carousel-params').each(function () {
      params.push($(this).val());
    });
    $('#eventer-grid-area-start, #eventer-slider-area-start').append('<div style="display:none;"><input type="text" data-sattr="carousel" id="grid_carousel" class="eventer_select_val eventer-carousel-field" value="' + params + '"></div>');
    tb_remove();
  });
  $(document).on('click', '.categorychecklist input', function () {
    var term_checkbox = $(this);
    var taxonomy = $(this).closest('ul').attr('data-wp-lists');
    if (($(this).is(":checked")) && (taxonomy === 'list:eventer-venue' || taxonomy === 'list:eventer-organizer')) {
      var term_id = $(this).val();
      request = $.ajax({
        url: dynamicval.ajax_url,
        type: "post",
        //dataType:'json',
        data: {
          action: 'eventer_get_term_details',
          term_id: term_id,
          taxonomy: taxonomy,
        },
      });
      request.done(function (response, textStatus, jqXHR) {
        term_checkbox.closest('.postbox').find('.eventer-admin-term-metas-show').remove();
        term_checkbox.closest('.postbox').find('.wp-hidden-children').append(response);
      });
    }
    else {
      term_checkbox.closest('.postbox').find('.eventer-admin-term-metas-show').remove();
    }
  });
  $('.eventer_admin_add_more_ticket').click(function () {
    var add_this_field = $('table.eventer-admin-woo-tickets-table tr.eventer_admin_new_additional_ticket').clone();
    var removed_default_class = add_this_field.removeClass('eventer_admin_new_additional_ticket');
    var add_common_class = removed_default_class.addClass('wc_ticket_section');
    var new_added_tr = add_common_class.removeAttr('style');
    var added_trs = $('table.eventer-admin-woo-tickets-table tr.wc_ticket_section').length;
    new_added_tr.find('select.eventer-admin-dynamic-ticket-action').attr('name', 'wceventer_ticket_status' + parseInt(added_trs));
    new_added_tr.find('.eventer-admin-dynamic-ticket-name').attr('name', 'wceventer_ticket_name[]');
    new_added_tr.insertAfter($('table.eventer-admin-woo-tickets-table tr.wc_ticket_section:last'));
    $('#eventer_event_start_dt, #eventer_event_end_dt, .eventer_activation_date').appendDtpicker({
      //"inline": true, //This will allow datepicker to always open
      "amPmInTimeList": true,
      "minuteInterval": 15,
      "closeOnSelected": true,
      "firstDayOfWeek": parseInt(dynamicval.week_start)
    });
    return false;
  });
  $(document).on('click', '.eventer-time-slot-add', function (e) {
    var section_main = $(this).closest('.eventer-time-slot-sections');
    var default_section = section_main.find('table tr.eventer-time-slot-default').clone();
    default_section = default_section.addClass('eventer-time-slot-section');
    default_section = default_section.removeClass('eventer-time-slot-default');
    default_section = default_section.show();
    default_section.insertBefore('tr.eventer-time-slot-default');
    e.preventDefault();
  });
  $(document).on('click', '.eventer-remove-time-slot', function () {
    $(this).closest('tr').remove();
  });
  $('.eventer-admin-woo-download-tickets-action').click(function () {
    $(this).closest('form').submit();
    return false;
  });

  $(document).on('click', '.eventer-coupon-add-new', function (e) {
    var sample_tr = $('table .eventer-coupon-clone').clone();
    //console.log(sample_tr);
    //return false;
    sample_tr = $(sample_tr).removeClass('eventer-coupon-clone');
    sample_tr = $(sample_tr).addClass('eventer-coupon-reloaded');
    sample_tr.show();
    $(this).closest('div').find('table').append(sample_tr);
    $('.eventer-coupon-validity').appendDtpicker({
      //"inline": true, //This will allow datepicker to always open
      "amPmInTimeList": time_format,
      "minuteInterval": 15,
      "closeOnSelected": true,
      "firstDayOfWeek": parseInt(dynamicval.week_start)
    });
    return false;
  });
  $(document).on('click', '.eventer-coupon-save', function (e) {
    var coupons = [{ 'id': '', 'title': '', 'code': '', 'amount': '', 'validity': '', 'status': '', 'remove': '' }];
    $('table.coupon tr').each(function () {
      if ($(this).hasClass('eventer-coupon-clone') || $(this).hasClass('eventer-coupons-heading')) {
        return true;
      }
      var coupon_vals = {};
      var coupon_id = $(this).find('.eventer-coupon-id').val();
      var coupon_title = $(this).find('.eventer-coupon-title').val();
      var coupon_code = $(this).find('.eventer-coupon-code').val();
      var coupon_amount = $(this).find('.eventer-coupon-amount').val();
      var coupon_validity = $(this).find('.eventer-coupon-validity').val();
      var coupon_status = $(this).find('.eventer-coupon-status');
      coupon_status = ((coupon_status).is(":checked")) ? 1 : 0;
      var coupon_remove = $(this).find('.eventer-coupon-remove');
      coupon_remove = ((coupon_remove).is(":checked")) ? 1 : 0;
      coupon_vals.id = coupon_id;
      coupon_vals.title = coupon_title;
      coupon_vals.code = coupon_code;
      coupon_vals.amount = coupon_amount;
      coupon_vals.validity = coupon_validity;
      coupon_vals.status = coupon_status;
      coupon_vals.remove = coupon_remove;
      coupons.push(coupon_vals);
    });
    //console.log(coupons);
    $.ajax({
      url: dynamicval.ajax_url,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'eventer_coupon_refresh',
        coupons: coupons,
      },
      beforeSend: function (xhr) {
        $('.eventer-coupon-loading').show();
      },
      success: function (response) {
        //console.log(response);
        var coupons_found = response.length;
        $('.eventer-coupon-reloaded').remove();
        if (coupons_found > 0) {
          jQuery.each(response, function (i, val) {
            var sample_tr_set = $('table .eventer-coupon-clone').clone();
            var sample_tr = $(sample_tr_set).removeClass('eventer-coupon-clone');
            sample_tr.addClass('eventer-coupon-reloaded');
            sample_tr.show();
            sample_tr.find('.eventer-coupon-id').val(val.id);
            sample_tr.find('.eventer-coupon-title').val(val.coupon_name);
            sample_tr.find('.eventer-coupon-code').val(val.coupon_code);
            sample_tr.find('.eventer-coupon-amount').val(val.discounted);
            sample_tr.find('.eventer-coupon-validity').val(val.valid_till);
            if (val.coupon_status === "1") {
              sample_tr.find('.eventer-coupon-status').attr('checked', true);
            }
            $('table.coupon').append(sample_tr);

          });
        }
        $('.eventer-coupon-loading').hide();
        $('.eventer-coupon-validity').appendDtpicker({
          //"inline": true, //This will allow datepicker to always open
          "amPmInTimeList": time_format,
          "minuteInterval": 15,
          "closeOnSelected": true,
          "firstDayOfWeek": parseInt(dynamicval.week_start)
        });
      }
    });
    return false;
  });
  if ((dynamicval.load_coupons) === '1') {
    $('.eventer-coupon-save').trigger('click');
    //return false;
  }
});
