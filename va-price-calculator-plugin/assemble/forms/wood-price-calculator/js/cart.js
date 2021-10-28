jQuery(function() {

	jQuery.fn.nval = function() {
		return Number(this.val())
	};

	function numberWithSpaces(x) {
		var parts = x.toString().split(".");
		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
		return parts.join(",");
	}

// Wrapper need to reinitialize calculators after ajax
// See: "jQuery( document ).on( 'updated_cart_totals', function() { <...> } " in the end of file
    function each_inputs_init() {

	var inputs = jQuery('.product-quantity .square'),
	cart_button = jQuery('button[name="update_cart"]');

	console.log('product_params', product_params);
	console.log('inputs', inputs);

// In main code copypasted from script.js, but there we do it for `each` product in cart
	inputs.each(function(){

		var
		form = jQuery(this).closest('.product-quantity'),
		square_input = form.find('.square input'),
		amount_input = form.find('.quantity input'),
		plus_button = form.find('.square .wpc-plus'),
		minus_button = form.find('.square .wpc-minus');

		console.log('form', form);
		console.log('elements',square_input, amount_input, plus_button, minus_button);

		var width, length, vr, stock_amount, incart_amount, availabe_amount;

		function set_vars() {
			var _id = square_input.attr('id');
			console.log('_id', _id);
			vr = (_id.includes('square-input-')) ? Number(_id.replace('square-input-', '')) : null;

			console.log('vr', vr);

			if (typeof vr !== 'undefined') {
				length = product_params[vr]['length'];
				width = product_params[vr]['width'];
			//	price = Number(product_params[vr]['price']);
				stock_amount = product_params[vr]['variation_stock_quantity'];
			//	incart_amount = product_params[vr]['variation_incart_quantity'];
				incart_amount = amount_input.nval();     //Why take from the backend, if we have it already? 
				availabe_amount = stock_amount - incart_amount;
			}
		}


		function amount_of_square($square) {
			return Math.ceil($square / ( length * width / 10**6))
		}

		function square_of_amount($amount) {
			return $amount * length * width / 10**6
		}

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

		function init_form() {
			set_vars();
			calc_square();
		}

		init_form();

		square_input.change(function(){
		//	console.log('square_input',square_input.val());
			square_input.val(square_check_limits(square_input.nval()));
			calc_amount();
		});

		amount_input.change(function(){
		//	console.log('amount_input',amount_input.val());
			amount_input.val(amount_check_limits(amount_input.nval()));
			calc_square();
		});

		square_input.keyup(function(){
		//	console.log('square_input',square_input.val());
		//	console.log('square_input nval',square_input.nval());
			calc_amount();
		});

		amount_input.keyup(function(){
		//	console.log('amount_input',amount_input.val());
			calc_square();
		});

		square_input.prev(minus_button).click(function(e) {
			e.preventDefault();
			let step_attr = square_input.attr('step');
			let step = (typeof step_attr !== 'undefined' && step_attr !== false) ? Number(step_attr) : 1;
			let t = square_input.nval() - step;
			t = square_check_limits(t);
			t = Math.round(t * 10) / 10;
			square_input.val(t);
			cart_button.prop( "disabled", false );
			calc_amount();
		});

		square_input.next(plus_button).click(function(e) {
			e.preventDefault();
			let step_attr = square_input.attr('step');
			let step = (typeof step_attr !== 'undefined' && step_attr !== false) ? Number(step_attr) : 1;
			let t = square_input.nval() + step;
			t = square_check_limits(t);
			t = (t).toFixed(1);
			square_input.val(t);
			cart_button.prop( "disabled", false );
			calc_amount();
		});

	});

	} //End of each_inputs_init()

//Onload initializaition
	each_inputs_init();

//On "updated_cart_totals" reinitialization after refresh cart ajax
	jQuery( document ).on( 'updated_cart_totals', function() {
		console.log('updated_cart_totals');
		each_inputs_init();
	});


});