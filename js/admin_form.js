jQuery(function ($) {
  "use strict";
  $(document).ready(function () {
    $(document).on('click', '.eventer-settings-action, .eventer-settings-action-form', function () {
      var element = $(this);
      if (element.attr('data-key') === 'remove') {
        element.closest('.eventer-row').remove();
      }
      else if (element.attr('data-key') === 'disable' || element.attr('data-key') === 'enable') {
        if (element.attr('data-key') === 'disable') {
          element.addClass('eventer-fn-act-enable');
          element.removeClass('eventer-fn-act-disable');
          element.attr('data-key', 'enable');
          element.attr('title', adminval.disable_row);
          element.closest('.eventer-dynamic_area-action').removeClass('eventer-fe-disabled-row');
        }
        else {
          element.removeClass('eventer-fn-act-enable');
          element.addClass('eventer-fn-act-disable');
          element.attr('data-key', 'disable');
          element.attr('title', adminval.enable_row);
          element.closest('.eventer-dynamic_area-action').addClass('eventer-fe-disabled-row');
        }
      }
      var form_id = $('form#eventer_add_new_event').attr("data-id");
      var form_sections = $('form#eventer_add_new_event').attr("data-sections");
      var form_status = $('form#eventer_add_new_event').attr("data-status");
      var form_name = $('form#eventer_add_new_event').attr("data-name");
      var dynamic_fields = {};
      $('#eventer_add_new_event').closest('form').find('.eventer_dynamic_section_area').each(function () {
        var section_id = $(this).attr('id');
        var section_rows = {};
        var section_type = $(this).find('.eventer-section-type').val();
        var section_switch = $(this).find('.eventer-section-switch').val();
        section_rows.type = section_type;
        section_rows.btn = section_switch;
        var section_specific_all = [];

        $(this).attr('data-section', section_type);
        $(this).find('.eventer-dynamic_area-action').each(function () {
          var row_switch = $(this).find('.eventer-row-switch').attr('data-key');
          var section_columns = {};
          var each_field_details = [];
          section_columns.status = row_switch;
          $(this).find('.eventer_fn_field_col').each(function () {
            var section_specific = {};
            var column = $(this).attr('data-column');
            var shortcode = $(this).find('input').val();
            section_specific.column = column;
            section_specific.id = $(this).find('input').attr('data-id');
            //section_specific.display = $(this).find('input').attr('data-display');
            section_specific.shortcode = shortcode;
            each_field_details.push(section_specific);
          });
          section_columns.shorts = each_field_details;
          section_specific_all.push(section_columns);
        });
        section_rows.fields = section_specific_all;
        dynamic_fields[section_id] = section_rows;
      });
      var sections = [];
      $('.eventer_add_new_event').find('.eventer-fn-form-block').each(function () {
        var section = $(this).attr('id');
        if (typeof section !== "undefined") {
          sections.push(section);
        }
      });
      $.ajax({
        method: "POST",
        url: adminval.root + 'imithemes/form_settings',
        data: { 'dynamic': dynamic_fields, 'sections': sections, 'form_id': form_id, 'number': form_sections, 'status': form_status, 'name': form_name },
        beforeSend: function (xhr) {
          element.closest('form').find('.eventer-loader-wrap').show();
          xhr.setRequestHeader('X-WP-Nonce', adminval.nonce);
        },
        success: function (response) {
          element.closest('form').find('.eventer-loader-wrap').hide();
          //alert( ajaxval.success );
        },
        fail: function (response) {
          alert(adminval.failure);
        },
        complete: function (status) {
          element.closest('form').find('.eventer-loader-wrap').hide();
        }
      });
    });

    $('.eventer_dynamic_section_area, .eventer_add_new_event').sortable({
      //connectWith: $(".eventer_dynamic_section_area"),
      cursor: 'pointer',
      opacity: 0.4,
      stop: function () {
        $('.eventer-settings-action-form').trigger("click");
      }
    });
    $('.eventer_dynamic_section_area, .eventer_add_new_event').disableSelection();

    $('.eventer_add_new_row').change(function () {
      var row_selected = $(this).find('option:selected');
      var fields = row_selected.attr('data-rows');
      var cols = JSON.parse(row_selected.attr('data-cols'));
      var add_fields = '<div class="eventer-row eventer-fn-field eventer-dynamic_area-action">';
      for (var i = 1; fields >= i; i++) {
        var class_new = cols[i - 1];
        add_fields += '<div class="eventer-col' + class_new + ' eventer_fn_field_col" data-column="' + class_new + '">';
        //add_fields += '<label>'+adminval.add_shortcode+'</label>';
        add_fields += '<input type="hidden" placeholder="' + adminval.place_shortcode + '" class="" data-id="eventer-' + Math.random() + '" value="">';
        add_fields += '<div class="eventer eventer-fe-builder-ele"><span class="eventer-fe-ele-icon"></span><span class="eventer-fe-ele-title">Add Field</span><span class="eventer-fe-ele-value"></span><a class="eventer-fe-ele-copy"></a><a class="eventer-fe-ele-paste eventer_disabled_link"></a><a class="eventer-fe-ele-settings"></a></div>';
        add_fields += '</div>';
      }
      add_fields += '<div class="eventer-fn-actions"><a href="javascript:void();" class="eventer-row-switch eventer-fn-act-enable eventer-settings-action" data-key="enable" title="' + adminval.disable_row + '"><i class="eventer-icon-eye"></i></a><a href="javascript:void();" class="eventer-fn-act-enable eventer-settings-action" data-key="remove" data-status="0" title="' + adminval.delete_row + '"><i class="eventer-icon-close"></i></a></div>';
      add_fields += '</div>';
      $(add_fields).insertBefore(row_selected.closest('.eventer_dynamic_section_area').find('.eventer_add_field_before'));
      $('.eventer_add_new_row').val('');
    });
    $(document).on('click', '.eventer-fe-ele-copy', function () {
      $(this).closest('form').find('.eventer_copied_content').val('');
      var copied_content = $(this).closest('.eventer_fn_field_col').find('input').val();
      $(this).closest('form').find('.eventer_copied_content').val(copied_content);
      $(this).closest('form').find('.eventer-fe-ele-paste').removeClass('eventer_disabled_link');
    });
    $(document).on('click', '.eventer-fe-ele-paste', function () {
      var copied_content = $(this).closest('form').find('.eventer_copied_content').val();
      $(this).closest('.eventer_fn_field_col').find('input').val(copied_content);
      $(this).closest('.eventer_fn_field_col').find('.eventer-fe-ele-settings').trigger('click');
    });
    $(document).on('click', '.eventer-fe-ele-settings, .eventer_generate_shortcode_form', function () {
      $('.eventer-modal-close').trigger('click');
      $('#eventer-contact-form').closest('div').remove();
      $('.eventer-modal').remove();
      //$('body').removeClass('eventer-overflow-hidden');
      var element = $(this);
      var data = {};
      if (element.hasClass('eventer-fe-ele-settings')) {
        var field_id = element.closest('.eventer_fn_field_col').find('input').attr('data-id');
        data.field = field_id;
        var shortcode_val = $('[data-id="' + field_id + '"]').val();
        data.shortcode = shortcode_val;
      }
      else if (element.hasClass('eventer_generate_shortcode_form')) {
        var field_id_form = $(this).closest('form').attr('data-target');
        var shortcode = '[eventer_fields';
        var meta_shortcodes = '';
        $(this).closest('form').find('.eventer_select_val').each(function () {
          var field_value = $(this).val();

          var attribute = $(this).attr('data-sattr');
          if ((attribute === 'meta_key' && field_value === 'custom') || (attribute === 'meta_key_custom' && field_value === '') || (attribute === 'meta_key_custom' && field_value !== '')) {
            if (attribute === 'meta_key' && field_value === 'custom') {
              attribute = 'meta_key_custom';
              field_value = 'custom';
            }
            else if (attribute === 'meta_key_custom' && field_value !== '') {
              attribute = 'meta_key';
            }
            else {
              return true;
            }

          }
          if (attribute === 'name') {
            $('[data-id="' + field_id_form + '"]').closest('.eventer_fn_field_col').find('.eventer-fe-ele-title').text(field_value);
          }
          if (attribute === 'type') {
            meta_shortcodes += field_value;
          }
          if (attribute === 'meta_key') {
            meta_shortcodes += '/' + field_value;
          }
          //attribute = (attribute==='meta_key_custom' && field_value!=='')?'meta_key':attribute;
          shortcode += ' ' + attribute + '="' + field_value + '"';
        });
        $('[data-id="' + field_id_form + '"]').closest('.eventer_fn_field_col').find('.eventer-fe-ele-value').text(meta_shortcodes);
        var selected = '';
        element.closest('form').find('.eventer_shortcode_value').each(function () {
          $(this).find('.eventer_checked_fields').each(function (start) {
            var separator = (start % 2 === 0) ? '|' : '';
            var comma = (start === 0 && selected !== '') ? ',' : '';
            selected += comma + $(this).val() + separator;
          });
        });
        shortcode += ' param="' + selected + '"';
        shortcode += ']';

        $('[data-id="' + field_id_form + '"]').val(shortcode);
        $('#eventer-modal-close').trigger('click');
        $('.eventer_create_shortcode').remove();
        $('body').removeClass('eventer-overflow-hidden');
        //$('.eventer-settings-action-form').unbind("click");
        $('.eventer-settings-action-form').trigger("click");
        return false;
      }
      $.ajax({
        method: "POST",
        url: adminval.root + 'form/shortcode',
        data: JSON.stringify(data),
        crossDomain: true,
        contentType: 'application/json',
        beforeSend: function (xhr) {
          element.closest('form').find('.eventer-loader-wrap').show();
          xhr.setRequestHeader('X-WP-Nonce', adminval.nonce);
        },
        success: function (response) {
          element.closest('form').find('.eventer-loader-wrap').hide();
          $('body').removeClass('eventer-overflow-hidden');
          $('#eventer-modal-close').trigger('click');
          $("body").append(response.form);
          $("a.eventer_generate_shortcode").trigger("click");
        },
        fail: function (response) {

        }

      });
    });

    $(document).on('click change', '.eventer_add_new_values', function () {
      var action = $(this).attr('data-action');
      var form_closest = $(this).closest('form');
      if (action === 'delete') {
        $(this).closest('.eventer_checked_field').remove();
        return false;
      }
      else if (action === 'hide') {
        $('.eventer_featured_type').hide();
        if ($(this).val() === 'select' || $(this).val() === 'checkbox' || $(this).val() === 'radio' || $(this).val() === 'textarea') {
          if ($(this).val() !== 'textarea') {
            form_closest.find('.eventer_checked_field_section, .eventer_shortcode_value').show();
            form_closest.find('.eventer-textarea-required').hide();
          }
          else {
            form_closest.find('.eventer_checked_field_section, .eventer_shortcode_value').hide();
            form_closest.find('.eventer-textarea-required').show();
          }
        }
        else if ($(this).val() === 'featured') {
          $('.eventer_field_mandatory').removeClass('eventer-col5');
          $('.eventer_field_mandatory').addClass('eventer-col3');
          $('.eventer_featured_type').show();
        }
        else {
          form_closest.find('.eventer_checked_field_section, .eventer_shortcode_value').hide();
          form_closest.find('.eventer-textarea-required').hide();
        }
        return false;
      }
      else if (action === 'meta_field') {
        if ($(this).val() === 'custom') {
          form_closest.find('.eventer_custom_meta_field').show();
        }
        else {
          form_closest.find('.eventer_custom_meta_field').hide();
        }
        return false;
      }
      else {
        var field_add = form_closest.find('.eventer_shortcode_value').last();
        var cloned_field_add = form_closest.find('.eventer_shortcode_values').clone();
        cloned_field_add.find('input').val("");
        var new_field_to_add = cloned_field_add.removeClass('eventer_shortcode_values');
        new_field_to_add = cloned_field_add.addClass('eventer_shortcode_value');
        new_field_to_add = cloned_field_add.addClass('eventer_checked_field');
        $(new_field_to_add).insertAfter(field_add);
      }
    });
  });
  //});
});