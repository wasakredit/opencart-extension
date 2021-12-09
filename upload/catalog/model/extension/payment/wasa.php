<?php

require_once(DIR_SYSTEM . 'library/wasa/wasa/client-php-sdk/Wasa.php');

class ModelExtensionPaymentWasa extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/wasa');

        return [
            'code'       => 'wasa',
            'title'      => $this->language->get('text_title'),
            'terms'      => '',
            'sort_order' => $this->config->get('wasa_sort_order')
        ];
    }
}
