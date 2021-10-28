<?php  
global $product;
$pv_wc_array = $product->get_available_variations();
?>
<div id="wood-price-calculator" class="price-calculator">
	<form class="price-calculator">
		<div class="product-params">
			<div class="square-price-title">
				<label class="title"><?php echo _e('Cena za m²:', 'woocommerce') ?></label>
			</div>
			<div class="single-price">
				<label>
					<span class="square-price price"></span>
			   <!-- <span class="currency-label">Kč</span> -->
				</label>
				<label class="price-label"><?php echo _e('Kč/m² bez DPH²', 'va-price-calculator') ?> </label>
			</div>
			<div class="single-price">
				<label>
					<span class="square-price-taxed price"></span>
					<!-- <span class="currency-label">Kč</span> -->
				</label>
				<label class="price-label"><?php echo _e('Kč/m² vč. DPH', 'va-price-calculator') ?> </label>
			</div>
		</div>
		<div class="variations-section">
			<div class="variations-wrapper">
				<div class="wood-width-variations-label">
					<label class="title">Délka:</label>
				</div>
				<div class="wood-width-variations">
					<?php 
					$start_variation = 0;
					foreach ($pv_wc_array as $key => $pv) {
						?>
						<input id="wood-width-radio-<?php echo $pv['variation_id'] ?>" type="radio" name="wood-width" value="<?php echo $pv['variation_id'] ?>" 
					<?php //Set cheked radio button depend of stock availability
					if($pv['is_in_stock'] == 0) {
						if( $start_variation != -1 ) { 
							$start_variation += 1; 
						}
						echo 'disabled';
					}
					if($key == $start_variation) {
						$start_variation = -1;
						echo 'checked'; 
					}  ?>
					>
					<label class="block-radio" for="wood-width-radio-<?php echo $pv['variation_id'] ?>"><?php echo $pv['attributes']['attribute_pa_length']?> mm</label>
					<?php } ?>
				</div>
			</div>
			<div class="stock-amount">
			<?php  // If no available variation, print "Není skladem."
			if ( $start_variation != -1 ) {
			?>
				<label class="no-avaliable-variation-in-stock"><?php _e('Není skladem.', 'woocommerce') ?></label>	
				<?php
			} else {  // Else print Calculator ?>
				<label class="stock-amount-label"><?php echo _e('Skladem', 'woocommerce') ?></label>
				<label class="unit-stock-amount price"></label>
				<label class="pieces-label">ks</label>
			<?php } ?>
			</div>			
		</div>
		<?php if ( $start_variation == -1 ) { 
			/* ---- Start calculator ---- */ ?>
		<div class="price-section">
			<div class="single-price-section">
				<label class="title"><?php echo _e('Cena za ks:', 'woocommerce') ?></label>
				<div class="single-price">
					<label>
						<span class="unit-price price"></span>
						<!-- <span class="currency-label">Kč</span> -->
					</label>
					<label class="price-label"><?php echo _e('Kč/ks bez DPH', 'woocommerce') ?> </label>
				</div>
				<div class="single-price">
					<label> 
						<span class="unit-price-taxed price"></span>
						<!-- <span class="currency-label">Kč</span> -->
					</label>
					<label class="price-label"><?php echo _e('Kč/ks vč. DPH', 'woocommerce') ?> </label>
				</div>
			</div>
			<div class="total-price-section"> 
				<label class="title"><?php echo _e('Celkem:', 'woocommerce') ?></label>
				<div class="total-price-block">
					<label> 
						<span class="total-price price">0</span>
						<!-- <span class="currency-label">Kč</span> -->
					</label>
					<label class="price-label"><?php echo _e('Kč bez DPH', 'woocommerce') ?></label>
				</div>
				<div class="total-price-block">
					<label> 
						<span class="total-price-taxed price">0</span>
						<!-- <span class="currency-label">Kč</span> -->
					</label>
					<label class="price-label"><?php echo _e('Kč vč. DPH', 'woocommerce') ?></label>
				</div>
			</div>
		</div>
		<div class="values-inputs">
			<div class="inputs">
				<a class="wpc-minus">-</a>
				<input class="wpc-p-m" type="number" name="square" step="0.1" min="0">
				<a class="wpc-plus">+</a>
				<label><?php echo _e('m²', 'woocommerce') ?></label>
				<label class="available-square"><?php echo _e('In stock', 'woocommerce') ?>
				<span></span>
				<?php echo _e('m²', 'woocommerce') ?></label>
			</div>
			<div class="inputs">
				<a class="wpc-minus">-</a>
				<input class="wpc-p-m" type="number" name="amount" min="1" value="1">
				<a class="wpc-plus">+</a>
				<label><?php echo _e('ks', 'woocommerce') ?></label>
			</div>
		</div>

		<?php } /* ---- End calculator ---- */ ?> 
		<div class="submit-button">		
			<button class="single_add_to_cart_button_2 <?php if($start_variation != -1) echo 'disabled'; ?> button" <?php if($start_variation != -1) echo 'disabled'; ?>><?php echo _e('Add to cart', 'woocommerce') ?></button>
			<label>__</label>
		</div>
	</form>
</div>