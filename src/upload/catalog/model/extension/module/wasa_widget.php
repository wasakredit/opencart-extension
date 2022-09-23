<?php

require_once(DIR_SYSTEM . 'library/wasa/wasa/client-php-sdk/Wasa.php');

class ModelExtensionModuleWasaWidget extends Model
{
    public function getWidget($product, $format = 'small')
    {
        if (!$this->config->get('payment_wasa_leasing_show_widget')) {
            return;
        }

        $this->client = Sdk\ClientFactory::CreateClient(
            $this->config->get('payment_wasa_leasing_client_id'),
            $this->config->get('payment_wasa_leasing_secret_key'),
            $this->config->get('payment_wasa_leasing_test_mode')
        );

        $price = !empty($product['special'])
            ? $this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax'))
            : $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

        $response = $this->client->get_monthly_cost_widget($price, $format);

        return $this->load->view('extension/module/wasa_widget', [
            'snippet' => $response->data ?? null
        ]);
    }
}
