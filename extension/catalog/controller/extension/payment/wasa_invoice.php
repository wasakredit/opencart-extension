<?php
namespace Opencart\Catalog\Controller\Extension\WasaKredit\Payment;

class WasaInvoice extends \Opencart\System\Engine\Controller
{
    private string $extension_path = 'extension/wasakredit/payment/wasa_invoice';
    private string $extension_code = 'payment_wasa_invoice';

    public function index(): string
    {
        $this->loadExtensionLanguage();

        $data['language'] = $this->config->get('config_language');

        return $this->load->view($this->extension_path, $data);
    }

    public function confirm(): void
    {
        $this->loadExtensionLanguage();

        $json = [];

        if (!isset($this->session->data['order_id'])) {
            $json['error'] = $this->language->get('error_order');
        }

        if (!isset($this->session->data['payment_method']) || $this->session->data['payment_method'] != str_replace('payment_', '', $this->extension_code)) {
            $json['error'] = $this->language->get('error_payment_method');
        }

        if (!$json) {
            $this->load->model('checkout/order');

            $this->model_checkout_order->addHistory(
                $this->session->data['order_id'],
                $this->config->get($this->extension_code . '_order_status_id')
            );

            $json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function loadExtensionLanguage(): void
    {
        $this->load->language($this->extension_path);
    }
}
