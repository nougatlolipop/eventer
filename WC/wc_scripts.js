jQuery(function ($) {
	"use strict";
	$(document).ready(function () {
		function eventer_refresh_fragments() {
			$(document.body).trigger('wc_fragment_refresh');
		}
		$(document).on("click", '.add-ticket-wc', function (e) {
			e.preventDefault();
			var tickets = $(this).closest('li').find('.eventer-wc-ticket-quantity').val();
			var product = $(this).closest('li').attr('data-product');
			var ticket_id = $(this).closest('li').attr('data-eventer');
			var event_date = $(this).closest('li').attr('data-edate');
			var event_date_multi = $(this).closest('li').attr('data-multi');
			var event_time = $(this).closest('li').attr('data-etime');
			var event_slot = $(this).closest('li').attr('data-slot');
			var event_slot_title = $(this).closest('li').attr('data-slottitle');
			var event_url = $(this).closest('li').attr('data-eventerurl');
			var cart_text = $(this).closest('li').attr('data-addedcart');
			var ticket_price = $(this).closest('li').attr('data-price');
			var event_allday = $(this).closest('li').attr('data-allday');
			var element = $(this);
			var request = $.ajax({
				url: initval.ajax_url,
				type: "post",
				//async: false,
				data: {
					action: 'eventer_add_product_to_cart',
					tickets: tickets,
					product: product,
					ticket_id: ticket_id,
					event_date: event_date,
					event_multi: event_date_multi,
					event_url: event_url,
					event_time: event_time,
					event_slot: event_slot,
					event_slot_title: event_slot_title,
					event_allday: event_allday,
					ticket_price: ticket_price,
				},
				beforeSend: function (xhr) {
					$('.add-ticket-wc').prop('disabled', true);
				},
			});
			request.done(function (response) {
				$('.add-ticket-wc').prop('disabled', false);
				element.closest('li').find('.eventer-wc-ticket-added').text(cart_text).slideDown();
				setTimeout(function () {
					element.closest("li").find(".eventer-wc-ticket-added").slideUp();
				}, 5000);
				eventer_refresh_fragments();
			});
		});
		$(document).on("change", '.eventer-wc-ticket-quantity', function () {
			var raw_price = $(this).closest('li').attr('data-price');
			var raw_quantity = $(this).val();
			var raw_total = raw_price * raw_quantity;
			var raw_currency = $(this).closest('li').attr('data-currency');
			var btn_txt = $(this).closest('li').attr('data-btntxt');
			$(this).closest('li').find('.eventer-wc-ticket-total').text(raw_currency + raw_total.toString());
		});
	});
});
