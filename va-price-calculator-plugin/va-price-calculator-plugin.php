<?php
/**
 * 
 * @package           VA Price Calculator
 * @author            Vladislav Artyukhov
 * @copyright         Vladislav Artyukhov 2021
 * 
 * Plugin Name: VA Price Calculator
 * Description: Custumable, half-developer plugin for making price calculator for all purpoes.
 * Plugin URI: 
 * Author: Vladislav Artyukhov
 * Version: 0.0.1
 * Author URI: https://vladislav-artyukhov.s-d-i.space/
 *
 * Text Domain: va-price-calculator

 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'VA_CALC__FILE', __DIR__ );

require VA_CALC__FILE . '/assemble/assemble.php';