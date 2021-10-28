
jQuery(function() {
// Using "<...>.nval()" instead of "Number(<...>.val())"
	jQuery.fn.nval = function() {
		return Number(this.val())
	};
// Making pretty numbers with spaces between thousand and "," as float separator, using dark magic of regular expressions  
	function numberWithSpaces(x) {
		var parts = x.toString().split(".");
		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
		return parts.join(",");
	}
// Get all requered html elements
	var form = jQuery('#wood-price-calculator'),
	inputs = form.find('input'),
	square_input = form.find('input[name="square"]'),
	amount_input = form.find('input[name="amount"]'),
	//amount_input = jQuery('form.cart input[name="quantity"]'),
	radio_input = form.find('input[name="wood-width"]'),
	checked_radio = form.find('input[name="wood-width"]:checked'),
	available_square_label = form.find('.available-square'),
	available_square_span = form.find('.available-square span')
	square_price = form.find('.square-price'),
	square_price_taxed = form.find('.square-price-taxed'),
	unit_price =  form.find('.unit-price'),
	unit_price_taxed =  form.find('.unit-price-taxed'),
	unit_stock_amount = form.find('.unit-stock-amount'),
	total_price = form.find('.total-price'),
	total_price_taxed = form.find('.total-price-taxed'),
	submit_button = form.find('.single_add_to_cart_button_2');

	var plus_button = jQuery('.wpc-plus'),
	minus_button = jQuery('.wpc-minus'),
	plus_minus_input = form.find('.wpc-p-m');

// Math variables
	var price, width, length, vr, availabe_amount;

	const tax = 21;
// What we have got from PHP in "VA_PC_Wood_Price_Calculator::set_vars_to_js( <...> )"
	console.log('product_params', product_params);

//Setting variables to chosen variation
	function set_vars() {
	//	console.log('vr', vr);
		if (typeof vr !== 'undefined') {
			length = product_params[vr]['length'];
			width = product_params[vr]['width'];
			price_taxed = product_params[vr]['price'];
			price = price_taxed * 100 / 121;
			stock_amount = product_params[vr]['variation_stock_quantity'];
			incart_amount = product_params[vr]['variation_incart_quantity'];
			availabe_amount = stock_amount - incart_amount;
		} else {
			if (!submit_button.hasClass('disabled')) { submit_button.addClass('disabled'); }
			submit_button.attr('disabled', 'disabled');
		}
	}

// Calculation, using variables
	function set_unit_price() {
		unit_price.text(numberWithSpaces(price.toFixed(2)));
		unit_price_taxed.text(numberWithSpaces((price_taxed).toFixed(2)));
		square_price.text(numberWithSpaces((
			Math.round( price * 1 / ( length * width / 10**6) )  // for 1 m^2 calculate amount without round and multiply by price
			).toFixed(2)));
		square_price_taxed.text(numberWithSpaces((
			Math.round( price * 1 / ( length * width / 10**6) * ( tax + 100 ) / 100  )  // for 1 m^2 calculate amount without round and multiply by price
			).toFixed(2)));
	}
// Main mathematical formulas for caulculation amount of square and backward. Note: "Math.ceil" rounds a number to the next largest integer.
	function amount_of_square($square) {
		return Math.ceil($square / ( length * width / 10**6))
	}

	function square_of_amount($amount) {
		return $amount * length * width / 10**6
	}
// Formulas to check max and min values of inputs. If the value out of bounds, then return value of closest bound.
	function amount_check_limits(t) {
		if(t > stock_amount) t = stock_amount;
		if(t < 1) t = 1;
		return t;
	}

	function square_check_limits(t) {
		if(t > Math.floor(square_of_amount(stock_amount) * 10) / 10) t = Math.floor(square_of_amount(stock_amount) * 10) / 10;
		if(t < 0) t = 0;
		return t;
	}
// Fuctions, which calculate (using previous formulas) and setup values to input fields
	function calc_price() {
		total_price.text( numberWithSpaces(
			( amount_input.nval() * price ).toFixed(2)
			));
		total_price_taxed.text( numberWithSpaces(
			( amount_input.nval() * price * (100 + tax) / 100 ).toFixed(2)
			));
	//	console.log(amount_input.nval() * price);
		if( available_square_label.hasClass('active') ) available_square_label.removeClass('active');		
	}

	function calc_amount() {
		let t = amount_of_square(square_input.nval());
		//t = (t > stock_amount) ? stock_amount : t;
		t = amount_check_limits(t);
		amount_input.val(
			t
			);

	}

	function calc_square() {
		square_input.val(
			//amount_input.nval() * length * width / 10**6 
			//).toFixed(2)
			(Math.floor(square_of_amount(amount_input.nval()) * 10) / 10).toFixed(1)
			);
	}


	//Start Init
	function init_form() {
		vr = checked_radio.val();
		if (typeof vr !== 'undefined') {
			if (submit_button.hasClass('disabled')) { submit_button.removeClass('disabled'); };
			set_vars();
			set_unit_price();
			unit_stock_amount.text(availabe_amount);
			calc_price();
		} else {
			if (!submit_button.hasClass('disabled')) { submit_button.addClass('disabled'); }
			vr = radio_input.val();
			console.log('radio_input', radio_input);
			console.log('vr', vr);
			set_vars();
			set_unit_price();
		}
	}

	init_form();

	/* ---- Event listners ---- */
// Radion onChange event
	radio_input.change(function(){
		checked_radio = form.find('input[name="wood-width"]:checked');
		vr = checked_radio.val();
		set_vars();
		set_unit_price();
		unit_stock_amount.text(availabe_amount);
		//square_input.val(square_check_limits(square_input.nval()));
		calc_amount();
		//if( available_square_label.hasClass('active') ) available_square_label.removeClass('active');
		//calc_square();
		calc_price();
		if( square_check_limits(square_input.nval()) < square_input.nval() ) { 
			available_square_label.addClass('active');
			available_square_span.text(square_of_amount(availabe_amount).toFixed(1)); 
		}
	});

// Inputs onChange event
	square_input.change(function(){
	//	console.log('square_input',square_input.val());
		square_input.val(square_check_limits(square_input.nval()));
		calc_amount();
		calc_price();
		available_square_label.removeClass('active');
	});

	amount_input.change(function(){
	//	console.log('amount_input',amount_input.val());
		amount_input.val(amount_check_limits(amount_input.nval()));
		calc_square();
		calc_price();
	});

// Inputs onKeyUp event
	square_input.keyup(function(){
	//	console.log('square_input',square_input.val());
		console.log('square_input nval',square_input.nval());
		calc_amount();
		calc_price();
	});

	amount_input.keyup(function(){
	//	console.log('amount_input',amount_input.val());
		calc_square();
		calc_price();
	});

//Plus and minus input buttons onClick events
	square_input.prev(minus_button).click(function(e) {
		e.preventDefault();
		let step_attr = square_input.attr('step');
		let step = (typeof step_attr !== 'undefined' && step_attr !== false) ? Number(step_attr) : 1;
		let t = square_input.nval() - step;
		t = square_check_limits(t);
		t = Math.round(t * 10) / 10;
		square_input.val(t);
		calc_amount();
		calc_price();
	});
	square_input.next(plus_button).click(function(e) {
		e.preventDefault();
		let step_attr = square_input.attr('step');
		let step = (typeof step_attr !== 'undefined' && step_attr !== false) ? Number(step_attr) : 1;
		let t = square_input.nval() + step;
		t = square_check_limits(t);
		t = (t).toFixed(1);
		square_input.val(t);
		calc_amount();
		calc_price();
	});
	amount_input.prev(minus_button).click(function(e) {
		e.preventDefault();
		let step_attr = amount_input.attr('step');
		let step = (typeof step_attr !== 'undefined' && step_attr !== false) ? Number(step_attr) : 1;
		let t = amount_input.nval() - step;
		t = amount_check_limits(t);
		amount_input.val(t);
		calc_square();
		calc_price();
	});
	amount_input.next(plus_button).click(function(e) {
		e.preventDefault();
		let step_attr = amount_input.attr('step');
		let step = (typeof step_attr !== 'undefined' && step_attr !== false) ? Number(step_attr) : 1;
		let t = amount_input.nval() + step;
		t = amount_check_limits(t);
		amount_input.val(t);
		calc_square();
		calc_price();
	});

//	console.log('plus_minus_input', plus_minus_input);
//	console.log('plus_minus_input.siblings()', plus_minus_input.siblings('a'));


	/* ---------- Add To Cart Ajax Plugin -------------*/
	// https://quadmenu.com/add-to-cart-with-woocommerce-and-ajax-step-by-step/

	jQuery(document).on('submit', '.single_add_to_cart_button_2', function (e) {
		e.preventDefault();

		var thisbutton = jQuery(this),
                //form = jQuery(thisbutton).closest('form'), //form.cart
                id = thisbutton.val(),
                product_qty = amount_input.val() || 1,
                product_id = product_params['product_id'] || id,
                variation_id = product_params[vr]['variation_id'] || 0;

                var data = {
                	action: 'woocommerce_ajax_add_to_cart',
                	product_id: product_id,
                	product_sku: '',
                	quantity: product_qty,
                	variation_id: variation_id,
                };

                jQuery(document.body).trigger('adding_to_cart', [thisbutton, data]);

                jQuery.ajax({
                	type: 'post',
                	url: wc_add_to_cart_params.ajax_url,
                	data: data,
                	beforeSend: function (response) {
                		thisbutton.removeClass('added').addClass('loading');
                		console.log('ajax data', data);
                	},
                	complete: function (response) {
                		thisbutton.addClass('added').removeClass('loading');
                		console.log('complete response', response);
                	},
                	success: function (response) {

                		console.log('success response', response);
                		product_params = response['product_params'];
                		console.log('product_params', product_params );

                		set_vars();
                		unit_stock_amount.text(availabe_amount);

		            	if (response.error && response.product_url) {
		            		window.location = response.product_url;
		            		return;
		            	} else {
		            		jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, thisbutton]);
		            	}
		            },

        });

                return false;
            });





});