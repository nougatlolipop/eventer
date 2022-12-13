jQuery(function ($) {
  "use strict";

  function disableDays(date) {
    var pad = function (value) {
      return (value < 10 ? '0' : '') + value;
    };
    var sDate = date.getFullYear() + "-" + pad(date.getMonth() + 1) + "-" + pad(date.getDate());
    $('#ui-datepicker-div').addClass('eventer-custom-ui-datepicker');
    return [$.inArray(sDate, single.enabled_date) !== -1, 'highlight'];

  }

  function eventer_match_height() {
    $('.equah').each(function () {
      $(this).find('.equah-item').matchHeight();
    });
  }
  if (single.stripe_switch == '1' && single.woo_payment_switch !== 'on') {
    //Stripe.setPublishableKey(single.stripe_publishable_key);
    var stripe = Stripe(single.stripe_publishable_key); // test publishable API key
    var elements = stripe.elements();

    var card = elements.create('card', {
      hidePostalCode: true,
      style: {
        base: {
          iconColor: '#666EE8',
          color: '#31325F',
          lineHeight: '40px',
          fontWeight: 300,
          fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
          fontSize: '15px',

          '::placeholder': {
            color: '#CFD7E0',
          },
        },
      }
    });
    // Add an instance of the card UI component into the `card-element` <div>
    if ($('#card-element').length) {
      card.mount('#card-element');
    }
    // Handle events and errors
    card.addEventListener('change', function (event) {
      var displayError = document.getElementById('card-errors');
      if (event.error) {
        displayError.textContent = event.error.message;
      } else {
        displayError.textContent = '';
      }
    });
  }

  function stripeTokenHandler(token) {
    // Insert the token ID into the form so it gets submitted to the server
    var form = document.getElementById('ticket-reg');
    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'stripeToken');
    hiddenInput.setAttribute('value', token.id);
    var form$ = $(".ticket-reg");
    form$.append("<input type='hidden' class='eventer-stripe-token' name='stripeToken' value='" + token.id + "' />");
    $('.save-registrant').trigger('click');
  }

  function createToken(e) {
    e.preventDefault();
    let custData = {
      name: $("#reg_email").val(),
      /*address_line1: '21 Great Street',
      address_line2: 'Shilloong',
      address_city: 'Chicago',
      address_state: 'Illinois',
      address_zip: '12345',
      address_country: 'US'*/
    };

    stripe.createToken(card, custData).then(function (result) {
      if (result.error) {
        // Inform the user if there was an error
        var errorElement = document.getElementById('card-errors');
        errorElement.textContent = result.error.message;
      } else {
        // Send the token to your server
        stripeTokenHandler(result.token);
      }
    });
  }

  $(document).on('click', '.eventer-stripe-trigger', function (e) {
    if (single.stripe_switch == '1' && single.woo_payment_switch !== 'on') {
      createToken(e);
      let token_val = $('.eventer-stripe-token').val();
      if (typeof token_val != 'undefined' && token_val != '') {
        $(this).removeClass('eventer-stripe-trigger');
      }
    }
  })

  function APPENDQUERYURL(key, value, url) {
    if (!url) { url = window.location.href; }
    var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
      hash;
    if (re.test(url)) {
      if (typeof value !== 'undefined' && value !== null) {
        return url.replace(re, '$1' + key + "=" + value + '$2$3');
      } else {
        hash = url.split('#');
        url = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');
        if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
          url += '#' + hash[1];
        }
        return url;
      }
    } else {
      if (typeof value !== 'undefined' && value !== null) {
        var separator = url.indexOf('?') !== -1 ? '&' : '?';
        hash = url.split('#');
        url = hash[0] + separator + key + '=' + value;
        if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
          url += '#' + hash[1];
        }
        return url;
      } else { return url; }

    }
  }

  function CHECKBOXVALIDATE(names, fields) {
    var chkds = $("input[name='" + names + "']:" + fields);
    if (chkds.is(":checked")) {
      return "1";
    } else {
      return "0";
    }
  }

  function VALIDATEEMAIL(emailField) {
    var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,8})$/;

    if (reg.test(emailField.val()) === false) {
      return "0";
    }
    return "1";

  }
  //Load dynamic booking area on single details page
  function eventer_dynamic_bookings(dateText) {
    var event_time = $('.eventer-time-slot :selected').val();
    $('.eventer-ticket-details').attr('data-date', dateText);
    $('.eventer-ticket-details').attr('data-time', event_time);
    var request = $.ajax({
      url: single.ajax_url,
      type: "post",
      dataType: 'json',
      data: {
        action: 'eventer_dynamic_ticket_area',
        date: dateText,
        time: event_time,
        event: single.dynamic_event,
      },
      beforeSend: function (xhr) {
        $('.eventer-loader-form').show();
      },
    });
    request.done(function (response) {
      $('.eventer-event-date').html('<i class="eventer-icon-calendar"></i>' + response.formatted);
      //$('.eventer-front-ticket-area-dynamic').empty();
      $('.eventer-ticket-details-wrap').replaceWith(response.tickets);
      $('.eventer_ticket-filter').replaceWith(response.tickets_modal);
      $('#reg_event_date').text(response.date);
      $('#reg_event_time').text(response.time);
      $('#paypal_return').text(response.event_url);
      var metas = $('.eventer-single-event-details');
      metas.empty();
      metas.removeClass('eventer eventer-event-single eventer-single-event-details');
      metas.html(response.metas);
      $('#eventer-future-bookings').datepicker({
        showOn: "button",
        maxDate: new Date(single.max_date),
        dateFormat: 'dd-mm-yy',
        buttonText: response.date_show,
        defaultDate: new Date(response.date),
        beforeShowDay: disableDays,
        onSelect: function (dateText, inst) {
          eventer_dynamic_bookings(dateText);
        }
      });
      $('.eventer-loader-form').hide();
      $('.eventer-time-slot').val(event_time);
    });
  }

  function eventer_action_print_button(elem) {
    var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    var mywindow = window.open('', 'PRINT', 'height=400,width=600');
    mywindow.document.write('<html><head><title>' + document.title + '</title>');

    mywindow.document.write('<link rel="stylesheet" href="' + single.eventer_style + '" type="text/css" />');
    mywindow.document.write('</head><body class="eventer eventer-print-window">');
    mywindow.document.write('<h1>' + document.title + '</h1>');

    mywindow.document.write(document.getElementById(elem).innerHTML);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10*/

    mywindow.print();
    if (isSafari) {

    } else {
      mywindow.close();
    }

    return true;
  }

  function VALIDATEFORMFIELDS(element, counting) {

    var passing = "1";
    var total_counting = counting;
    var required = element.attr("data-required");
    if (required === "1") {
      var field_type = element.prop("type");
      if (field_type === "text" || field_type === "textarea" || field_type === "email" || field_type === "number") {
        if (element.val() === '') {
          element.addClass("eventer-required-field");
          passing = "0";
          total_counting = total_counting + 1;
        } else if (field_type === "email" && VALIDATEEMAIL(element) === "0") {
          element.addClass("eventer-required-field");
          element.closest("form").find(".message").empty();
          element.closest("form").find(".message").append(element.attr("name") + ' <p>' + single.email_msg + '</p>');
          passing = "0";
          total_counting = total_counting + 1;
        } else if (field_type === "number" && !$.isNumeric(element.val())) {
          element.addClass("eventer-required-field");
          element.closest("form").find(".message").empty();
          element.closest("form").find(".message").append(element.attr("name") + ' <p>' + single.number_msg + '</p>');
          passing = "0";
          total_counting = total_counting + 1;
        } else {
          element.removeClass("eventer-required-field");
          passing = "1";
        }
      } else if (field_type === "checkbox" || field_type === "radio") {
        var fname = element.attr("name");
        var result = CHECKBOXVALIDATE(fname, element.prop("type"));
        if (result === "1") {
          element.closest("div").removeClass("eventer-required-field");
          passing = "1";
        } else {
          element.closest("div").addClass("eventer-required-field");
          passing = "0";
          total_counting = total_counting + 1;
        }
      } else {
        if (element.val() === '') {
          element.addClass("eventer-required-field");
          passing = "0";
          total_counting = total_counting + 1;
        } else {
          element.removeClass("eventer-required-field");
          passing = "1";

        }
      }
    }
    if (counting === '') {
      return passing;
    } else {
      return total_counting;
    }

  }
  $(document).ready(function () {
    eventer_match_height();

    //Code to change booking area on single page while changing time slot
    $(document).on('change', '.eventer-time-slot', function () {
      eventer_dynamic_bookings($('.eventer-ticket-details').attr('data-date'));
    });

    $('#eventer-future-bookings').datepicker({
      showOn: "button",
      maxDate: new Date(single.max_date),
      buttonText: single_meta.future_date_cal,
      dateFormat: 'dd-mm-yy',
      beforeShowDay: disableDays,
      defaultDate: new Date(single.min_date),
      onSelect: function (dateText, inst) {
        eventer_dynamic_bookings(dateText);
      }
    });

    $(document).on('click', '.eventer-toggle-area-trigger', function (e) {
      var dialog_heading = $(this).attr('data-eventer-dialog-heading');
      if (typeof dialog_heading !== 'undefined' && dialog_heading !== '') {
        $(this).closest('.eventer-modal').find('.eventer-section-wise-heading').text(dialog_heading);
      }
      var targetIn = $(this).attr('data-eventer-toggle-in');
      var targetOut = $(this).attr('data-eventer-toggle-out');
      $(this).parents('.eventer-toggle-area').find(targetOut).slideUp('fast');
      $(this).parents('.eventer-toggle-area').find(targetIn).slideDown('slow');
      e.preventDefault();
    });




    if (single.register_status !== "" && typeof single.register_status !== 'undefined') {
      $("#eventer-ticket-confirmation").emodal();
    }

    $(".eventer-get-ticket-modal").click(function (event) {
      $.emodal.close();
      $("#eventer-ticket-show-now").emodal();
    });

    $(document).on('mouseenter mouseleave', '.eventer-event-save', function () {
      $(this).find('ul').toggle();
    });

    $('.eventer-print-ticket').click(function () {
      eventer_action_print_button('eventer-ticket-show-now');
    });

    $("form.organizer-contact").on("submit", function (e) {
      var form_element = $(this);
      var validated = "1";
      $(this).closest("form").find(".message").empty();
      var all_fields = $(this).serializeArray();
      var eventer_id = $(".organizer-details").text();
      var eventer_date = $(".eventer-date").text();
      $('form.organizer-contact *').filter(':input').each(function () {
        var passing = VALIDATEFORMFIELDS($(this), '');
        if (passing !== "1") {
          validated = "0";
        }
      });
      if (validated !== "1") {
        return false;
      }

      var request = $.ajax({
        url: single.ajax_url,
        type: "post",
        async: false,
        data: {
          action: 'eventer_contact_organizer',
          org_data: all_fields,
          eventer_id: eventer_id,
          eventer_date: eventer_date,
          nonce: single.organizer_contact,
        },
        beforeSend: function (xhr) {
          form_element.find("input[type=submit]").prop('disabled', true);
        },
      });
      request.done(function (response) {
        form_element.find("input[type=submit]").prop('disabled', false);
        form_element.find(".message").html(single.contact_manager_text);
        $('#eventer-contact-form').slideUp(1500);
        $('.eventer-modal-close').trigger('click');
      });
      request.fail(function (jqXHR, textStatus, errorThrown) {
        alert(errorThrown);
      });
      e.preventDefault();
    });

    $(document).on('submit', 'form.ticket-reg', function (e) {
      var booking_type = $(this).attr('data-booking');
      var closest_booking = $('.eventer-front-ticket-area-dynamic');
      var booking_nonce = $(this).attr('data-nonce');
      $('.eventer-removable-field-bypermission').each(function () {
        if ($(this).css('visibility') == 'hidden' || $(this).css('display') == 'none') {
          $(this).remove();
        }
      });
      if ($(this).hasClass("eventer-direct-register") || single.mandatory_registrants === 'on') {
        var validated = "1";
        $(closest_booking).find('form.ticket-reg *').filter(':input').each(function () {
          if ($(this).hasClass('eventer-stripe-fields')) {
            return true;
          }
          var passing = VALIDATEFORMFIELDS($(this), '');
          if (passing !== "1") {
            validated = "0";
          }
        });
        if (validated !== "1") {
          return false;
        }
      }
      var form_element = $(this);
      var cart_status = $(form_element).find('.eventer-row').attr('data-cart');
      var total_price_ticket = $(closest_booking).find(".eventer-ticket-price-total").attr('data-fprice');
      var $all_fields = $(this).find(':input').not('.registrant-fields').serializeArray();
      var all_registrants_list = {};
      $(closest_booking).find('.eventer-registrants-row').each(function () {
        var ticket_name_reg = $(this).attr('data-ticket');
        var registrants_list = [];
        $(this).find('.eventer-registrants-list').each(function () {
          var registrants_field_list = {};
          var registrant_name = $(this).find('.eventer_registrant_name').val();
          var registrant_email = $(this).find('.eventer_registrant_email').val();
          if (registrant_name === '') return true;
          registrants_field_list.name = registrant_name;
          registrants_field_list.email = registrant_email;
          registrants_list.push(registrants_field_list);
        });
        all_registrants_list[ticket_name_reg] = registrants_list;
      });

      var registration;
      var Tickets = [];
      var Ticket_Validation = [];
      var service = [];
      $(closest_booking).find('.services-section').each(function () {
        var pid = $(this).closest('.eventer-ticket-type-row').attr('data-pid');
        var cost = $(this).closest('.eventer-ticket-type-row').find('.total-price').attr('data-mprice');
        var services = {};
        var service_vals = '';
        services.name = $(this).closest('.eventer-ticket-type-row').find('.name-ticket').text();
        $(this).find('.add_services').each(function () {
          if ($(this).is(':checked')) {
            service_vals += $(this).val() + ', ';
          }
        });
        services.value = service_vals;
        services.pid = pid;
        services.cost = cost;
        service.push(services);
      });
      $(closest_booking).find(".num-tickets").each(function () {

        var $crow = $(this).closest(".eventer-ticket-type-row");
        var data = {};
        var ticket_price = '0';
        var ticket_name_new = $crow.find(".name-ticket").text();
        if ($(this).val() !== '') {
          ticket_price = $crow.find(".price-ticket").attr('data-tprice');
          Ticket_Validation.push(data);
        }
        if (ticket_name_new !== '') {
          data.name = $crow.find(".name-ticket").text();
          data.number = $crow.find(".num-tickets").val();
          data.pid = $crow.find('.eventer-row').attr('data-pid');
          data.wpid = $crow.find('.eventer-row').attr('data-wpid');
          data.id = $crow.find('.eventer-row').attr('data-ticketid');
          data.price = ticket_price;
          Tickets.push(data);
        }

      });
      if (Ticket_Validation.length === 0) {
        $(closest_booking).find('form.ticket-reg').find(".message").empty();
        $(closest_booking).find('form.ticket-reg').find(".message").html(single.blank_tickets);
        e.preventDefault();
        return false;
      } else if ($(closest_booking).find('input:radio:checked').length <= 0 && total_price_ticket > 0 && booking_type != 'woo') {
        $(closest_booking).find('form.ticket-reg').find(".message").empty();
        $(closest_booking).find('form.ticket-reg').find(".message").html(single.blank_payment);
        e.preventDefault();
        return false;
      } else {
        $(closest_booking).find('form.ticket-reg').find(".message").empty();
        if (single.offline_switch !== "1") {
          //$(closest_booking).find(".chosen-payment-option").prop("checked", true);
        }
      }
      var paypal_arguments = '';
      //var dotpay_arguments = '';
      var stripe_arguments = '';
      var eventer_cred = {};
      if ($(closest_booking).find("form#ticket-reg").attr("action").indexOf("paypal") > -1) {
        paypal_arguments = '<input type="hidden" name="business" value="' + single.paypal_email + '">';
        paypal_arguments += '<input type="hidden" name="cmd" value="_xclick">';
        paypal_arguments += '<input type="hidden" name="on0" value="">';
        paypal_arguments += '<input type="hidden" name="on1" value="' + $(closest_booking).find("#reg_email").val() + '">';
        paypal_arguments += '<input type="hidden" name="item_name" value="' + $(closest_booking).find("#paypal_itemname").text() + '">';
        paypal_arguments += '<input type="hidden" name="item_number" value="' + $(closest_booking).find("#eventer_id").text() + '">';
        paypal_arguments += '<input type="hidden" name="amount" value="' + $(closest_booking).find(".eventer-ticket-price-total").attr('data-fprice') + '">';
        paypal_arguments += '<input type="hidden" name="currency_code" value="' + single.paypal_curr + '">';
        paypal_arguments += '<input type="hidden" name="cancel_return" value="' + $("#paypal_return").text() + '">';
        paypal_arguments += '<input type="hidden" name="return" value="' + $(closest_booking).find("#paypal_return").text() + '">';
        $(closest_booking).find(".save-registrant").before(paypal_arguments);
      } else if ($(closest_booking).find("form#ticket-reg").attr("action").indexOf("dotpay") > -1) {
        booking_type = 'dotpay';
      }
      //return false;
      if ($('.eventer_stripe_field').is(':visible')) {
        booking_type = 'stripe';
        eventer_cred = { 'token': $('.eventer-stripe-token').val() };
      }
      if ($(closest_booking).find('#register-reg').is(":checked")) {
        registration = 1;
      }
      var amount = parseFloat($(closest_booking).find(".eventer-ticket-price-total").attr('data-fprice'));
      var request = $.ajax({
        url: single.ajax_url,
        type: "post",
        dataType: 'json',
        //async: false,
        data: {
          action: 'eventer_registrant_tickets',
          reg_data: $all_fields,
          eventer_id: $(closest_booking).find("#eventer_id").text(),
          amount: amount,
          reg_mail: $(closest_booking).find("#reg_email").val(),
          reg_name: $(closest_booking).find("#reg_name").val(),
          cart_status: cart_status,
          reg_event_date: $(closest_booking).find("#reg_event_date").text(),
          reg_event_time: $(closest_booking).find("#reg_event_time").text(),
          reg_event_slot: $(closest_booking).find("#reg_event_slot_name").text(),
          card_cred: eventer_cred,
          tickets: Tickets,
          registration: registration,
          services: service,
          registrants: all_registrants_list,
          book_type: booking_type,
          //nonce: booking_nonce,
        },
        beforeSend: function (jqXhr) {
          $('body').addClass('woocommerce-checkout');
          jqXhr.setRequestHeader('X-WP-Nonce', booking_nonce);
          form_element.find("input[type=submit]").prop('disabled', true);
          form_element.find('.eventer-loader-wrap').show();
        },
      });
      request.done(function (response) {
        e.preventDefault();
        if (response.reg_invalid == '1') {
          form_element.find('.eventer-loader-wrap').hide();
          form_element.find("input[type=submit]").prop('disabled', false);
          $('.message').empty();
          $('.message').html('<br/><div class="eventer-ticket-full">You already have booked the ticket for ' + response.ticket_name + '</div>');
          return false;
        }
        if (response.reg) {
          var form_action = $(closest_booking).find("form.ticket-reg").attr("action");
          if (form_action.indexOf("paypal") <= -1) {
            var newformaction = APPENDQUERYURL('reg', response.reg, form_action);
            form_action = newformaction;
            $(closest_booking).find("form.ticket-reg").attr("action", form_action);

          }
          var paypal_return = $(closest_booking).find("#paypal_return").text();
          var new_paypal_return = APPENDQUERYURL('reg', response.reg, paypal_return);
          $(closest_booking).find("input[name=return]").val(new_paypal_return);
          $(closest_booking).find("input[name=on0]").val(response.reg);
        }
        form_element.find("input[type=submit]").prop('disabled', false);
        form_element.find('.eventer-loader-wrap').hide();

        if (booking_type === 'woo') {
          closest_booking.find('.eventer-order-summary-added').remove();
          $('<div class="eventer-order-summary-added widget woocommerce widget_shopping_cart">' + response.woo + '</div>').insertBefore(closest_booking.find('.eventer-woo-checkout-section'));
          $(closest_booking).find(".eventer-show-order-summary").trigger("click");
          jQuery(document.body).trigger("update_checkout");
          $(closest_booking).find(".showcoupon").click(function () {
            $(closest_booking).find(".checkout_coupon").slideToggle();
            return false;
          });
          $(closest_booking).find(".showlogin").click(function () {
            $(closest_booking).find(".woocommerce-form-login").slideToggle();
            return false;
          });
          form_element.find('.eventer_dynamic_checkout_payment').show();
          e.preventDefault();
        } else {
          if (booking_type == 'stripe' && response.secret !== '') {
            stripe.handleCardAction(
              response.secret
            ).then(function (result) {
              if (result.error) {
                document.getElementById("payment-errors").textContent = result.error.message;
              } else {
                $.ajax({
                  url: single.ajax_url,
                  type: "post",
                  dataType: 'json',
                  data: {
                    action: 'eventer_confirm_payment_stripe',
                    secret: result.paymentIntent.id,
                    reg_id: response.reg_id
                  },
                  success: function (response) {
                    e.preventDefault();
                    var form_action = $(closest_booking).find("form.ticket-reg").attr("action");
                    var newformaction = APPENDQUERYURL('reg', response.reg, form_action);
                    form_action = newformaction;
                    $(closest_booking).find("form.ticket-reg").attr("action", form_action);
                    $(closest_booking).find("form.ticket-reg").addClass("new-ticket-reg");
                    $(closest_booking).find("form.ticket-reg").removeClass("ticket-reg");
                    $(closest_booking).find('form.new-ticket-reg').submit();
                  }
                });
              }
            });
            return false;
          } else {
            e.preventDefault();
            $(closest_booking).find("form.ticket-reg").addClass("new-ticket-reg");
            $(closest_booking).find("form.ticket-reg").removeClass("ticket-reg");
            $(closest_booking).find('form.new-ticket-reg').submit();
          }
        }
      });
      request.fail(function (jqXHR, textStatus, errorThrown) {
        form_element.find('.eventer-loader-wrap').hide();
        form_element.find("input[type=submit]").prop('disabled', false);
        $('.message').empty();
        $('.message').html('<br/><div class="eventer-ticket-full">There is some error</div>');
      });
      return false;
    });

    $(document).on("click", ".validate-registrant", function (e) {
      e.preventDefault();
      var validated = "1";
      var closest_booking = $('.eventer-front-ticket-area-dynamic');
      $(closest_booking).find('form.ticket-reg *').filter(':input').each(function () {
        if ($(this).is(":hidden")) {
          return true;
        }
        var passing = VALIDATEFORMFIELDS($(this), '');
        if (passing !== "1") {
          validated = "0";
        }
      });
      if (validated !== "1") {
        return false;
      }
      $(closest_booking).find('.restricted-row').each(function () {
        var ticket_cookie = $(this).attr('data-booked');
        if (ticket_cookie !== "") {
          $(this).find('.num-tickets').prop('disabled', true);
          $(this).find('.eventer-qtyplus').prop('disabled', true);
          $(this).find('.eventer-qtyminus').prop('disabled', true);
          $(this).find('.eventer-restricted-msg').show();
        } else {
          $(this).find('.num-tickets').prop('disabled', false);
          $(this).find('.eventer-qtyplus').prop('disabled', false);
          $(this).find('.eventer-qtyminus').prop('disabled', false);
          $(this).find('.eventer-restricted-msg').hide();
        }
      });
      $(closest_booking).find("#fname").removeClass("eventer-input-error");
      $(closest_booking).find("#reg_email").removeClass("eventer-input-error");
      $(closest_booking).find('form.ticket-reg').find(".message").empty();
      var targetIn = $(this).attr('data-eventer-toggle-in');
      var targetOut = $(this).attr('data-eventer-toggle-out');
      $(this).parents('.eventer-toggle-area').find(targetOut).slideUp('fast');
      $(this).parents('.eventer-toggle-area').find(targetIn).slideDown('slow');
      if (single.minimum_ticket != '') {
        $('.num-tickets').val(single.minimum_ticket);
        $('.num-tickets').trigger('keyup');
      }
    });

    $(document).on('change', ".add_services", function () {
      var closest_booking = $('.eventer-front-ticket-area-dynamic');
      var have_ticket;
      $(closest_booking).find(".num-tickets").each(function () {
        if ($(this).val() !== '0') {
          have_ticket = 1;
        }
      });
      var $crow = $(this).closest(".eventer-ticket-type-row");
      var clist = $(this).closest("li");
      var price = clist.attr('data-tprice');
      var total_price_services = 0;
      var check_status;
      var total_price_ticket = 0;
      if ($(this).is(':radio')) {
        $crow.find(".price-ticket").attr('data-mprice', 0);
      }
      if ($(this).is(':checked')) {
        clist.attr('data-mprice', price);
        check_status = "1";
      } else {
        clist.attr('data-mprice', 0);
        check_status = "0";
      }
      if ($(closest_booking).find(".restrict-service").length && !$(closest_booking).find('.restrict-service-field').is(':checked')) {
        check_status = "2";
      } else {
        check_status = "1";
      }
      $crow.find(".price-ticket").each(function () {
        var service_price = ($(this).attr("data-mprice") !== '') ? $(this).attr("data-mprice") : 0;
        total_price_services = total_price_services + parseFloat(service_price);
      });
      var set_price_individual_service = (single.curr_position === "postfix") ? total_price_services + single.curr : single.curr + total_price_services;
      $crow.find(".total-price").text(set_price_individual_service);
      $crow.find(".total-price").attr('data-mprice', total_price_services);
      $(closest_booking).find(".total-price").each(function () {
        var $one_price = ($(this).attr("data-mprice") !== '') ? $(this).attr("data-mprice") : 0;
        total_price_ticket = total_price_ticket + parseFloat($one_price);
      });
      var set_price = (single.curr_position === "postfix") ? total_price_ticket + single.curr : single.curr + total_price_ticket;
      $(closest_booking).find(".eventer-ticket-price-total").text(set_price);
      $(closest_booking).find(".eventer-ticket-price-total").attr('data-fprice', total_price_ticket);
      $(closest_booking).find(".eventer-ticket-price-total").attr('data-aprice', total_price_ticket);
      $(closest_booking).find(".payment-options-area").hide();
      if (total_price_ticket > 0) {
        $(closest_booking).find('.save-registrant').prop('disabled', false);
        $(closest_booking).find('.add_services_btn').removeClass('disableClick');
        if (check_status === "2") {
          $(closest_booking).find('.save-registrant').prop('disabled', true);
          $(closest_booking).find('.add_services_btn').removeClass('disableClick');
        }
        //$(closest_booking).find("form#ticket-reg").attr('action', single.paypal_site);
        $(closest_booking).find(".payment-options-area").show();
        //$(closest_booking).find(".offline_message").hide();
        //$(closest_booking).find(".save-registrant").val(single.paypal_proceed);
        $(closest_booking).find(".save-registrant").attr("data-payment", "1");
        $(closest_booking).find('form.ticket-reg').find(".message").empty();

        var checked_payment = $('.chosen-payment-option:checked').val();
        if (checked_payment === '1') {
          $(closest_booking).find("form#ticket-reg").attr('action', single.paypal_site);
          $(closest_booking).find(".save-registrant").val(single.paypal_proceed);
        } else if (checked_payment === '2') {
          $(closest_booking).find("form#ticket-reg").attr('action', $("#paypal_return").text());
          $(closest_booking).find(".save-registrant").val(single.stripe_proceed);
          $('.save-registrant').addClass('eventer-stripe-trigger');
        }

        if (single.offline_switch !== "1" && single.woo_payment_switch !== 'on') {
          //$(closest_booking).find(".chosen-payment-option").prop("checked", true);
        } else {
          //$(closest_booking).find("input[name=chosen-payment-option][value='0']").prop("checked", true);
          //$(closest_booking).find(".offline_message").show();
          $(closest_booking).find(".save-registrant").val(single.proceed_register);
          $(closest_booking).find(".save-registrant").attr("data-payment", "");
          $(closest_booking).find('form.ticket-reg').find(".message").empty();
          $(closest_booking).find("form#ticket-reg").attr('action', $("#paypal_return").text());
        }
      } else if (have_ticket === 1 && check_status !== "2") {
        $(closest_booking).find('.save-registrant').prop('disabled', false);
        $(closest_booking).find('.add_services_btn').removeClass('disableClick');
      } else {
        $(closest_booking).find('.save-registrant').prop('disabled', true);
        $(closest_booking).find('.add_services_btn').removeClass('disableClick');
      }
    });

    $('.add_services_btn').click(function () {
      if (single.mandatory_registrants == 'on') {
        var validated = "1";
        $('form.ticket-reg *').filter(':input').each(function () {
          if ($(this).hasClass('eventer-stripe-fields')) {
            return true;
          }
          var passing = VALIDATEFORMFIELDS($(this), '');
          if (passing !== "1") {
            validated = "0";
          }
        });
        if (validated !== "1") {
          return false;
        }
      }

      var closest_booking = $('.eventer-front-ticket-area-dynamic');
      if ($(closest_booking).find(".restrict-service").length && !$(closest_booking).find('.restrict-service-field').is(':checked')) {
        $(closest_booking).find('.save-registrant').prop('disabled', true);
      }
    });

    $(document).on('keyup mouseup', ".num-tickets, .eventer-qtyminus, .eventer-qtyplus", function () {
      var $crow = $(this).closest(".eventer-ticket-type-row");
      $('.eventer-coupon-validate').removeClass('eventer-coupon-validated eventer-coupon-invalid');
      $('.eventer-coupon-validate').removeAttr('disabled');
      var limit_event_count = parseInt(single.event_tickets_set);
      if ($crow.attr('data-limit') == "yes") {
        limit_event_count = 1;
      }
      var closest_booking = $('.eventer-front-ticket-area-dynamic');
      //$crow.append(registrant_fields);
      var remaining_tickets = $crow.find('.remaining-ticket').text();
      var set_remaining = (remaining_tickets >= limit_event_count) ? limit_event_count : remaining_tickets;
      var price = $crow.find(".price-ticket").attr('data-tprice');
      var number = parseInt($crow.find(".num-tickets").val());
      var calculation;
      var have_ticket;
      if ($(this).val() === '-' || $(this).val() === '+') {
        if (!isNaN(number)) {
          if ($(this).val() === '-' && number > 0) {
            calculation = $crow.find('.num-tickets').val(number - 1);
          } else if ($(this).val() === '+') {
            calculation = $crow.find('.num-tickets').val(number + 1);
            if (parseInt($crow.find('.num-tickets').val()) > parseInt(set_remaining)) {
              $crow.find('.num-tickets').val(set_remaining);
              //calculation = 10;
            }
          }
        } else {
          calculation = $crow.find('.num-tickets').val(0);
        }
      }
      if (single.optional_tickets === 'optional') {
        //Code start for optional tickets feature 2019-07-19
        if ($(this).closest('.eventer-q-field').find('input').val() > 0) {
          $crow.addClass('eventer-optional-set');
          $crow.closest('.eventer-ticket-step2').find('.eventer-ticket-type-row').each(function () {
            if ($(this).hasClass('eventer-optional-set')) {

            } else {
              $(this).addClass('eventer-ticket-type-restriction');
              $(this).find('.eventer-q-field').hide();
            }

          });
        } else {
          $crow.closest('.eventer-ticket-step2').find('.eventer-ticket-type-row').removeClass('eventer-optional-set');
          $crow.closest('.eventer-ticket-step2').find('.eventer-ticket-type-row').removeClass('eventer-ticket-type-restriction');
          $crow.closest('.eventer-ticket-step2').find('.eventer-ticket-type-row .eventer-q-field').show();
        }

        //Code of optional tickets ending here
      }

      var total_tickets = $crow.find(".num-tickets").val();
      if (parseInt(total_tickets) > parseInt(set_remaining)) {
        $crow.find(".num-tickets").val(set_remaining);
      }
      var new_total_tickets = $crow.find(".num-tickets").val();
      var registrants_fields_set = '';
      var registrants_boxes = $crow.find('.eventer-registrants-row').length;
      var registrants_boxes_ind = $crow.find('.eventer-registrants-row').find('.eventer-registrants-list').length;
      var registrant1_name = $(closest_booking).find('#reg_name').val();
      var registrant1_email = $(closest_booking).find('#reg_email').val();
      var checked_row_permission_field = 1;
      var eventer_allowed_registrants_details = 1;
      for (var i = registrants_boxes_ind; i < new_total_tickets; i++) {
        var registrant_fields = '';

        if ($crow.find('.default-registrant').length <= 0 && $(closest_booking).find('.default-registrant').length > 0) {
          checked_row_permission_field = 0;
        }
        var permission_switch_added = $crow.find('.eventer-registrants-field-permission').length;
        var first_switch = 0;

        if (i === checked_row_permission_field && single.mandatory_registrants !== 'on') {
          first_switch = 1;
          registrant_fields += '<div class="eventer-col10 eventer-registrants-field-permission"><label class="eventer-checkbox"><input class="eventer-permission-add-registrants" checked type="checkbox"> ' + single.individual_label + '</label></div>';
        }
        var name_Series = (i + 1);
        var registrants_field_class = (permission_switch_added > 0 || first_switch !== 0) ? 'eventer-removable-field-bypermission' : '';
        registrant_fields += '<div class="eventer-registrants-list ' + registrants_field_class + '"><div class="eventer-col5 eventer-col10-xs"><label>' + single.registrant_label + ' ' + name_Series + '</label><input data-required="1" class="registrant-fields eventer_registrant_name" placeholder="' + single.registrant_name + '" name="' + single.registrant_name_label + i + '" type="text"></div><div class="eventer-col5 eventer-col10-xs"><label>&nbsp;</label><input data-required="1" class="registrant-fields eventer_registrant_email" placeholder="' + single.registrant_email + '" name="' + single.registrant_email_label + i + '" type="email"></div></div>';

        registrants_fields_set += registrant_fields;
      }
      var has_checkbox = $crow.find('.eventer-permission-add-registrants').length;
      eventer_allowed_registrants_details = (has_checkbox > 0) ? $crow.find('.eventer-permission-add-registrants:checked').length : eventer_allowed_registrants_details;
      if (single.individual_reg === '1') {
        if (registrants_boxes > 0 && new_total_tickets >= registrants_boxes_ind) {
          $crow.find('.eventer-registrants-row').append(registrants_fields_set);
        } else if (registrants_boxes > 0 && new_total_tickets <= registrants_boxes_ind && new_total_tickets !== '0') {
          var less_fields = (registrants_boxes_ind - new_total_tickets);
          $crow.find('.eventer-registrants-row > .eventer-registrants-list').slice(-less_fields).remove();
        } else {
          $crow.find('.eventer-registrants-row').remove();
          $crow.append('<div class="eventer-row eventer-registrants-row" data-ticket="' + $crow.find('.name-ticket').text() + '">' + registrants_fields_set + '</div>');
        }
        /*Starting Code to show hide registrant name & email field*/
        if (eventer_allowed_registrants_details !== 1) {
          $crow.find('.eventer-registrants-row').find('.eventer-removable-field-bypermission').hide();
        } else {
          $crow.find('.eventer-registrants-row').find('.eventer-removable-field-bypermission').show();
        }
        /*Ending Code to show hide registrant name & email field*/
        var removable_rows = $crow.find('.eventer-removable-field-bypermission').length;
        if (removable_rows <= 0) {
          $crow.find('.eventer-registrants-field-permission').remove();
        }
        if ($(closest_booking).find('.default-registrant').length === 0) {
          $(closest_booking).find(".eventer-registrants-list:first").find('.eventer_registrant_name').addClass('default-registrant');
          $(closest_booking).find(".eventer-registrants-list:first").find('.eventer_registrant_name').val(registrant1_name);
          $(closest_booking).find(".eventer-registrants-list:first").find('.eventer_registrant_email').val(registrant1_email);
        }

      }
      number = parseInt($crow.find(".num-tickets").val());
      if (($.isNumeric(number) && $.isNumeric(price)) || number === '') {
        if (number === '') {
          number = 0;
        }
        var $ticket_counted_price = price * number;
        $ticket_counted_price = $ticket_counted_price.toFixed(2);
        var set_price_individual = (single.curr_position === "postfix") ? $ticket_counted_price + single.curr : single.curr + $ticket_counted_price;
        $crow.find(".total-price").text(set_price_individual);
        $crow.find(".total-price").attr('data-mprice', $ticket_counted_price);
      }
      var total_price_ticket = 0;
      $(closest_booking).find(".total-price").each(function () {
        var $one_price = ($(this).attr("data-mprice") !== '') ? $(this).attr("data-mprice") : 0;
        total_price_ticket = total_price_ticket + parseFloat($one_price);
      });
      total_price_ticket = total_price_ticket.toFixed(2);
      var set_price = (single.curr_position === "postfix") ? total_price_ticket + single.curr : single.curr + total_price_ticket;
      $(closest_booking).find(".eventer-ticket-price-total").text(set_price);
      $(closest_booking).find(".eventer-ticket-price-total").attr('data-fprice', total_price_ticket);
      $(closest_booking).find(".eventer-ticket-price-total").attr('data-aprice', total_price_ticket);
      $(closest_booking).find(".num-tickets").each(function () {
        if ($(this).val() !== '0') {
          have_ticket = 1;
        }
      });
      if (total_price_ticket > 0) {
        //alert("1");
        $(closest_booking).find(".payment-options-area").show();
        var payment_options = $('.chosen-payment-option').length;
        if (payment_options === 1) {
          $('.chosen-payment-option').attr('checked', true);
        }
        var checked_payment = $('.chosen-payment-option:checked').val();
        $(closest_booking).find('.save-registrant').prop('disabled', false);
        $(closest_booking).find('.add_services_btn').removeClass('disableClick');
        if (checked_payment === '1') {
          $(closest_booking).find("form#ticket-reg").attr('action', single.paypal_site);
          $(closest_booking).find(".save-registrant").val(single.paypal_proceed);
        } else if (checked_payment === '2') {

          $(closest_booking).find("form#ticket-reg").attr('action', $("#paypal_return").text());
          $(closest_booking).find(".save-registrant").val(single.stripe_proceed);
        }



        //$(closest_booking).find(".offline_message").hide();

        $(closest_booking).find(".save-registrant").attr("data-payment", "1");
        $(closest_booking).find('form.ticket-reg').find(".message").empty();
        if (single.offline_switch !== "1" && single.woo_payment_switch !== 'on') {
          //alert(single.offline_switch);
          //$(closest_booking).find(".chosen-payment-option").prop("checked", true);
        } else {
          //$(closest_booking).find("input[name=chosen-payment-option][value='0']").prop("checked", true);
          //$(closest_booking).find(".offline_message").show();
          /*$(closest_booking).find(".save-registrant").val(single.proceed_register);
          $(closest_booking).find(".save-registrant").attr("data-payment", "");
          $(closest_booking).find('form.ticket-reg').find(".message").empty();
          $(closest_booking).find("form#ticket-reg").attr('action', $(closest_booking).find("#paypal_return").text());*/
        }
      } else if (have_ticket === 1 && total_price_ticket <= 0) {
        $(closest_booking).find('.save-registrant').prop('disabled', false);
        $(closest_booking).find('.save-registrant').removeClass('eventer-stripe-trigger');
        $(closest_booking).find('.add_services_btn').removeClass('disableClick');
        $(closest_booking).find("form#ticket-reg").attr('action', $(closest_booking).find("#paypal_return").text());
        $(closest_booking).find(".payment-options-area").hide();
        //$(closest_booking).find(".offline_message").hide();
        $(closest_booking).find(".save-registrant").val(single.proceed_register);
        $(closest_booking).find(".save-registrant").attr("data-payment", "");
        $(closest_booking).find('form.ticket-reg').find(".message").empty();
      } else if (have_ticket === 1) {
        $(closest_booking).find('.save-registrant').prop('disabled', false);
        $(closest_booking).find('.add_services_btn').removeClass('disableClick');
        $(closest_booking).find("form#ticket-reg").attr('action', $(closest_booking).find("#paypal_return").text());
        $(closest_booking).find(".payment-options-area").hide();
        //$(closest_booking).find(".offline_message").hide();
        $(closest_booking).find(".save-registrant").val(single.proceed_register);
        $(closest_booking).find(".save-registrant").attr("data-payment", "");
        $(closest_booking).find('form.ticket-reg').find(".message").empty();
      } else {
        $(closest_booking).find('.save-registrant').prop('disabled', true);
        $(closest_booking).find('.add_services_btn').addClass('disableClick');
        $(closest_booking).find("form#ticket-reg").attr('action', $(closest_booking).find("#paypal_return").text());
        $(closest_booking).find(".payment-options-area").hide();
        //$(closest_booking).find(".offline_message").show();
        $(closest_booking).find(".save-registrant").val(single.proceed_register);
        $(closest_booking).find(".save-registrant").attr("data-payment", "");
        $(closest_booking).find('form.ticket-reg').find(".message").empty();
      }
    });

    $(document).on('click', '.remove_from_cart_button', function () {
      var product = $(this).attr('data-product_id');
      $("input:checkbox[name='" + product + "']:checked").each(function () {
        $(this).trigger('change');
      });
      $("input:radio[name='" + product + "']:checked").each(function () {
        $(this).prop('checked', false);
        $(this).trigger('change');
      });
      setTimeout(function () {
        jQuery(document.body).trigger("update_checkout");
        $(".showcoupon").click(function () {
          $(".checkout_coupon").slideToggle();
          return false;
        });
      }, 500);
    });

    $(document).on('change', '.eventer-permission-add-registrants', function () {
      if ($(this).is(':checked')) {
        $(this).closest('.eventer-registrants-row').find('.eventer-removable-field-bypermission').show();
      } else {
        $(this).closest('.eventer-registrants-row').find('.eventer-removable-field-bypermission').hide();
      }
    });

    $(document).on("change", 'input:radio[name="chosen-payment-option"]',
      function () {
        if (this.checked && this.value === '0') {
          $("form#ticket-reg").attr('action', $("#paypal_return").text());
          $('.save-registrant').removeClass('eventer-stripe-trigger');
          $(".offline_message").show();
          $(".eventer_stripe_field").hide();
          $(".save-registrant").val(single.proceed_register);
          $(".save-registrant").attr("data-payment", "");
          $('form.ticket-reg').find(".message").empty();
        } else if (this.checked && this.value === '1') {
          $("form#ticket-reg").attr('action', single.paypal_site);
          $('.save-registrant').removeClass('eventer-stripe-trigger');
          $(".offline_message").hide();
          $(".eventer_stripe_field").hide();
          $(".save-registrant").val(single.paypal_proceed);
          $(".save-registrant").attr("data-payment", "1");
          $('form.ticket-reg').find(".message").empty();
        } else if (this.checked && this.value === '3') {
          $("form#ticket-reg").attr('action', single.dotpay_site);
          $('.save-registrant').removeClass('eventer-stripe-trigger');
          $(".offline_message").hide();
          $(".eventer_stripe_field").hide();
          $(".save-registrant").val(single.dotpay_proceed);
          $(".save-registrant").attr("data-payment", "1");
          $('form.ticket-reg').find(".message").empty();
        } else if (this.checked && this.value === '2') {
          $("form#ticket-reg").attr('action', $("#paypal_return").text());
          $('.save-registrant').addClass('eventer-stripe-trigger');
          $(".offline_message").hide();
          $(".eventer_stripe_field").show();
          $(".save-registrant").val(single.stripe_proceed);
          $(".save-registrant").attr("data-payment", "1");
          $('form.ticket-reg').find(".message").empty();
        }
      }
    );

    $(document).on('click', '.eventer-coupon-validate', function () {
      var coupon_code = $(this).closest('.eventer-row').find('input.eventer-apply-coupon').val();
      var couponField = $(this).closest('.eventer-row').find('input.eventer-apply-coupon');
      $(couponField).on('keydown', function () {
        $(this).parents('.eventer-coupon-module').find('.eventer-coupon-validate').removeClass('eventer-coupon-invalid eventer-coupon-validated');
        $('.eventer-coupon-validate').removeAttr('disabled');
      });
      $.ajax({
        url: single.ajax_url,
        type: 'post',
        dataType: 'json',
        data: {
          action: 'eventer_validate_coupon',
          coupon: coupon_code,
          amount: $('.eventer-ticket-price-total').attr('data-aprice'),
        },
        success: function (response) {
          if (response.validate === '1') {
            $('.eventer-ticket-price-total').attr('data-fprice', response.amount);
            var set_price = (single.curr_position === "postfix") ? response.amount + single.curr : single.curr + response.amount;
            $(".eventer-ticket-price-total").text(set_price);
            $('.eventer-coupon-validate').removeClass('eventer-coupon-invalid');
            $('.eventer-coupon-validate').addClass('eventer-coupon-validated');
            $('.eventer-coupon-validate').attr('disabled', 'disabled');
            if (response.amount <= 0) {
              $('.eventer-payment-options').hide();
              $('.offline_message').hide();
              $("form#ticket-reg").attr('action', $("#paypal_return").text());
              $('.save-registrant').removeClass('eventer-stripe-trigger');
              $(".offline_message").show();
              $(".eventer_stripe_field").hide();
              $(".save-registrant").val(single.proceed_register);
              $(".save-registrant").attr("data-payment", "");
              $('form.ticket-reg').find(".message").empty();
            }
          } else {
            $('.eventer-coupon-validate').removeClass('eventer-coupon-validated');
            $('.eventer-coupon-validate').addClass('eventer-coupon-invalid');
            $('.eventer-coupon-validate').attr('disabled', 'disabled');
          }
        },
      });
      return false;
    });
  });
});