jQuery(function ($) {
  "use strict";
  $(document).ready(function () {
    function eventerformatDate(date) {
      var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

      if (month.length < 2) month = '0' + month;
      if (day.length < 2) day = '0' + day;
      return [year, month, day].join('-');
    }
    const params = new URLSearchParams(location.search);
    var tab = params.get('tab');
    if (dashboard.current_user_id === '' || dashboard.current_user_id === '0') {
      $('.eventer-fe-usermenu').hide();
      $('.eventer-dashboard-content-area').empty();
      setTimeout(function () { $('[data-tab="eventer_login"]').trigger('click'); }, 1000);
    }
    else {
      if (tab !== '' && typeof tab !== 'undefined' && tab !== null) {
        $('.eventer-dashboard-content-area').empty();
        setTimeout(function () { $('[data-tab="' + tab + '"]').trigger('click'); }, 1000);
      }
      else {
        /*if($('body').find('.eventer-section-bookings').length>0)
        {
            $('.eventer_dashboard_tabs').closest('li').removeClass('active');
            $('[data-tab="bookings"]').closest('li').addClass('active');
            $("#eventer-dashboard-content-area").addClass('eventer-dashboard-main');
            $('[data-tab="eventer_bookings"]').trigger('click');
        }
        else if($('body').find('.eventer-fe-submissions-list').length>0)
        {
            $('.eventer_dashboard_tabs').closest('li').removeClass('active');
            $('[data-tab="submissions"]').closest('li').addClass('active');
            
        }*/
        if (dashboard.default_shortcode !== 'eventer_submissions') {

        }
        else {
          //eventer_get_submissions(0, 'draft,publish,pending', 100, '1');
        }
        setTimeout(function () { $('[data-tab="' + dashboard.default_shortcode + '"]').trigger('click'); }, 1000);
      }
      //eventer_get_submissions(0, 'draft,publish,pending', 100, '1');
      //eventer_load_more();
    }

    $(window).on("popstate", function () {
      const params = new URLSearchParams(location.search);
      var tab = params.get('tab');
      $('[data-tab="' + tab + '"]').trigger('click');
    });




    function eventer_get_bookings(count, offset, status) {
      var count = (typeof count !== 'undefined' && count === '') ? 6 : count;
      var offset = (typeof offset !== 'undefined' && offset === '') ? 0 : offset;
      var status = (typeof status !== 'undefined' && status === '') ? '' : status;
      $.ajax({
        method: "POST",
        url: dashboard.root + 'eventers/bookings',
        data: JSON.stringify({ 'status': status }),
        crossDomain: true,
        contentType: 'application/json',
        beforeSend: function (xhr) {
          $('.eventer-fe-dashboard').find('.eventer-loader-wrap').show();
          xhr.setRequestHeader('X-WP-Nonce', dashboard.nonce);
        },
        success: function (response) {
          if (offset === 0) {
            $('.eventer-fe-booking-list').remove();
            $('.eventer-booking-order').remove();
          }
          $('.eventer-fe-dashboard').find('.eventer-loader-wrap').hide();
          $('.eventer-fe-dash-content').attr('data-termcount', response.bookings.length);
          $('.eventer-fe-dash-content').attr('data-type', 'bookings');
          var height = $('.eventer-dashboard-main').innerHeight();
          $('.eventer-dashboard-main').css("height", height);
          $('.eventer-dashboard-main').css("overflow-y", "scroll");
          var all_bookings = response.bookings;
          var total_result = (count + offset <= all_bookings.length) ? count + offset : all_bookings.length;
          if (all_bookings.length > 0) {
            for (var list = 0; list < (all_bookings.length); list++) {
              var events = all_bookings[list].events;
              var order = all_bookings[list].order;
              var order_status = all_bookings[list].status;
              var tickets_available = (order_status === 'completed') ? 'eventer-dynamic-bookings' : '';
              var result_li = '<span class="eventer-booking-order">' + dashboard.order_string + order + '</span>';
              result_li += '<ul class="eventer-fe-dash-list eventer-fe-booking-list ' + tickets_available + ' eventer_count_list" data-order="' + order + '">';
              for (var list_li = 0; list_li < events.length; list_li++) {
                var id = events[list_li].id;
                var status_event = events[list_li].status_check;
                if (status_event !== 1) {
                  //result_li.removeclass('eventer-dynamic-bookings');
                }
                result_li += '<li class="eventer-fe-booking-record" data-event="' + id + '">';
                var title = events[list_li].name;
                var status = events[list_li].status;
                var date = events[list_li].date;
                var ticket = events[list_li].ticket;
                var venue = events[list_li].venue;
                result_li += '<div><span class="eventer-fe-ticket-status eventer-fe-ticket-status_upcoming">' + status + '</span></div>';
                result_li += '<div><strong>' + title + '</strong></div>';
                result_li += '<div><i class="eventer-icon-calendar"></i>' + date + '</div>';
                result_li += '<div><i class="eventer-icon-location-pin"></i>' + venue + '</div>';
                var ticket_info = '';
                for (var ticket_st = 0; ticket_st < ticket.length; ticket_st++) {
                  ticket_info += '<span class="eventer-fe-ticket-count">' + ticket[ticket_st] + '</span>';
                }
                result_li += '<div>' + ticket_info + '</div>';
                result_li += '</li>';
              }
              result_li += '';
              result_li += '</ul>';
              $('.eventer-dashboard-main').append(result_li);
            }
          }
          else {
            $('.eventer-fe-booking-list').remove();
            $('.eventer-booking-order').remove();
            var blank_result_li = '<ul class="eventer-fe-dash-list eventer-fe-booking-list">';
            blank_result_li += '<li class="eventer-fe-booking-record">';
            blank_result_li += '<div><strong>Sorry, no bookings found</strong></div>';
            blank_result_li += '</li>';
            blank_result_li += '</ul>';
            $('.eventer-dashboard-main').append(blank_result_li);
          }
        },
        fail: function (response) {

        },
      });
    }
    function eventer_get_submissions(offset, status, count, result) {
      var count = (typeof count !== 'undefined' && count === '') ? 4 : count;
      var offset = (typeof offset !== 'undefined' && offset === '') ? 0 : offset;
      var status = (typeof status !== 'undefined' && status === '') ? ['draft', 'publish', 'pending'] : status;
      var result = (typeof result !== 'undefined' && result === '') ? '' : result;
      $.ajax({
        method: "GET",
        url: dashboard.root + 'wp/v2/eventer/?per_page=100&author=' + dashboard.current_user_id,
        data: { 'custom_fields_get': ['eventer_rest_email_status'], 'status': ['draft', 'publish', 'pending'] },
        beforeSend: function (xhr) {
          $('.eventer-fe-dashboard').find('.eventer-loader-wrap').show();
          xhr.setRequestHeader('X-WP-Nonce', dashboard.nonce);
        },
        success: function (response) {
          $('.eventer-fe-dashboard').find('.eventer-loader-wrap').hide();
          if (response.length > 0) {
            var height = $('.eventer-dashboard-main').innerHeight();
            $('.eventer-dashboard-main').css("height", height);
            $('.eventer-dashboard-main').css("overflow-y", "scroll");
            for (var list = 0; list < response.length; list++) {
              if (parseInt(response[list].author) === parseInt(dashboard.current_user_id)) {
                var status = response[list].status;
                var li_class = '';
                if (status === 'publish') {
                  li_class = 'eventer-fe-event-active';
                }
                else if (status === "draft") {
                  li_class = 'eventer-fe-event-inactive';
                }
                else {
                  li_class = 'eventer-fe-event-review';
                }
                var result_li = $('.eventer-fe-submissions-list').find('.eventer_dashboard_submissions_list').clone();
                result_li = result_li.removeClass('eventer_dashboard_submissions_list');
                result_li = result_li.addClass(li_class);
                result_li = result_li.addClass('eventer_count_list');
                result_li = result_li.attr('id', 'submission-' + response[list].id);
                result_li = result_li.attr('data-event', response[list].id);
                result_li.find('.eventer-submission-date').text(eventerformatDate(response[list].date));
                result_li.find('.eventer-submission-title').append('<a href="' + response[list].link + '" class="eventer-title-permalink"></a>');
                result_li.find('.eventer-title-permalink').html(response[list].title.rendered);
                result_li.find('.eventer-submission-venue').text(response[list].eventer_venue.term);
                result_li.find('.eventer-submission-organizer').text(response[list].eventer_organizer.term);
                if (response[list].eventer_rest_email_status !== 'publish') {
                  result_li.find('.eventer-submission-status-act').remove();
                }
                else if (response[list].status === 'publish') {
                  result_li.find('.eventer-submission-status-act').attr('data-action', 'draft');
                  result_li.find('.eventer-submission-status-act').find('a').text('Disable');
                }
                else if (response[list].status === 'draft') {
                  result_li.find('.eventer-submission-status-act').attr('data-action', 'publish');
                  result_li.find('.eventer-submission-status-act').find('a').text('Enable');
                }
                result_li.css('display', '');
                $('.eventer-fe-submissions-list').append(result_li);
              }
            }
            if (result !== '2') {
              //eventer_get_user_terms(0, 'eventer-organizer');
              //eventer_get_user_terms(0, 'eventer-venue');
            }

          }
          else {
            $('.eventer-fe-dash-content').attr('data-postscount', $('.eventer-fe-submissions-list').find('.eventer_count_list').length);
            if ($('.eventer-fe-submissions-list').find('.eventer_count_list').length > 0) {
              return true;
            }
            var result_none = $('.eventer-fe-submissions-list').find('.eventer_dashboard_submissions_list').clone();
            result_none = result_none.removeClass('eventer_dashboard_submissions_list');
            result_none = result_none.addClass('eventer-fe-event-review');
            result_none = result_none.addClass('eventer-fe-no-results');
            result_none = result_none.addClass('eventer_count_list');
            result_none.find('.eventer-submission-date').closest('div').remove();
            result_none.find('.eventer-submission-title').text('Sorry, no submissions found.');
            result_none.find('.eventer-submission-venue').closest('div').remove();
            result_none.find('.eventer-submission-organizer').closest('div').remove();
            result_none.find('.eventer-fe-list-actions').remove();
            result_none.css('display', '');
            $('.eventer-fe-submissions-list').append(result_none);
          }

        },
        fail: function (response) {

        },
        complete: function (response) {
          //eventer_get_submissions();
        },
      });
    }
    function eventer_load_more() {
      $('.eventer-dashboard-main').on('scroll', function () {
        var count_list = $(this).find('.eventer_count_list').length;
        var posts = $('.eventer-fe-dash-content').attr('data-postscount');
        var type = $('.eventer-fe-dash-content').attr('data-type');
        if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
          if (type === "bookings") {
            posts = $('.eventer-fe-dash-content').attr('data-termcount');
          }
          if (count_list < posts) {
            if (type === "bookings") {
              posts = $('.eventer-fe-dash-content').attr('data-termcount');
              eventer_get_bookings(8, count_list);
            }
            else if (type === "submissions") {
              eventer_get_submissions(count_list, 'draft,publish,pending', 4, '2');
            }

          }

        }
      });
    }
    $(document).on('click', '.eventer-get-user-bookings', function () {
      var status = $(this).attr('data-book');
      $('.eventer-get-user-bookings').removeClass('active');
      $(this).addClass('active');
      eventer_get_bookings(6, 0, status);
    });
    $(document).on('click', '.eventer_submission_status_wise a', function () {
      $('.eventer_submission_status_wise').closest('li').removeClass('active');
      $(this).closest('li').addClass('active');
      var status = $(this).attr('data-status');
      $('.eventer_count_list').remove();
      eventer_get_submissions(0, status, 4, '2');

    });
    $(document).on('click', '.eventer-term-pagination', function () {
      var offset = $(this).attr('data-offset');
      if ($(this).hasClass('pagination-eventer-organizer')) {
        eventer_get_user_terms(offset, 0, 'eventer-organizer');
      }
      else {
        eventer_get_user_terms(0, offset, 'eventer-venue');
      }

    });
    function eventer_get_user_terms(organizer, venue, taxo) {
      var organizer = (typeof organizer !== 'undefined' && organizer === '') ? 0 : organizer;
      var venue = (typeof venue !== 'undefined' && venue === '') ? 0 : venue;
      var taxo = (typeof taxo !== 'undefined' && taxo === '') ? '' : taxo;
      $.ajax({
        method: "POST",
        url: dashboard.root + 'eventers/terms',
        data: JSON.stringify({ 'organizer': organizer, 'venue': venue }),
        crossDomain: true,
        contentType: 'application/json',
        beforeSend: function (xhr) {
          //$('.eventer-fe-submissions-list').find('.eventer-loader-wrap').show();
          xhr.setRequestHeader('X-WP-Nonce', dashboard.nonce);
        },
        success: function (response) {
          if (taxo === '') {
            $('.eventer-fe-sidebar').empty();
          }
          else {
            $('.taxonomy-' + taxo).remove();
          }
          var not_blank = '';
          $.each(response, function (index, value) {
            if (taxo !== '' && taxo !== value.taxonomy) {
              return true;
            }
            var next_link = '';
            var prev_link = '';

            if (value.older >= 0) {
              prev_link = '<a data-offset="' + value.older + '" class="pagination-' + value.taxonomy + ' eventer-term-pagination eventer-prev-term">Prev</a>';
            }
            if (value.newer > 0 && value.newer < value.total) {
              next_link = '<a data-offset="' + value.newer + '" class="pagination-' + value.taxonomy + ' eventer-term-pagination eventer-next-term">Next</a>';
            }
            var term = '';

            term += '<div class="taxonomy-' + value.taxonomy + '">';

            term += '<label>' + index + '</label><div class="eventer-fe-infolist"><ul>';
            $.each(value.terms, function (subindex, subvalue) {
              term += '<li>';
              not_blank = '1';
              var counter = 1;
              $.each(subvalue, function (childindex, childvalue) {
                if (counter === 1) {
                  term += '<strong>' + subvalue.name + '</strong>';
                  if (childindex !== 'name') {
                    term += '<span class="eventer-meta">' + childvalue + '</span>';
                  }
                }
                else if (childindex !== 'name') {
                  term += '<span class="eventer-meta">' + childvalue + '</span>';
                }
                counter++;
              });
              term += '</li>';
            });
            term += '</ul></div><div class="eventer-spacer-30"></div>';

            term += next_link + prev_link;

            term += '</div>';

            term += '';
            if (value.taxonomy === 'eventer-organizer') {
              $('.eventer-fe-sidebar').prepend(term);
            }
            else {
              $('.eventer-fe-sidebar').append(term);
            }


          });
          if (not_blank === '') {
            $('.eventer-fe-sidebar').remove();
          }
        },
        fail: function (response) {

        }
      });
    }
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

    $(document).on('click', '.eventer-submission-actions li', function () {
      var action = $(this).attr('data-action');
      var eventer = $(this).closest('.eventer_count_list').attr('data-event');
      if (action === 'edit') {
        $('[data-tab="eventer_add_new"]').attr('data-temp', eventer);
        $('[data-tab="eventer_add_new"]').trigger('click');
      }
    });
    $(document).on('click', '.eventer-submission-act', function () {
      var action = $(this).attr('data-action');
      if (action === 'delete') {
        var li = $(this).closest('ul').closest('li');
        var change = li.clone();
        change.find('.eventer-while-delete').remove();
        li.empty();
        li.append(change);
        li.append('<a data-action="remove" class="eventer-submission-act">Remove</a>');
      }
      else if (action === 'remove') {
        var sub = $(this).closest('li');
        var eventer_id = $(this).closest('li').attr('data-event');
        $.ajax({
          method: "DELETE",
          url: dashboard.root + 'wp/v2/eventer/' + eventer_id + '?force=true',
          data: contents,
          beforeSend: function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dashboard.nonce);
          },
        }).success(function (response) {
          sub.remove();
        });
      }
      else {
        var eventer = $(this).closest('ul').closest('li').attr('data-event');
        var new_action = (action === 'draft') ? 'publish' : 'draft';
        var new_action_class = (action === 'draft') ? 'eventer-fe-event-active' : 'eventer-fe-event-inactive';
        var new_action_class_remove = (action === 'draft') ? 'eventer-fe-event-inactive' : 'eventer-fe-event-active';
        var event = eventer_submit_eventer({ 'status': action }, $(this).closest('ul').closest('ul'), eventer);
        if (event !== '') {
          $(this).attr('data-action', new_action);
          $(this).find('a').text($(this).attr('data-' + new_action));
          $(this).closest('ul').closest('li').addClass(new_action_class_remove);
          $(this).closest('ul').closest('li').removeClass(new_action_class);
        }

      }

    });
    $(document).on('click', '.eventer_dashboard_tabs, .eventer-dynamic-bookings', function () {
      if ($(this).hasClass('eventer_dashboard_tabs')) {
        $('.eventer_dashboard_tabs').closest('li').removeClass('active');
        $(this).closest('li').addClass('active');
      }
      var tabs = $(this).attr('data-tab');
      var order = $(this).attr('data-order');
      order = (typeof order !== 'undefined' && order !== null) ? order : '';
      var shortcode = {};
      var eventer_id = '';

      if (tabs === 'eventer_add_new') {
        eventer_id = $(this).attr('data-temp');
        shortcode = $(this).attr('data-shortcode');
        $('[data-tab="eventer_add_new"]').attr('data-temp', '');
      }
      if ((dashboard.current_user_id === '' || dashboard.current_user_id === '0') && (tabs !== 'eventer_login')) {
        return false;
      }
      else if (tabs !== 'eventer_login') {
        $('.eventer-fe-usermenu').show();
      }
      $.ajax({
        method: "POST",
        url: dashboard.ajax_url,

        data: { action: 'eventer_switch_dashboard_tab', 'tab': tabs, 'order': order, 'shortcode': shortcode },
        type: "post",
        beforeSend: function (xhr) {
          $('.eventer-fe-dash-content').find('.eventer-loader-wrap').show();
          xhr.setRequestHeader('X-WP-Nonce', dashboard.nonce);
        },
        success: function (response) {
          $('.eventer-fe-dash-content').find('.eventer-loader-wrap').hide();
          if (tabs !== 'eventer_submissions') {
            //$('.eventer-dashboard-download-tickets').hide();
            $('.eventer-fe-sidebar').remove();
            $('.eventer-dashboard-main').empty();
            $('.eventer-dashboard-main').append(response);
          }
          if (order !== "" && typeof order !== 'undefined') {
            $('.eventer-fe-booking-linkss').trigger('click');
          }
          else if (tabs === 'eventer_bookings') {
            history.pushState('', '', eventer_update_URL('tab', tabs));
            $('.eventer-fe-dash-content').attr('data-type', 'bookings');
            eventer_get_bookings();
          }
          else if (tabs === 'eventer_submissions') {
            $('.eventer-fe-dash-content').empty();
            $('.eventer-fe-dash-content').append(response);
            history.pushState('', '', eventer_update_URL('tab', tabs));
            $('.eventer-fe-dash-content').attr('data-type', 'submissions');
            eventer_get_submissions();
            eventer_get_user_terms();

          }
          else if (tabs === 'eventer_login') {
            $('.eventer-dashboard-main').css("overflow-y", "");
            $('.eventer-dashboard-main').css("height", "auto");
          }
          else if (tabs === 'eventer_add_new') {
            $('.eventer-dashboard-main').css("overflow-y", "");
            $('.eventer-dashboard-main').css("height", "auto");
            $('.eventer-set-form-fields').val(eventer_id);

            history.pushState('', '', eventer_update_URL('tab', tabs));
            eventer_set_datepicker();
            eventer_wp_editor_set();

            if (eventer_id !== '') {
              $('.eventer-submit-form-btn').val('Update');
              $('.eventer-set-form-fields').trigger('click');
            }
            else {
              $('.eventer_front_date_field').val('');
            }

            eventer_form_autocomplete_fields();
            eventer_featured_image_wp();
          }
          eventer_load_more();
        },
        fail: function (response) {

        },
      });
    });

  });
});