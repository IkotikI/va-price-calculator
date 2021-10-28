<?php 
/*
* Main assembling class of the plugin.
* New units incding directly in "Includs units" section
* 
*/
class VA_Price_Calculator {

	public $calculators = array();

	function __construct() {
		/* ---- Includs units section start ---- */
		include 'forms/wood-price-calculator/calculator.php';
		/* ---- Includs units section end ---- */

		// Add shortcodes.
		add_shortcode( 'VA_Price_Calculator', array($this, 'VA_Price_Calculator_Shotcode') );

	}
	/* Wood Price Calculator */

	function VA_Price_Calculator_Shotcode( $atts ) {

	if(isset($this->calculators[ $atts['name'] ])) {
		return $this->calculators[ $atts['name'] ]::get_form();
	}
	else {
		echo ' Ищи ошибки ';
		print_r($atts);
		print_r($this->calculators);
		return $atts['name'] . ' не определён';
	}

	}


}

new VA_Price_Calculator();

?>