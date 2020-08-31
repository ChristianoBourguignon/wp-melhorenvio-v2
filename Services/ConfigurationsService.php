<?php

namespace Services;

use Controllers\ConfigurationController;
use Models\Address;
use Models\Agency;
use Models\Option;
use Models\CalculatorShow;
use Models\JadlogAgenciesShow;
use Models\UseInsurance;
use Models\Seller;

class ConfigurationsService
{
    /**
     * Function to save the salesperson's settings.
     *
     * @param array $data
     * @return array
     */
    public function saveConfigurations($data)
    {
        $response = [];

        (new Seller())->destroy();
        (new SessionService())->destroy(SellerService::USER_SESSION);

        if (isset($data['address'])) {
            $response['address'] = (new Address())->setAddressShopping(
                $data['address']
            );
        }

        if (isset($data['store'])) {
            $response['store'] = (new StoreService())->setStore($data['store']);
        }

        if (isset($data['agency'])) {
            $response['agency'] = (new Agency())->setAgency($data['agency']);
        }

        if (isset($data['show_calculator'])) {
            $response['show_calculator'] = (new CalculatorShow())->set(
                $data['show_calculator']
            );
        }

        if (isset($data['show_all_agencies_jadlog'])) {
            $response['show_all_agencies_jadlog'] = (new JadlogAgenciesShow())
                ->set($data['show_all_agencies_jadlog']);
        }

        if (isset($data['where_calculator'])) {
            $response['where_calculator'] = $this->setWhereCalculator(
                $data['where_calculator']
            );
        }

        if (isset($data['path_plugins'])) {
            $response['path_plugins'] = $this->savePathPlugins(
                $data['path_plugins']
            );
        }

        if (isset($data['options_calculator'])) {
            $response['options_calculator'] = $this->setOptionsCalculator(
                $data['options_calculator']
            );
        }

        return $response;
    }

    /**
     * Function to search all user settings
     *
     * @return array
     */
    public function getConfigurations()
    {
        $responseAgencies = (new Agency())->get();

        return [
            'addresses'           => (new Address())->getAddressesShopping()['addresses'],
            'stores'              => (new StoreService())->getStores(),
            'agencies'            => $responseAgencies['agencies'],
            'allAgencies'         => $responseAgencies['allAgencies'],
            'agencySelected'      => $responseAgencies['agencySelected'],
            'calculator'          => (new CalculatorShow())->get(),
            'all_agencies_jadlog' => (new JadlogAgenciesShow())->get(),
            'use_insurance'       => (new UseInsurance())->get(),
            'where_calculator'    => (!get_option('melhor_envio_option_where_show_calculator'))
                ? 'woocommerce_before_add_to_cart_button'
                : get_option('melhor_envio_option_where_show_calculator'),
            'metodos'             => (new OptionsMethodShippingService())->get(),
            'services_codes'      => (new ShippingMelhorEnvioService())->getCodesEnableds(),
            'path_plugins'        => $this->getPathPluginsArray(),
            'options_calculator'  => $this->getOptionsCalculator()
        ];
    }

    /**
     * Function to save the moment that will be called the product calculator
     *
     * @param string $option
     * @return void
     */
    public function setWhereCalculator($option)
    {
        delete_option('melhor_envio_option_where_show_calculator');
        add_option('melhor_envio_option_where_show_calculator', $option);

        return [
            'success' => true,
            'option' => $option
        ];
    }

    /**
     * Function to save receipt and own hands options
     *
     * @param array $options
     * @return array
     */
    public function setOptionsCalculator($options)
    {
        delete_option(Option::OPTION_RECEIPT);
        delete_option(Option::OPTION_OWN_HAND);
        delete_option(Option::OPTION_INSURANCE_VALUE);

        add_option(Option::OPTION_RECEIPT, $options['receipt'], true);
        add_option(Option::OPTION_OWN_HAND, $options['own_hand'], true);
        add_option(Option::OPTION_INSURANCE_VALUE, $options['insurance_value'], true);


        return [
            'success' => true,
            'options' => $this->getOptionsCalculator()
        ];
    }

    /**
     * Function to save the plugin's directory path
     *
     * @param stroing $path
     * @return string
     */
    public function savePathPlugins($path)
    {
        delete_option('melhor_envio_path_plugins');
        add_option('melhor_envio_path_plugins', $path);

        return get_option('melhor_envio_path_plugins');
    }

    /**
     * Function for obtaining acknowledgment options and own hands
     *
     * @return array
     */
    public function getOptionsCalculator()
    {
        return [
            'receipt' => filter_var(get_option(Option::OPTION_RECEIPT, "false"), FILTER_VALIDATE_BOOLEAN),
            'own_hand' => filter_var(get_option(Option::OPTION_OWN_HAND, "false"), FILTER_VALIDATE_BOOLEAN),
            'insurance_value' => filter_var(get_option(Option::OPTION_INSURANCE_VALUE, "true"), FILTER_VALIDATE_BOOLEAN)
        ];
    }

    /**
     * Function to get path plugin.
     *
     * @return string
     */
    public function getPathPluginsArray()
    {
        $path = get_option('melhor_envio_path_plugins');

        if (!$path) {
            $path = ABSPATH . 'wp-content/plugins';
        }

        return $path;
    }
}
