<?php

require_once(DIR_SYSTEM . 'library/wasa/wasa/client-php-sdk/Wasa.php');

class ModelExtensionPaymentWasaInvoice extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/wasa_invoice');

        return [
            'code'       => 'wasa_invoice',
            'title'      => $this->language->get('text_title'),
            'terms'      => '',
            'sort_order' => $this->config->get('wasa_sort_order')
        ];
    }
}
