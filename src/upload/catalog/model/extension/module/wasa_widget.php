<?php

require_once(DIR_SYSTEM . 'library/wasa/wasa/client-php-sdk/Wasa.php');

class ModelExtensionModuleWasaWidget extends Model
{
    public function getWidget($product, $format = 'small-no-icon')
    {
        if (!$this->config->get('payment_wasa_leasing_show_widget') && !$this->config->get('payment_wasa_invoice_show_widget')) {
            return;
        }

        if ($this->config->get('payment_wasa_leasing_show_widget') && $this->config->get('payment_wasa_leasing_status')) {
            $extension = 'payment_wasa_leasing';
        } elseif ($this->config->get('payment_wasa_invoice_show_widget') && $this->config->get('payment_wasa_invoice_status')) {
            $extension = 'payment_wasa_invoice';
        } else {
            return;
        }

        $format = !empty($this->config->get($extension . '_widget_size'))
            ? $this->config->get($extension . '_widget_size')
            : $format;

        $this->client = Sdk\ClientFactory::CreateClient(
            $this->config->get($extension . '_client_id'),
            $this->config->get($extension . '_secret_key'),
            $this->config->get($extension . '_test_mode')
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
