jQuery(document).ready(function ($) {
  $('.vc_tta-tab').click(function () {
    setTimeout(function () {
      //$('.calendar').fullCalendar('rerenderEvents');
      $('.calendar').fullCalendar('refetchEvents');
      $('.calendar').fullCalendar('render');
    }, 1000);
  });
  var event_end_time = false;
  if (fcal.event_end_time === '1') {
    event_end_time = true;
  }
  var initialLocaleCode = fcal.sitelan;
  var weeks;
  if (fcal.weeks === '6') {
    weeks = true;
  }
  else {
    weeks = false;
  }
  $('.calendar').prepend('<div id="loading-image"><div class="eventer-loader-wrap"><div class="eventer-loader"></div></div></div>');
  $('.calendar').fullCalendar({
    header: {
      left: fcal.header_left,
      center: fcal.header_center,
      right: fcal.header_right
    },
    defaultView: fcal.cal_view,
    defaultDate: fcal.defaultDate,
    displayEventEnd: event_end_time,
    firstDay: fcal.week_str,
    isRTL: fcal.cal_rtl,
    eventAfterRender: function (event, element) {
      element.find('.fc-title').html(event.title);
    },
    eventRender: function (event, element, view) {
      var title = element.find('.fc-title, .fc-list-item-title');
      title.html(title.text());
    },
    eventClick: function (event) {
      if (event.url) {
        window.open(event.url, event.targ);
        return false;
      }
    },
    eventMouseover: function (calEvent, jsEvent) {
      var tooltip = calEvent.metas;
      $("body").append(tooltip);
      $(this).mouseover(function (e) {
        $(this).css('z-index', 10000);
        $('.tooltipevent').fadeIn('500');
        $('.tooltipevent').fadeTo('10', 1.9);
      }).mousemove(function (e) {
        //var height = $(window).height();
        var width = $(window).width();
        //var half_height = height/2;
        //var half_width = width/2;
        //var third_height = height/3;
        //var third_width = width/3;
        var popup_height = $('.tooltipevent').height();
        var popup_width = $('.tooltipevent').width();
        var mouse_space_top = e.pageY - $(window).scrollTop();
        //var mouse_space_left = e.pageX-width;
        //var heightset = (mouse_space_top>third_height)?mouse_space_top:third_height;
        var set_popup_position = e.pageY - popup_height;
        set_popup_position = (mouse_space_top > popup_height) ? set_popup_position : (e.pageY - mouse_space_top) + 40;
        $('.tooltipevent').css('top', set_popup_position);
        var set_popup_width = e.pageX + 10;
        var remaining_width = width - set_popup_width;
        set_popup_width = (remaining_width > popup_width) ? set_popup_width : e.pageX - popup_width;
        $('.tooltipevent').css('left', set_popup_width);
      });
    },

    eventMouseout: function (calEvent, jsEvent) {
      $(this).css('z-index', 8);
      $('.tooltipevent').remove();
    },
    locale: initialLocaleCode,
    buttonIcons: false, // show the prev/next text
    weekNumbers: false,
    navLinks: true, // can click day/week names to navigate views
    editable: false,
    //themeSystem: 'bootstrap3',
    eventLimit: 3, // allow "more" link when too many events
    fixedWeekCount: weeks,
    googleCalendarApiKey: fcal.calendar_api,
    timeFormat: fcal.time_format,
    loading: function (bool) {
      if (bool)
        jQuery('#loading-image').show();
      else
        jQuery('#loading-image').hide();
    },
    viewRender: function (view, element) {
      element.closest('.calendar').fullCalendar('removeEventSources');
      var calendar_id = element.closest('div.eventer-calendar-render').find(".shortcode-vals").attr('data-calendar');
      element.closest('.calendar').fullCalendar('refetchEventSources');
      element.closest('.calendar').fullCalendar('addEventSource', {
        url: fcal.homeurl + 'front/eventer-feed.php',
        type: 'POST',
        data: {
          shortcode_atts: $.parseJSON(element.closest('div.eventer-calendar-render').find(".shortcode-vals").text()),
          site_lang: fcal.sitelan
        }
      }
      );
      if (calendar_id !== '' && typeof calendar_id !== 'undefined') {
        jQuery('.calendar').fullCalendar('addEventSource', {
          googleCalendarId: calendar_id
        });
      }

      jQuery('.calendar').fullCalendar('refetchEventSources');
    },
  });

  // build the locale selector's options
  $.each($.fullCalendar.locales, function (localeCode) {
    $('#locale-selector').append(
      $('<option/>')
        .attr('value', localeCode)
        .prop('selected', localeCode == initialLocaleCode)
        .text(localeCode)
    );
  });

  // when the selected option changes, dynamically change the calendar option
  $('#locale-selector').on('change', function () {
    if (this.value) {
      $('#calendar').fullCalendar('option', 'locale', this.value);
    }
  });
});