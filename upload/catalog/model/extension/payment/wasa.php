<?php

use Sdk\AccessToken;
use Sdk\Api;
use Sdk\Client;
use Sdk\Response;

require_once(DIR_SYSTEM.'library/Wasa.php');

class ModelExtensionPaymentWasa extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/wasa');

        $status = true;

        $method_data = [];

        if ($status) {
            $method_data = [
                'code'       => 'wasa',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('wasa_sort_order')
            ];
        }

        return $method_data;
    }

    public function getWidget($price)
    {
        $data['publishable_key'] = $this->config->get('payment_wasa_client_id');
        $data['wasa_secret_key'] = $this->config->get('payment_wasa_secret_key');
        $data['wasa_test_mode'] = $this->config->get('payment_wasa_test_mode');
        $wasa_test_mode  = false;

        if ($data['wasa_test_mode']) {
            $wasa_test_mode  = true;
        }

        $this->_client = new Client(
            $data['publishable_key'],
            $data['wasa_secret_key'],
            $wasa_test_mode
        );

        $response = $this->_client->get_monthly_cost_widget($price);

        return $response->data;
    }
}
