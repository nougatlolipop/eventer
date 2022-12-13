jQuery(function ($) {
  "use strict";
  $(document).ready(function () {
    $(document).on('click', '.eventer-set-form-fields', function () {
      eventer_set_form_fields();
    });

    window.eventer_wp_editor_set = function () {
      $('.eventer_wp_editor').each(function () {
        if ($(this).hasClass('eventer_data_required')) {
          $(this).attr('data-required', 1);
        }
        var result = $(this).attr('class').split(' ');
        for (var st = 0; st <= result.length; st++) {
          if (result[st].indexOf('data-meta') !== -1) {
            var data_meta = result[st].replace('data-meta|', '');
            $(this).attr('data-meta', data_meta);
            var editor_id = $(this).attr('id');
            tinymce.execCommand('mceRemoveEditor', false, editor_id);
            tinymce.execCommand('mceAddEditor', false, editor_id);
            return false;
          }
        }
      });
    };
    window.eventer_featured_image_wp = function () {
      var imgIdInputURL;
      var frame,
        metaBox = $('.eventer-wp-featured-media'), // Your meta box id here
        addImgLink = metaBox.find('.eventer_featured_image_wp_add'),
        delImgLink = metaBox.find('.eventer_featured_image_wp_remove'),
        imgContainer = metaBox.find('.eventer_featured_image_wp_preview'),
        imgIdInput = metaBox.find('.eventer_featured_image_id');
      imgIdInputURL = metaBox.find('.eventer_featured_image_URL');

      // ADD IMAGE LINK
      addImgLink.on('click', function (event) {
        event.preventDefault();
        if (frame) {
          frame.open();
          return;
        }

        // Create a new media frame
        frame = wp.media({
          title: 'Select or Upload Media Of Your Chosen Persuasion',
          button: {
            text: 'Use this media'
          },
          multiple: false  // Set to true to allow multiple files to be selected
        });


        // When an image is selected in the media frame...
        frame.on('select', function () {

          // Get media attachment details from the frame state
          var attachment = frame.state().get('selection').first().toJSON();

          // Send the attachment URL to our custom image input field.
          imgContainer.append('<img src="' + attachment.url + '" alt="" style="max-width:100%;"/>');
          imgIdInputURL.val(attachment.url);
          // Send the attachment id to our hidden input
          imgIdInput.val(attachment.id);

          // Hide the add image link
          addImgLink.addClass('hidden');

          // Unhide the remove image link
          delImgLink.removeClass('hidden');
        });

        // Finally, open the modal on click
        frame.open();
      });


      // DELETE IMAGE LINK
      delImgLink.on('click', function (event) {

        event.preventDefault();

        // Clear out the preview image
        imgContainer.html('');

        // Un-hide the add image link
        addImgLink.removeClass('hidden');

        // Hide the delete image link
        delImgLink.addClass('hidden');

        // Delete the image id from the hidden input
        imgIdInput.val('');

      });
    };
    window.eventer_set_datepicker = function () {
      $('.eventer_front_date_field').appendDtpicker({
        //"inline": true, //This will allow datepicker to always open
        "amPmInTimeList": true,
        "minuteInterval": 15,
        "closeOnSelected": true,
        "futureOnly": true,
        "firstDayOfWeek": frontForm.week_start,
        "calendarMouseScroll": false,
        "onShow": function () {
          $('.eventer-datepicker').addClass('eventer-fe-datepicker');
        }
      });
    };

    function eventer_set_form_fields() {
      var data_get = {};
      var custom_fields_key = [];
      var eventer_get = '';
      $('#eventer_add_new_event').find('.eventer_dynamic_section_area').each(function () {
        eventer_get = $('#eventer_add_new_event').find('.eventer-set-form-fields').val();
        var section_type = $(this).attr('data-section');
        if (section_type === '') {
          $(this).find('.eventer_add_event_field').each(function () {
            var field_meta_key = $(this).attr('data-meta');
            if (field_meta_key !== 'title' && field_meta_key !== 'content') {
              custom_fields_key.push(field_meta_key);
            }
          });
        }
        else if (section_type === 'tickets') {
          $(this).find('.eventer_add_event_field').each(function () {
            var field_meta_key = $(this).attr('data-meta');
            if ((field_meta_key.indexOf('eventer_ticket_name') != -1 || field_meta_key.indexOf('eventer_ticket_quantity') != -1 || field_meta_key.indexOf('eventer_ticket_price') != -1) && (jQuery.inArray("eventer_tickets", custom_fields_key) === -1)) {
              custom_fields_key.push('eventer_tickets');
            }
            else if (field_meta_key.indexOf('eventer_ticket_name') === -1 && field_meta_key.indexOf('eventer_ticket_quantity') === -1 && field_meta_key.indexOf('eventer_ticket_price') === -1) {
              custom_fields_key.push(field_meta_key);
            }

          });
        }
      });
      data_get.custom_fields_get = custom_fields_key;
      eventer_get = (eventer_get !== '') ? eventer_get : ajaxval.update;
      $.ajax({
        method: "GET",
        async: false, //Because content was not loading due to data-meta was not set before response
        url: ajaxval.root + 'wp/v2/eventer/' + eventer_get,
        data: data_get,
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', ajaxval.nonce);
        },
        success: function (response) {
          $('#eventer_add_new_event').find('.eventer_dynamic_section_area').each(function () {
            var section_type = $(this).attr('data-section');
            var meta_key = '';
            if (section_type === 'eventer-organizer') {
              $(this).find('.eventer_add_event_field').each(function () {
                if ($(this).prop("type") === 'file') {
                  return true;
                }
                meta_key = $(this).attr('data-meta');
                if (meta_key === 'eventer-organizer') {
                  $(this).val(response.eventer_organizer.term);
                  return true;
                }
                if ($(this).prop("type") == "checkbox" || $(this).prop("type") == "radio") {
                  var sdfaf = $(this).val();
                  if (jQuery.inArray(sdfasdf, response.eventer_organizer[meta_key]) !== -1) {
                    $(this).prop('checked', true);
                    //$(this).addClass('eventer_add_event_field');
                    return true;
                  }
                }
                else {
                  $(this).val(response.eventer_organizer[meta_key]);
                }
              });
            }
            else if (section_type === 'eventer-venue') {
              $(this).find('.eventer_add_event_field').each(function () {
                if ($(this).prop("type") === 'file') {
                  return true;
                }
                meta_key = $(this).attr('data-meta');
                if (meta_key === 'eventer-venue') {
                  $(this).val(response.eventer_venue.term);
                  return true;
                }
                if ($(this).prop("type") == "checkbox" || $(this).prop("type") == "radio") {
                  var sdfasdf = $(this).val();
                  if (jQuery.inArray(sdfasdf, response.eventer_venue[meta_key]) !== -1) {
                    $(this).prop('checked', true);
                    //$(this).addClass('eventer_add_event_field');
                    return true;
                  }
                }
                else {
                  $(this).val(response.eventer_venue[meta_key]);
                }
              });
            }
            else if (section_type === 'tickets') {
              var tickets = response.eventer_tickets;
              $(this).find('.eventer-row').each(function (start) {
                if (start + 1 > tickets.length || tickets.length <= 0) {
                  return false;
                }
                var woo_product = (typeof tickets[start].pid !== 'undefined') ? tickets[start].pid : '';
                var woo_ticket_id = (typeof tickets[start].id !== 'undefined') ? tickets[start].id : '';
                var woo_ticket_restrict = (typeof tickets[start].restrict !== 'undefined') ? tickets[start].restrict : '';
                var woo_enabled = (typeof tickets[start].enabled !== 'undefined') ? tickets[start].enabled : '';
                if (woo_product !== '') {
                  $('[data-meta="eventer_ticket_name' + start + '"]').closest('div').append('<input type="hidden" data-meta="eventer_ticket_pid" class="eventer_ticket_pid" value="' + woo_product + '">');
                }
                if (woo_ticket_id !== '') {
                  $('[data-meta="eventer_ticket_name' + start + '"]').closest('div').append('<input type="hidden" data-meta="eventer_ticket_id" class="eventer_ticket_id" value="' + woo_ticket_id + '">');
                }
                if (woo_ticket_restrict !== '') {
                  $('[data-meta="eventer_ticket_name' + start + '"]').closest('div').append('<input type="hidden" data-meta="eventer_ticket_restrict" class="eventer_ticket_restrict" value="' + woo_ticket_restrict + '">');
                }
                if (woo_enabled !== '') {
                  $('[data-meta="eventer_ticket_name' + start + '"]').closest('div').append('<input type="hidden" data-meta="eventer_ticket_enabled" class="eventer_ticket_enabled" value="' + woo_enabled + '">');
                }
                $('[data-meta="eventer_ticket_name' + start + '"]').val(tickets[start].name);
                $('[data-meta="eventer_ticket_quantity' + start + '"]').val(tickets[start].number);
                $('[data-meta="eventer_ticket_price' + start + '"]').val(tickets[start].price);
              });
              $(this).find('.eventer_add_event_field').each(function () {
                var meta_key = $(this).attr('data-meta');
                if (meta_key === '' || $(this).hasClass('eventer_ticket_name') || $(this).hasClass('eventer_ticket_quantity') || $(this).hasClass('eventer_ticket_price')) {
                  return true;
                }
                if ($(this).prop("type") == "checkbox" || $(this).prop("type") == "radio") {
                  var sdfasdf = $(this).val();
                  if (jQuery.inArray(sdfasdf, response[meta_key]) !== -1) {
                    $(this).prop('checked', true);
                    return true;
                  }
                }
                else {
                  $(this).val(response[meta_key]);
                }
              });
            }
            else if (section_type !== 'eventer-venue' && section_type !== 'eventer-orgabizer' && section_type !== 'tickets') {
              $(this).find('.eventer_add_event_field').each(function () {
                if ($(this).prop("type") === 'file') {
                  return true;
                }
                var meta_key = $(this).attr('data-meta');
                if (meta_key === 'eventer_featured_image_preview') {
                  return true;
                }
                if (meta_key === 'title' || meta_key === 'content') {
                  var id = $('[data-meta="' + meta_key + '"]').attr('id');
                  if (meta_key === 'content' && $('#' + id).hasClass('eventer_wp_editor')) {
                    tinymce.get(id).execCommand('mceInsertContent', false, response[meta_key].rendered);
                  }
                  else {
                    $(this).val(response[meta_key].rendered);
                    return true;
                  }

                }
                if ($(this).prop("type") == "checkbox" || $(this).prop("type") == "radio") {
                  var sdfasdf = $(this).val();
                  if (jQuery.inArray(sdfasdf, response[meta_key]) !== -1) {
                    $(this).prop('checked', true);
                    return true;
                  }
                }
                else {
                  $(this).val(response[meta_key]);
                }
              });
            }
          });
          $('.eventer_featured_image_preview').append('<img src="' + response.eventer_featured_image_url + '">');
        },
        fail: function (response) {
          alert(ajaxval.failure);
        },
        complete: function (response) {
          //$('[data-meta="content"]').val(response.content.rendered);
        }
      });
    }

    if (ajaxval.update !== '') {
      setTimeout(function () { eventer_set_form_fields(); }, 500);
    }
    window.eventer_wp_editor_set();
    eventer_set_datepicker();
    eventer_featured_image_wp();

    // Validate Front End Form Fields
    function eventer_valdiate_fields(element) {
      var passing = "1";
      var required = element.attr("data-required");
      var field_type = '';
      if (required === "1") {
        var field_type = element.prop("type");
        if (field_type === "text" || field_type === "textarea" || field_type === "email" || field_type === "number") {
          if (element.val() === '') {
            element.addClass("eventer-required-field");
            passing = "0";
          }
          else if (field_type === "email" && EVENTER.VALIDATEEMAIL(element) === "0") {
            element.addClass("eventer-required-field");
            element.closest("form").find(".message").empty();
            element.closest("form").find(".message").append(element.attr("name") + ' <p>' + initval.email_msg + '</p>');
            passing = "0";
          }
          else if (field_type === "number" && !$.isNumeric(element.val())) {
            element.addClass("eventer-required-field");
            element.closest("form").find(".message").empty();
            element.closest("form").find(".message").append(element.attr("name") + ' <p>' + initval.number_msg + '</p>');
            passing = "0";
          }
          else {
            element.removeClass("eventer-required-field");
            passing = "1";
          }
        }
        else if (field_type === "checkbox" || field_type === "radio") {
          var fname = element.attr("name");
          var result = CHECKBOXVALIDATE(fname, element.prop("type"));
          if (result === "1") {
            element.closest("label").removeClass("eventer-required-field");
            passing = "1";
          }
          else {
            element.closest("label").addClass("eventer-required-field");
            passing = "0";
          }
        }
        else {
          if (element.val() === '' || element.val() === '35' || element.val() == '0') {
            element.addClass("eventer-required-field");
            passing = "0";
          }
          else {
            element.removeClass("eventer-required-field");
            passing = "1";
          }
        }
      }
      var label = element.closest('div').find('label').text();
      if (element.hasClass('eventer_wp_editor')) {
        label = element.closest('.eventer_dynamic_meta_field').find('.wp-editor-label').text();
      }
      return { 'error': passing, 'message': label };
    }

    function CHECKBOXVALIDATE(names, fields) {
      var chkds = $("input[name='" + names + "']:" + fields);
      if (chkds.is(":checked")) {
        return "1";
      }
      else {
        return "0";
      }
    }

    $('.eventer_term_saved').click(function () {
      var term = $(this).val();
      if ($(this).is(":checked")) {
        var metas = $(this).attr('data-metas');
        metas = JSON.parse(metas);
        $.each(metas, function (index, value) {
          $('[data-meta="' + index + '"').val(value);
        });
      }
      else {

      }
    });
    jQuery(document).on('submit', '#eventer_add_new_event', function (event) {
      $('#section-message .eventer-row').empty();
      var validated = "1";
      var message_show = '';
      var eventer_id = $(this).find('.eventer-set-form-fields').val();
      $('form#eventer_add_new_event *').filter(':input').each(function () {
        var passing = eventer_valdiate_fields($(this));
        var passing_error = passing.error;
        var passing_message = passing.message;
        if (passing_error !== "1") {
          validated = "0";
          message_show += '<div class="eventer-col10 eventer-fn-form-status-error">Please fill ' + passing_message + '</div>';
        }
      });
      if (validated !== "1") {

        $('#section-message').show();
        $(this).find('#section-message .eventer-row').append(message_show);
        $('html, body').animate({
          scrollTop: $("#section-message").offset().top
        }, 1000);
        return false;
      }
      $('#section-message').hide();
      var terms = [];
      var custom_fields = {};
      var checked_data = [];
      var tickets = [];
      var tickets_assoc = {};
      var woo_tickets = [];
      var custom_meta = {
        //status: status_set,
      };
      var status_set = (eventer_id === '') ? custom_meta.status = ajaxval.event_status : '';
      var this_form = $(this);
      $(this).find('.eventer_dynamic_section_area').each(function () {
        var section_type = $(this).attr('data-section');
        if (section_type === 'eventer-organizer') {
          var organizer = {};
          var organizer_meta = {};
          var checked_data = [];

          $(this).find('.eventer_add_event_field').each(function () {
            var field_meta_value = '';
            var field_meta_key = $(this).attr('data-meta');
            if (field_meta_key === '' || field_meta_key === 'eventer-organizer') {
              organizer.term = (field_meta_key !== '') ? $(this).val() : '';
              return true;
            }
            if (($(this).prop("type") == "checkbox" || $(this).prop("type") == "radio") && ($(this).is(':checked'))) {
              if (!(field_meta_key in organizer_meta)) {
                checked_data = [];
              }

              if ($(this).is(':checked')) {
                checked_data.push($(this).val());
                organizer_meta[field_meta_key] = checked_data;
              }

            }
            else if (($(this).prop("type") !== "checkbox" && $(this).prop("type") !== "radio")) {
              field_meta_value = $(this).val();
              organizer_meta[field_meta_key] = field_meta_value;
            }
          });
          organizer.taxonomy = 'eventer-organizer';
          organizer.meta = organizer_meta;
          terms.push(organizer);
        }
        else if (section_type === 'eventer-venue') {
          var venue = {};
          var venue_meta = {};
          var venue_checked_data = [];

          $(this).find('.eventer_add_event_field').each(function () {
            var field_meta_key = $(this).attr('data-meta');
            if (field_meta_key === '' || field_meta_key === 'eventer-venue') {
              venue.term = (field_meta_key !== '') ? $(this).val() : '';
              return true;
            }
            if (($(this).prop("type") == "checkbox" || $(this).prop("type") == "radio") && ($(this).is(':checked'))) {
              if (!(field_meta_key in venue_meta)) {
                venue_checked_data = [];
              }

              if ($(this).is(':checked')) {
                venue_checked_data.push($(this).val());
                venue_meta[field_meta_key] = venue_checked_data;
              }

            }
            else if (($(this).prop("type") !== "checkbox" && $(this).prop("type") !== "radio")) {
              venue_meta[field_meta_key] = $(this).val();
            }
          });
          venue.taxonomy = 'eventer-venue';

          venue.meta = venue_meta;
          terms.push(venue);
        }
        else if (section_type === 'tickets') {
          $(this).find('.eventer-row').each(function (start) {
            var ticket_arr = {};
            var ticket_row = $(this);
            var woo_tickets_each = {};
            var event_ticket_name = ticket_row.find('.eventer_ticket_name').val();
            if (event_ticket_name !== '' && typeof event_ticket_name !== 'undefined') {
              var product_metas = {};
              ticket_arr.name = event_ticket_name;
              var event_ticket_quantity = $(this).find('.eventer_ticket_quantity').val();
              var event_price = $(this).find('.eventer_ticket_price').val();
              var event_pid = $(this).find('.eventer_ticket_pid').val();
              var event_ticket_id = $(this).find('.eventer_ticket_id').val();
              var event_ticket_enabled = $(this).find('.eventer_ticket_enabled').val();
              var event_ticket_restrict = $(this).find('.eventer_ticket_restrict').val();
              event_pid = (event_pid !== '' && typeof event_pid !== 'undefined') ? event_pid : '';
              ticket_arr.number = event_ticket_quantity;
              ticket_arr.price = event_price;

              tickets_assoc[event_ticket_name] = event_ticket_quantity;
              product_metas._regular_price = event_price;
              product_metas._price = event_price;
              if (ajaxval.woo_tickets === 'on') {
                $.ajax({
                  method: "POST",
                  async: false,
                  url: ajaxval.root + 'wp/v2/product/' + event_pid,
                  data: { 'metas': product_metas, 'title': event_ticket_name, 'content': 'This is event ticket, you can not add it directly.', 'status': 'publish' },
                  beforeSend: function (xhr) {
                    this_form.find('.eventer-loader-wrap').show();
                    xhr.setRequestHeader('X-WP-Nonce', ajaxval.nonce);
                  },
                }).success(function (response) {
                  ticket_arr.pid = response.id;
                  ticket_arr.id = event_ticket_id;
                  ticket_arr.restrict = event_ticket_restrict;
                  ticket_arr.enabled = event_ticket_enabled;
                  woo_tickets_each.wceventer_ticket_id = response.id;
                  woo_tickets_each.wceventer_ticket_number = event_ticket_quantity;

                  if (event_pid === '') {
                    ticket_row.find('.eventer_ticket_name').closest('div').append('<input type="hidden"  class="eventer_ticket_pid" value="' + response.id + '">');
                  }
                }).error(function (response) {

                }).complete(function (response) {

                });
              }

              tickets.push(ticket_arr);
              woo_tickets.push(woo_tickets_each);
            }
            else {
              $(this).find('.eventer_add_event_field').each(function () {
                var meta_key = $(this).attr('data-meta');
                if (meta_key === '' || $(this).hasClass('eventer_ticket_name') || $(this).hasClass('eventer_ticket_quantity') || $(this).hasClass('eventer_ticket_price') || $(this).val() === '') {
                  return true;
                }
                else {
                  var meta_val = '';
                  if (($(this).prop("type") == "checkbox" || $(this).prop("type") == "radio") && ($(this).is(':checked'))) {
                    if (!(meta_key in custom_fields)) {
                      checked_data = [];
                    }

                    if ($(this).is(':checked')) {
                      checked_data.push($(this).val());
                      custom_fields[meta_key] = checked_data;
                    }

                  }
                  else if (($(this).prop("type") !== "checkbox" && $(this).prop("type") !== "radio")) {
                    meta_val = $(this).val();
                    custom_fields[meta_key] = meta_val;
                  }
                }
              });
            }

          });
          custom_fields.eventer_tickets = tickets;
          custom_fields.eventer_booked_tickets = tickets_assoc;
          custom_fields.wceventer_tickets = woo_tickets;
        }
        else if (section_type !== 'tickets' && section_type !== 'eventer-venue' && section_type !== 'eventer-organizer') {
          $(this).find('.eventer_add_event_field').each(function () {
            var meta_key = $(this).attr('data-meta');
            if (meta_key === '' || meta_key === 'eventer_featured_image_url' || $(this).val() === '' || meta_key === 'eventer_featured_image_preview') {
              return true;
            }
            else if (meta_key === "eventer-category") {
              var eventer_cat = {};
              var eventer_cat_meta = {};
              var eventer_cat_checked_data = [];
              eventer_cat.term = $(this).val();
              eventer_cat.taxonomy = 'eventer-category';
              eventer_cat.meta = eventer_cat_meta;
              terms.push(eventer_cat);
            }
            else if (meta_key === "eventer-tag") {
              var eventer_cat = {};
              var eventer_cat_meta = {};
              var eventer_cat_checked_data = [];
              eventer_cat.term = $(this).val();
              eventer_cat.taxonomy = 'eventer-tag';
              eventer_cat.meta = eventer_cat_meta;
              terms.push(eventer_cat);
            }
            else if (meta_key === "title" || meta_key === "content") {
              custom_meta[meta_key] = $(this).val();
              return true;
            }
            else if (meta_key === "eventer_thumbnail_id" && $(this).val() !== '') {
              custom_meta.featured_media = $(this).val();
              return true;
            }
            else if (meta_key === "eventer_thumbnail_URL" && $(this).val() !== '') {
              custom_fields.eventer_featured_image_url = $(this).val();
              return true;
            }
            var meta_val = '';
            if (($(this).prop("type") == "checkbox" || $(this).prop("type") == "radio") && ($(this).is(':checked'))) {
              if (!(meta_key in custom_fields)) {
                checked_data = [];
              }

              if ($(this).is(':checked')) {
                checked_data.push($(this).val());
                custom_fields[meta_key] = checked_data;
              }

            }
            else if (($(this).prop("type") !== "checkbox" && $(this).prop("type") !== "radio")) {
              meta_val = $(this).val();
              custom_fields[meta_key] = meta_val;
            }
          });
        }
      });
      custom_meta.terms = terms;
      custom_meta.add_new_event = '1';
      //return false;

      eventer_id = (eventer_id === '') ? '' : eventer_id;
      event.preventDefault();
      var attachment_id = 0;
      var attachment_url = '';
      if ($(this).find('.eventer_set_featured_image').val()) {
        var file = jQuery('.eventer_set_featured_image')[0].files[0];
        var formData = new FormData();
        formData.append('file', file);
        formData.append('title', custom_meta.title);
        jQuery.ajax({
          url: ajaxval.root + 'wp/v2/media',
          method: 'POST',
          processData: false,
          contentType: false,
          data: formData,
          beforeSend: function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', ajaxval.nonce);
          },
        }).success(function (response) {
          $('.eventer_featured_image_preview').find('img').remove();
          $('.eventer_featured_image_preview').append('<img src="' + response.guid.raw + '">');
          attachment_id = response.id;
          attachment_url = response.guid.raw;
          custom_meta.featured_media = attachment_id;
          custom_fields.eventer_featured_image_url = attachment_url;
        }).error(function (response) {

        }).complete(function (response) {
          custom_meta.fields = custom_fields;
          eventer_submit_eventer(custom_meta, this_form, eventer_id);
        });
      }
      else {
        custom_meta.fields = custom_fields;
        eventer_submit_eventer(custom_meta, this_form, eventer_id);
      }

    });
    function eventer_update_URL(key, value) {

      var baseUrl = [location.protocol, '//', location.host, location.pathname].join(''),
        urlQueryString = document.location.search,
        newParam = key + '=' + value,
        params = '?' + newParam;

      // If the "search" string exists, then build params from it
      if (urlQueryString) {

        var updateRegex = new RegExp('([\?&])' + key + '[^&]*');
        var removeRegex = new RegExp('([\?&])' + key + '=[^&;]+[&;]?');

        if (typeof value == 'undefined' || value == null || value == '') { // Remove param if value is empty

          params = urlQueryString.replace(removeRegex, "$1");
          params = params.replace(/[&;]$/, "");

        } else if (urlQueryString.match(updateRegex) !== null) { // If param exists already, update it

          params = urlQueryString.replace(updateRegex, "$1" + newParam);

        } else { // Otherwise, add it to end of query string

          params = urlQueryString + '&' + newParam;

        }

      }
      return baseUrl + params;
    };
    window.eventer_send_email = function (eventer) {
      $.ajax({
        method: "POST",
        url: ajaxval.root + 'imithemes/email',
        data: { 'event': eventer },
        crossDomain: true,
        //contentType: 'application/json',
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', ajaxval.nonce);
        },
        success: function (response) {
          //alert("success");
        },
        error: function (response) {

        }
      });
    };
    window.eventer_submit_eventer = function (contents, this_form, eventer_id) {
      var event_id = '';
      $.ajax({
        method: "POST",
        url: ajaxval.root + 'wp/v2/eventer/' + eventer_id,
        data: contents,
        beforeSend: function (xhr) {
          this_form.find('.eventer-loader-wrap').show();
          xhr.setRequestHeader('X-WP-Nonce', ajaxval.nonce);
        },
      }).success(function (response) {
        event_id = response.id;
        if (ajaxval.load_scripts === '1') {
          window.location.href = window.location + "?fevent=" + response.id;
        }
        else {
          this_form.find('.eventer-set-form-fields').val(response.id);
          this_form.find('.eventer-submit-form-btn').val('Update');
          this_form.find('#section-message').empty();
          $('#section-message').show();
          var message_show = (eventer_id !== '') ? ajaxval.update_event_msg : ajaxval.add_event_msg;
          this_form.find('#section-message').append(message_show);
          $('html, body').animate({
            scrollTop: $("#section-message").offset().top
          }, 1000);
          //return false;
        }
      }).error(function (response) {
        $('#section-message').show();
        this_form.find('#section-message .eventer-row').append(response.responseJSON.message);
        $('html, body').animate({
          scrollTop: $("#section-message").offset().top
        }, 1000);
      }).complete(function (response) {
        this_form.find('.eventer-loader-wrap').hide();
        eventer_send_email($('.eventer-set-form-fields').val());
        return eventer_id;
      });
    }

    // Eventer Add New Event Form Front Actions
    $(document).on('submit', '.eventer_register_user', function (event) {
      event.preventDefault();
      var message = $(this).closest('.eventer-users-section').find('.message');
      var name = $(this).find('.eventer_register_username').val();
      if (name === '') {
        message.html();
        message.html('<div class="eventer-forms-info">' + ajaxval.enter_name + '</div>');
        return false;
      }
      var useremail = $(this).find('.eventer_register_email').val();
      var userpass1 = $(this).find('.eventer_register_pass1').val();
      var userpass2 = $(this).find('.eventer_register_pass2').val();
      if (useremail === '') {
        message.html();
        message.html('<div class="eventer-forms-info">' + ajaxval.enter_email + '</div>');
        return false;
      }
      if (userpass1 === '' || userpass2 === '') {
        message.html();
        message.html('<div class="eventer-forms-info">' + ajaxval.enter_pass + '</div>');
        return false;
      }
      if (userpass1 !== userpass2) {
        message.html();
        message.html('<div class="eventer-forms-info">' + ajaxval.repeat_pass + '</div>');
        return false;
      }
      var data = {
        username: name,
        email: useremail,
        password: userpass2,
      };
      $.ajax({
        method: "POST",
        url: ajaxval.root + 'imithemes/register',
        data: JSON.stringify(data),
        crossDomain: true,
        contentType: 'application/json',
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', ajaxval.nonce);
        },
        success: function (response) {
          message.html();
          message.html('<div class="eventer-forms-info">' + response.message + '</div>');
        },
        fail: function (response) {
          alert(ajaxval.failure);
        }

      });
    });

    $(document).on('submit', '.eventer_login_user', function (event) {
      event.preventDefault();
      var element = $(this);
      var message = $(this).closest('.eventer-users-section').find('.message');
      var name = $(this).find('.eventer_login_username').val();
      if (name === '') {
        message.html();
        message.html('<div class="eventer-forms-info">' + ajaxval.enter_name + '</div>');
        return false;
      }
      var userpass = $(this).find('.eventer_login_pass').val();
      if (userpass === '') {
        message.html();
        message.html('<div class="eventer-forms-info">' + ajaxval.enter_pass + '</div>');
        return false;
      }
      var data = {
        username: name,
        //email: useremail,
        password: userpass,
        nonce: frontForm.nonce,
      };
      $.ajax({
        method: "POST",
        url: ajaxval.root + 'imithemes/login',
        data: JSON.stringify(data),
        crossDomain: true,
        contentType: 'application/json',
        beforeSend: function (xhr) {
          element.find('.eventer-loader-wrap').show();
          xhr.setRequestHeader('X-WP-Nonce', ajaxval.nonce);
        },
        success: function (response) {
          element.find('.eventer-loader-wrap').hide();
          if (response.message === 1) {
            location.reload();
          }
          else if (response.message !== '') {
            element.closest('.eventer-users-section').find('.message').html('<div class="eventer-forms-info">' + response.message + '</div>');
          }
        },
        fail: function (response) {
          element.find('.eventer-loader-wrap').hide();
          alert(ajaxval.failure);
        }

      });
    });
    $(document).on('click', '#resend-reset', function () {
      $(this).attr('data-val', '1');
      $(this).closest('form.eventer_reset_password').submit();
    });

    $(document).on('submit', '.eventer_reset_password', function (event) {
      event.preventDefault();
      var element = $(this);
      var form_area = $(this).find('.eventer-form-fields-area');
      var message = $(this).closest('.eventer-users-section').find('.message');
      var name = $(this).find('.eventer_reset_username').val();
      var verification = $(this).find('.eventer_reset_verification').val();
      var pass1 = $(this).find('.eventer_reset_pass1').val();
      var pass2 = $(this).find('.eventer_reset_pass2').val();
      var resend_verify = $(this).find('#resend-reset').attr('data-val');
      if (name === '') {
        message.text();
        message.text(ajaxval.enter_name);
        return false;
      }
      if ($(this).find('.eventer_reset_verification').length > 0 && verification === '' && resend_verify !== '1') {
        message.text();
        message.text(ajaxval.enter_verify);
        return false;
      }
      if ($(this).find('.eventer_reset_pass1').length > 0 && (pass1 === '' || pass2 === '' || pass1 !== pass2) && resend_verify !== '1') {
        message.text();
        message.text(ajaxval.enter_pass);
        return false;
      }
      var data = {
        username: name,
        verification: verification,
        password: pass2,
        resend: resend_verify,
      };
      $.ajax({
        method: "POST",
        url: ajaxval.root + 'imithemes/reset',
        data: JSON.stringify(data),
        crossDomain: true,
        contentType: 'application/json',
        beforeSend: function (xhr) {
          element.find('.eventer-loader-wrap').show();
          xhr.setRequestHeader('X-WP-Nonce', ajaxval.nonce);
        },
        success: function (response) {
          element.find('.eventer-loader-wrap').hide();
          if (response.message === 1) {
            $('.eventer_reset_fields_area').empty();
            message.text();
            form_area.find('.eventer_reset_fields_area').append(response.second);
            //$('.eventer-dynamic-counter').find('.eventer-reset-counter').hide();
            $('.eventer-dynamic-counter').append('<span id="' + response.random + '" class="eventer-reset-counter"></span>');
            var counter = 0;
            var interval = setInterval(function () {
              counter++;
              $('.eventer-dynamic-counter').find('span#' + response.random).text((parseInt(response.seconds) - parseInt(counter)) + ' seconds left');
              if (parseInt(counter) >= parseInt(response.seconds)) {
                clearInterval(interval);
                $('.eventer_reset_fields_area').empty();
              }
            }, 1000);
          }
          else if (response.message === 2) {
            $('.eventer-verification-code-timer').empty();
            message.text();
            form_area.find('.eventer_reset_fields_area').append(response.second);
          }
          else if (response.message === 3) {
            message.html();
            message.html('<div class="eventer-forms-info">' + response.second + '</div>');
          }
          else if (response.message === 4) {
            message.html();
            message.html('<div class="eventer-forms-info">' + response.second + '</div>');
          }
        },
        fail: function (response) {
          alert(ajaxval.failure);
        }

      });
      return false;
    });

    window.eventer_form_autocomplete_fields = function () {
      if ($('.eventer_autocomplete_values').length <= 0) return true;
      var autocomplete_terms = JSON.parse($('.eventer_autocomplete_values').val());
      var autocomplete_tickets = autocomplete_terms.tickets;
      var autocomplete_organizer = autocomplete_terms.organizer;
      var autocomplete_venue = autocomplete_terms.venue;

      $('.eventer_ticket_name').autocomplete({
        source: autocomplete_tickets,
        minLength: 1,
        select: function (event, ui) {
          event.preventDefault();
          $(this).val(ui.item.label);
          var element = $(this);
          $.map(ui.item.value, function (value, index) {
            if (index === 'number') {
              element.closest('.eventer-row').find('.eventer_ticket_quantity').val(value);
            }
            else if (index === 'price') {
              element.closest('.eventer-row').find('.eventer_ticket_price').val(value);
            }
            else if (index === 'pid' && typeof index !== 'undefined') {
              element.closest('.eventer-row').append('<input type="hidden" class="eventer_ticket_pid" value="' + value + '">');
              //element.closest('.eventer-row').find('.eventer_ticket_name').prop('disabled', true);
            }

          });
        }
      });
      $('.eventer-organizer').autocomplete({
        source: autocomplete_organizer,
        minLength: 1,
        select: function (event, ui) {
          event.preventDefault();
          $(this).val(ui.item.label);
          var element = $(this);
          $.map(ui.item.value, function (value, index) {
            element.closest('.eventer_dynamic_section_area').find('[data-meta="' + index + '"]').val(value);
          });
        }
      });
      $('.eventer-venue').autocomplete({
        source: autocomplete_venue,
        minLength: 1,
        select: function (event, ui) {
          event.preventDefault();
          $(this).val(ui.item.label);
          var element = $(this);
          $.map(ui.item.value, function (value, index) {
            element.closest('.eventer_dynamic_section_area').find('[data-meta="' + index + '"]').val(value);
            //element.closest('.eventer_dynamic_section_area').find('[data-meta="'+index+'"]').prop('disabled', true);
          });
        }
      });
    };
    eventer_form_autocomplete_fields();
  });
});