<?php 
/**
 * 
 */
class VA_PC_Wood_Price_Calculator
{
	
	function __construct()
	{

		add_action( 'wp_enqueue_scripts', array($this, 'register_js_css' ));

		//add_action( 'woocommerce_init', array($this, 'get_product_variable' ));

		add_action( 'woocommerce_before_quantity_input_field', array($this, 'form_for_hooks' ));
		add_filter( 'woocommerce_cart_item_quantity', array($this, 'add_ks_to_cart'), 80, 3 );
		add_filter( 'woocommerce_cart_item_quantity', array($this, 'add_square_to_main_cart'), 1000, 3 );
		add_filter( 'woocommerce_widget_cart_item_quantity', array($this, 'add_square_to_widget_cart'), 1000, 3 );
		add_filter( 'woocommerce_checkout_cart_item_quantity', array($this, 'add_square_to_widget_cart'), 1000, 3 );
		add_filter( 'woocommerce_email_order_item_quantity', array($this, 'add_ks_to_email'), 80, 2 );
		add_filter( 'woocommerce_email_order_item_quantity', array($this, 'add_square_to_email'), 1000, 2 );
		add_action( 'woocommerce_cart_contents', array($this, 'set_vars_enqueue_js_css_to_cart'));

		add_action('wp_ajax_woocommerce_ajax_add_to_cart', array($this, 'woocommerce_ajax_add_to_cart'));
		add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', array($this, 'woocommerce_ajax_add_to_cart'));


	}
	//TODO. Make a single Product variable remove the need to recreate it in every function. 
	// See functions "get_input_to_cart()", "add_square_to_cart()" "get_refreshed_fragments()".
	// WC_Product_Variable object can be used to get data abount variations, and it conatian (or can call) each of them
/*	function get_product_variable() {
		$this->$productVariable = new WC_Product_Variable();
	}*/

	// Register scripts and style. Not turned on yet.
	static function register_js_css(){
		//wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js');
		wp_register_script( 'VA_PC_Wood_Price_Calculator_Script', plugins_url('/js/script.js', __FILE__ ), array( 'jquery' ), '1.0');
		wp_register_script( 'VA_PC_Wood_Price_Calculator_Cart_Script', plugins_url('/js/cart.js', __FILE__ ), array( 'jquery' ), '1.0');

		wp_register_style( 'VA_PC_Wood_Price_Calculator_Style', plugins_url('/css/style.css', __FILE__ ));
		wp_register_style( 'VA_PC_Wood_Price_Calculator_Cart_Style', plugins_url('/css/cart.css', __FILE__ ));
		/*wp_enqueue_style( 'VA_PC_Wood_Price_Calculator_Style');
		wp_enqueue_script('VA_PC_Wood_Price_Calculator_Script');*/
	}

	// Get calculator from. Enqueue styles, scripts, when it using
	public function get_form() {
		global $product;

		wp_enqueue_style( 'VA_PC_Wood_Price_Calculator_Style');
		if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js');
		}
		wp_localize_script( 'VA_PC_Wood_Price_Calculator_Script' , 'product_params', $this->set_vars_to_js($product) );
		wp_enqueue_script('VA_PC_Wood_Price_Calculator_Script');

/* ----	Debug start ---- 
			//global $post;
		?><div id="wp-query--meta" style="display: none;"> <?php 
		print_r(get_post_meta( get_the_ID() ));
		?></div> <?php

		//$this->get_refreshed_fragments();
	    //	VA_PC_Wood_Price_Calculator::get_refreshed_fragments();

			//global $product;
			?><div id="wp-query--woocommerce--$product" style="display: none;"> <?php 
			global $product;	
			print_r($product);
			?><br><p>Вариации продукта</p><br><?php
			print_r($product->get_available_variations());

			//print_r($this->$productVariable);
			//print_r($this->$productVariable->get_available_variations($product));\
		//	print_r(WC());
			?></div> <?php
----	Debug end ----  */ 

		ob_start();
      //include the specified file
		include_once __DIR__ . '/inc/form.php';
	  //assign the file output to $content variable and clean buffer
		$form = ob_get_clean();

		return $form;


		}
	// Wrap of "get_form()" function to make avaliability of turning on an off it using Post Meta
	function form_for_hooks() {
/*
	?> <div id="in-array-Wood_Price_Calculator" style="display: none;"> <?php 
	print_r(get_post_meta( get_the_ID(),  'VA_Price_Calculator' ));
	?> </div> <?php
*/
	//	if( in_array( 'Wood_Price_Calculator' ,  get_post_meta( get_the_ID(),  'VA_Price_Calculator' )) ) {
		$meta = get_post_meta( get_the_ID(),  'VA_Price_Calculator' );
		if( !empty( $meta ) && is_array( $meta ) && in_array( 'Wood_Price_Calculator' , $meta ) ) {
			echo $this->get_form();
		}

	}

	function get_input_to_cart( $default, $cart_item ) {

		$variableProduct = new WC_Product_Variable();
		$pv = $variableProduct->get_available_variation( $cart_item['variation_id'] );
		$this->add_vars_to_cart_js($pv, $cart_item);

		ob_start();
      //include the specified file
		include __DIR__ . '/inc/cart-square-input.php';
      //assign the file output to $content variable and clean buffer
		$form = ob_get_clean();

		return $default . $form;
	}

	// Hook to rewrite email Quantity by adding "ks"
	function add_ks_to_email( $default, $item ) {
		return $default . '&nbsp;' . __( 'ks', 'woocommerce' );
	}

	// Fuction for hook to adding square in cart items. See
	function add_square_to_cart( $default, $cart_item_key = '', $cart_item ) {

		if(!is_array($cart_item)) {
			$default .= var_dump($cart_item);
			$default .= var_dump($cart_item_key);
			return $default;
		}

		$variableProduct = new WC_Product_Variable();
		$pv = $variableProduct->get_available_variation( $cart_item['variation_id'] );

				$default .= '<div class="square">';
				//Mathematic formula of square calculation
				$square = number_format(
					intval($cart_item['quantity']) * intval($pv['dimensions']['length']) * intval($pv['dimensions']['width']) / 10**6 
					, 1, ',', '');
				$default .=  '<label class="square"><span class="square-value">' . $square . '</span><span class="units">&nbsp;' . __('m²', 'woocommerce') . '</span></label>';
				$default .= '</div>';

				return $default;

	}
	//Hook rewrite all Quantity items by giving Ks to each of them
	function add_ks_to_cart( $default, $cart_item_key, $cart_item ) {
		// Push .units label inside "<div class="quantity">"
		$default = substr( $default, 0, strripos($default, '</div>') - strlen( $default ) ); // Delete last </div>
		//Note: 
		//   strripos() - Finds the position of the last occurrence of a string inside another string (case-insensitive).
		//                Return position number countdown from the start. 
		//	 strlen()   - Returns the length of a string. 
		//	 substr()   - Returns a part of a string. Negative 3-rd param means that the length number
		//           	  to be returned countdowning from the end of the string.
		//           	
		//   As result:   We cut (substr) line to the lenght, setupped by negative offset from the end of the line,
		//                which is obtained by substration full length of the line (strlen) from postion of </div>
		//                (strripos) whitch equivalently length line before it.

		ob_start(); ?>   
		
			<label class="units"><?php echo _e('Ks', 'woocommerce') ?></label>
		
		<?php 
		$default .= ob_get_clean();
		$default .= '</div>'; // Adding back </div>

		return $default;
	}
    // Wraps to the fuction. 
    // Check to enabled metafield. Required "metafield => value" couple can be changed for each wrap.
    //
	function add_square_to_main_cart( $default, $cart_item_key, $cart_item ) {
		$meta = get_post_meta( $cart_item['product_id'],  'VA_Price_Calculator' );
		if( !empty( $meta ) && is_array( $meta ) && in_array( 'Wood_Price_Calculator' , $meta ) ) {
			//return $this->add_square_to_cart( $default, $cart_item_key, $cart_item );
			$default = '<div class="inputs">' . $this->get_input_to_cart( $default, $cart_item ) . '</div>' ;
			return $default;
		} else {
			return $default;
		}
	}

	function add_square_to_widget_cart( $default, $cart_item, $cart_item_key ) { //Because hooks variable reversed in Woocommerce core
		$meta = get_post_meta( $cart_item['product_id'],  'VA_Price_Calculator' );
		if( !empty( $meta ) && is_array( $meta ) && in_array( 'Wood_Price_Calculator' , $meta ) ) {
			return $this->add_square_to_cart( $default, $cart_item_key, $cart_item ); 
		} else {
			return $default;
		}
	}

	function add_square_to_email( $default, $item ) {
		$meta =  get_post_meta( $item->get_product_id(),  'VA_Price_Calculator' );
		if( !empty( $meta ) && is_array( $meta ) && in_array( 'Wood_Price_Calculator' , $meta ) ) {
			$as_cart_item = array(
				'product_id' => $item->get_product_id(),
				'variation_id' => $item->get_variation_id(),
				'quantity' => $item->get_quantity()
			);
			return $this->add_square_to_cart( $default, '', $as_cart_item );
		} else {
			return $default;
		}
	}


	// Get WooCommerce parameters and makes suitable array for sending into JS
	public static function set_vars_to_js( $product ) {
		global $woocommerce;
		$pv_wc_array = $product->get_available_variations();
		$product_prices = $product->get_price();
		$wc_cart_contents = $woocommerce->cart->cart_contents;

		//$post_meta_array = get_post_meta(get_the_ID());

/* ----	Debug start ---- 
		?><div id="wp-query--woocommerce--$product" style="display: none;"> <?php 
		print_r($product);
		?><br><p>Вариации продукта</p><br><?php
		print_r($pv_wc_array);
		print_r($product_prices);

		
		echo '$woocommerce->cart->cart_contents -> ';
		print_r($wc_cart_contents);
		echo '$post-meta-2';
		print_r($post_meta_array);
		?></div> <?php
 ----	Debug end ---- */
		
		foreach ($wc_cart_contents as $key => $cart_item) {
			$vr_in_cart[$cart_item['variation_id']] = $cart_item['quantity'];
		}

		$loc_pv['product_id'] = $product->get_id();

		foreach ($pv_wc_array as $key => $pv) {
			$vr_id = $pv['variation_id'];
			$loc_pv[$vr_id]['length'] = intval($pv['dimensions']['length']);
			$loc_pv[$vr_id]['width'] = intval($pv['dimensions']['width']);
			$loc_pv[$vr_id]['price'] = floatval($pv['display_price']);

			$loc_pv[$vr_id]['variation_id'] = $pv['variation_id'];

			$variation_obj = new WC_Product_variation($pv['variation_id']);
			$loc_pv[$vr_id]['variation_stock_quantity'] = $variation_obj->get_stock_quantity();
			$loc_pv[$vr_id]['variation_incart_quantity'] = $vr_in_cart[$pv['variation_id']] ?? 0;

    /* ----	Debug start ---- 

		    ?><div id="wp-query--woocommerce--$pv" style="display: none;"> <?php 

			$WC_Product_Variable = new WC_Product_Variable();
			$vr_test = $WC_Product_Variable->get_available_variation( $vr_id );

			echo '$vr_test[`'. $pv['variation_id'] . '`] -> ';
			print_r($vr_test);
			*/
		/*	echo '$pv[' . $key . '] -> ';
			print_r($pv);
			//$variation_obj = new WC_Product_variation($pv['variation_id']);
			echo 'variation_obj['. $pv['variation_id'] . '] -> ';
			print_r($variation_obj);
			echo $variation_obj->get_attribute( 'length' );
			echo $WC_Product_Variation->get_attribute( $attribute );

			echo '$variation_data ->';
			$variation_data = $variation_obj->get_variation_attributes();
			$variation_detail = woocommerce_get_formatted_variation( $variation_data, true );
            echo '$variation_detail['. $pv['variation_id'] . '] -> ';
	        print_r($variation_detail);  

            ?></div> <?php            
     ----	Debug end ---- */   
        }

        return $loc_pv;	
			
	}

	//Add variables to cart array. Works on each hook itaration, if for the product plugin have turned on.
	function add_vars_to_cart_js( $pv, $cart_item ){

		$this->cart_product_params[$pv['variation_id']]['length'] = intval($pv['dimensions']['length']);
		$this->cart_product_params[$pv['variation_id']]['width'] = intval($pv['dimensions']['width']);

		$variation_obj = new WC_Product_variation($pv['variation_id']);
		$this->cart_product_params[$pv['variation_id']]['variation_stock_quantity'] = $variation_obj->get_stock_quantity();
	//	$this->cart_product_params[$pv['variation_id']]['variation_incart_quantity'] = $cart_item['variation_id'] ?? 0;

	}

	//Enqueue styles, scripts for tha cart; send vars to the script.
	function set_vars_enqueue_js_css_to_cart() {
		if(isset($this->cart_product_params) && !empty($this->cart_product_params)) {
			wp_enqueue_style( 'VA_PC_Wood_Price_Calculator_Cart_Style');
			wp_localize_script( 'VA_PC_Wood_Price_Calculator_Cart_Script' , 'product_params', $this->cart_product_params );
			wp_enqueue_script('VA_PC_Wood_Price_Calculator_Cart_Script');
		}
	}

	// Rewrited WooCommerce fuction "WC_AJAX::get_refreshed_fragments()"
	static function get_refreshed_fragments() {
		ob_start();

		woocommerce_mini_cart();

		$mini_cart = ob_get_clean();

		$product_params = VA_PC_Wood_Price_Calculator::set_vars_to_js( new WC_Product_Variable( $_POST['product_id'] ) );

		$data = array(
			'fragments' => apply_filters(
				'woocommerce_add_to_cart_fragments',
				array(
					'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
				)
			),
			'cart_hash' => WC()->cart->get_cart_hash(),
			'product_params' => $product_params,
		//	'new WC_Product_Variable( $_POST[product_id] )->get_available_variations()' => (new WC_Product_Variable( $_POST['product_id'] ))->get_available_variations(),
		//	'new WC_Product( $_POST[product_id] )' => new WC_Product( $_POST['product_id'] ),
		//	'$woocommerce' => $woocommerce,
		);

		//print_r($data);

		wp_send_json( $data );
	}

	// Custon ajax "add_to_cart".
	// https://quadmenu.com/add-to-cart-with-woocommerce-and-ajax-step-by-step/
	function woocommerce_ajax_add_to_cart() {

		$product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
		$quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
		$variation_id = absint($_POST['variation_id']);
		$passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
		$product_status = get_post_status($product_id);

		if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id) && 'publish' === $product_status) {

			do_action('woocommerce_ajax_added_to_cart', $product_id);

			if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
				wc_add_to_cart_message(array($product_id => $quantity), true);
			}

			//WC_AJAX :: get_refreshed_fragments();
			
			VA_PC_Wood_Price_Calculator::get_refreshed_fragments();

		} else {

			$data = array(
				'error' => true,
				'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id));

			echo wp_send_json($data);
		}

		wp_die();
	}


	}
	//Initialisation of class. 
	//This .php file dirictly included in "asseble.php". "$this" is reference for "VA_Price_Calculator".
	$this->calculators['Wood_Price_Calculator'] = new VA_PC_Wood_Price_Calculator();

	?>