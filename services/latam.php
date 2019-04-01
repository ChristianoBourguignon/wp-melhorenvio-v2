<?php 

use Controllers\PackageController;
use Controllers\CotationController;
use Controllers\ProductsController;
use Controllers\TimeController;
use Controllers\MoneyController;
use Controllers\OptionsController;

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    add_action( 'woocommerce_shipping_init', 'latam_shipping_method_init' );
	function latam_shipping_method_init() {
		if ( ! class_exists( 'WC_Latam_Shipping_Method' ) ) {

			class WC_Latam_Shipping_Method extends WC_Shipping_Method {

                public $code = '10';
				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct($instance_id = 0) {
					$this->id                 = "latam"; 
                    $this->instance_id = absint( $instance_id );
                    $this->method_title       = "Latam (Melhor envio)"; 
					$this->method_description = 'Serviço Latam';
					$this->enabled            = "yes"; 
					$this->title              = isset($this->settings['title']) ? $this->settings['title'] : 'Melhor Envio Latam';
                    $this->supports = array(
                        'shipping-zones',
                        'instance-settings',
                    );
					$this->init_form_fields();
				}
				
				/**
				 * Init your settings
				 *
				 * @access public
				 * @return void
				 */
				function init() {
					$this->init_form_fields(); 
					$this->init_settings(); 
					add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
				}

				/**
				 * calculate_shipping function.
				 *
				 * @access public
				 * @param mixed $package
				 * @return void
				 */
				public function calculate_shipping( $package = []) {
					
					global $woocommerce;
					$to = str_replace('-', '', $package['destination']['postcode']);

					$prod = new ProductsController();
					$products = $prod->getProductsCart();
					
					$cotation = new CotationController();			
					
					if ($result = $cotation->makeCotationproducts($products, [$this->code], $to)) {

						if (isset($result->name) && isset($result->price)) {

							$method = (new optionsController())->getName($result->id, $result->name, '');

							$rate = [
								'id' => 'melhorenvio_latam',
								'label' => $method['method'] . (new timeController)->setLabel($result->delivery_range, $this->code),
								'cost' => (new MoneyController())->setprice($result->price, $this->code),
								'calc_tax' => 'per_item',
								'meta_data' => [
									'delivery_time' => $result->delivery_time,
									'company' => 'Latam'
								]
							];
							$this->add_rate($rate);	
						}
					} else {
						return false;
					}
                }
                
                /**
				 * Initialise Gateway Settings Form Fields
				 */
				function init_form_fields() {

					$this->form_fields = [
						'title' => [
							'title' => 'Titulo',
							'type' => 'text',
							'default' => 'Latam'
						],
						'enabled' => [
							'title' => 'Ativar',
							'type' => 'checkbox',
							'default' => 'yes'
						],
					];
				}   
			}
		}
	}
	
	function add_latam_shipping_method( $methods ) {
		$methods['latam'] = 'WC_Latam_Shipping_Method';
		return $methods;
	}
	add_filter( 'woocommerce_shipping_methods', 'add_latam_shipping_method' );
}
