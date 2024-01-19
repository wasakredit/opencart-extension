<?php

namespace Opencart\Catalog\Controller\Extension\WasaKredit\Payment;

class WasaLeasing extends \Opencart\System\Engine\Controller
{
    private string $extension_path = 'extension/wasa_kredit/payment/wasa_leasing';
    private string $extension_code = 'payment_wasa_leasing';

    public function index(): string
    {
        $this->load->language($this->extension_path);

        $this->load->model('localisation/country');
        $this->load->model('extension/wasa_kredit/helper/gateway');

        $client = $this->model_extension_wasa_kredit_helper_gateway->getClient($this->extension_code);

        $payment_address = !empty($this->session->data['payment_address'])
            ? $this->session->data['payment_address']
            : [];

        $payment_country = !empty($payment_address)
            ? $this->model_localisation_country->getCountry($payment_address['country_id'])
            : [];

        $shipping_address = !empty($this->session->data['shipping_address'])
            ? $this->session->data['shipping_address']
            : [];

        $shipping_country = !empty($shipping_address)
            ? $this->model_localisation_country->getCountry($shipping_address['country_id'])
            : [];

        if ($this->customer->isLogged()) {
            $name = sprintf('%s %s', $this->customer->getFirstName(), $this->customer->getLastName());
            $email = $this->customer->getEmail();
            $phone = $this->customer->getTelephone();
        } elseif (isset($this->session->data['customer'])) {
            $name = sprintf('%s %s', $this->session->data['customer']['firstname'], $this->session->data['customer']['lastname']);
            $email = $this->session->data['customer']['email'];
            $phone = $this->session->data['customer']['telephone'];
        }

        foreach ($this->cart->getProducts() as $product) {
            $price_ex_vat = $product['price'];
            $vat_rate = $this->tax->getTax(100, $product['tax_class_id']);
            $vat_amount = $this->tax->getTax($price_ex_vat, $product['tax_class_id']);

            $products[] = [
                'product_id'     => $product['product_id'],
                'product_name'   => $product['name'],
                'quantity'       => $product['quantity'],
                'vat_percentage' => $vat_rate,
                'price_ex_vat' => [
                    'amount'   => sprintf('%0.2f', $price_ex_vat),
                    'currency' => $this->session->data['currency']
                ],
                'vat_amount' => [
                    'amount'   => sprintf('%0.2f', $vat_amount),
                    'currency' => $this->session->data['currency']
                ]
            ];
        }

        $shipping_cost = !empty($this->session->data['shipping_method']['cost'])
            ? $this->session->data['shipping_method']['cost']
            : 0;

        $shipping_cost = $this->currency->format($shipping_cost, $this->session->data['currency'], false, false);

        $payload = [
            'order_references' => [
                [
                    'key'   => 'partner_order_number',
                    'value' => $this->session->data['order_id'],
                ],
            ],
            'delivery_address' => [
                'company_name'   => $shipping_address['company'] ?? null,
                'street_address' => $shipping_address['address_1'] ?? null,
                'postal_code'    => $shipping_address['postcode'] ?? null,
                'city'           => $shipping_address['city'] ?? null,
                'country'        => $shipping_country['name'] ?? null,
            ],
            'billing_address' => [
                'company_name'   => $payment_address['company'] ?? null,
                'street_address' => $payment_address['address_1'] ?? null,
                'postal_code'    => $payment_address['postcode'] ?? null,
                'city'           => $payment_address['city'] ?? null,
                'country'        => $payment_country['name'] ?? null,
            ],
            'shipping_cost_ex_vat' => [
                'amount'   => $shipping_cost,
                'currency' => $this->session->data['currency'],
            ],
            'cart_items'                   => $products ?? [],
            'customer_organization_number' => '',
            'purchaser_name'               => $name,
            'purchaser_email'              => $email,
            'purchaser_phone'              => $phone,
            'recipient_name'               => $name,
            'recipient_phone'              => $phone,
            'payment_types'                => 'leasing',
            'request_domain'               => $this->config->get('config_ssl'),
            'confirmation_callback_url'    => $this->url->link('checkout/success', '', true),
            'ping_url'                     => $this->url->link('extension/wasa_kredit/payment/wasa_leasing/callback', '', true),
        ];

        if ($this->session->data['currency'] != 'SEK') {
            $error_message = $this->language->get('error_currency');
        } else {
            $response = $client->create_leasing_checkout($payload);

            if (empty($response)) {
                $error_message = $this->language->get('error_client');
            } elseif (!empty($response->data['invalid_properties'][0]['error_message'])) {
                $error_message = $response->data['invalid_properties'][0]['error_message'];
            }
        }

        if (!empty($error_message)) {
            $data['error'] = $error_message;
        } else {
            $data['checkout'] = is_string($response->data ?? null) ? $response->data : null;
        }

        $data['test_mode'] = $this->config->get($this->extension_code . '_test_mode');
        $data['order_id'] = $this->session->data['order_id'];

        $data['create_url'] = $this->url->link('extension/wasa_kredit/payment/wasa_leasing|confirm', '', true);
        $data['success_url'] = $this->url->link('checkout/success', '', true);
        $data['error_url'] = $this->url->link('checkout/failure', '', true);
        $data['cancel_url'] = $this->url->link('checkout/checkout', '', true);

        $data['language'] = $this->config->get('config_language');

        return $this->load->view($this->extension_path, $data);
    }

    public function confirm(): void
    {
        $this->load->language($this->extension_path);

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->error('HTTP/1.0 405 Method Not Allowed', 'Method not allowed');
        } elseif (empty($this->request->post['data'])) {
            $this->error('HTTP/1.1 400 Bad Request', 'Payload is empty');
        }

        foreach ($this->request->post['data'] as $property) {
            switch ($property['key']) {
                case 'wasakredit-order-id':
                    $wasa_order_id = strval($property['value']);
                    break;

                case 'partner_order_number':
                    $order_id = intval($property['value']);
                    break;
            }
        }

        if (empty($wasa_order_id) || empty($order_id)) {
            $this->error('HTTP/1.1 400 Bad Request', 'References is empty');
        }

        $this->load->model('checkout/order');
        $this->load->model('extension/wasa_kredit/helper/order');

        $this->model_extension_wasa_kredit_helper_order->addWasaOrderId($order_id, $wasa_order_id);

        $order = $this->model_checkout_order->getOrder($order_id);

        if ($order['order_status_id'] > 0) {
            $this->success(null);
        }

        $order_status = $this->config->get($this->extension_code . '_default_order_status_id');

        $message = sprintf('Signering slutförd av kunden (%s)', $wasa_order_id);

        try {
            $this->model_checkout_order->addHistory($order_id, $order_status, $message, true);
        } catch (Exception $e) {
            if (method_exists($e, 'getMessage')) {
                $this->log->write($e->getMessage());
            }
        }

        $this->success(null);
    }

    public function callback()
    {
        $this->load->language($this->extension_path);

        $request = (strpos($this->request->server['CONTENT_TYPE'], 'application/json') !== false)
            ? json_decode(file_get_contents('php://input'), true)
            : $this->request->post;

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->error('HTTP/1.0 405 Method Not Allowed', 'Method not allowed');
        } elseif (empty($request['order_id'])) {
            $this->error('HTTP/1.1 400 Bad Request', 'Order id is empty');
        } elseif (empty($request['order_status'])) {
            $this->error('HTTP/1.1 400 Bad Request', 'Order status is empty');
        } elseif ($request['order_status'] === 'initialized') {
            $this->success(null);
        } elseif ($request['order_status'] === 'pending') {
            $this->success(null);
        }

        $wasa_order_id = $request['order_id'];
        $wasa_order_status = $request['order_status'];

        if (!in_array($wasa_order_status, ['ready_to_ship'])) {
            $this->success(null);
        }

        $this->load->model('extension/wasa_kredit/helper/gateway');

        $client = $this->model_extension_wasa_kredit_helper_gateway->getClient($this->extension_code);

        $response = $this->client->get_order($wasa_order_id);

        foreach ($response->data['order_references'] as $property) {
            switch ($property['key']) {
                case 'wasakredit-order-id':
                    $wasa_order_id = strval($property['value']);
                    break;

                case 'partner_order_number':
                    $order_id = intval($property['value']);
                    break;
            }
        }

        if (empty($wasa_order_id) || empty($order_id)) {
            $this->error('HTTP/1.1 400 Bad Request', 'References is empty');
        }

        $this->load->model('checkout/order');
        $this->load->model('extension/wasa_kredit/helper/order');

        $this->model_extension_wasa_kredit_helper_order->addWasaOrderId($order_id, $wasa_order_id);

        $order_status = $this->config->get($this->extension_code . '_default_order_status_id');

        if (!empty($order_status)) {
            $message = sprintf('Avtalet godkänt av Wasa (%s)', $wasa_order_id);

            try {
                $this->model_checkout_order->addOrderHistory($order_id, $order_status, $message, false);
            } catch (Exception $e) {
                if (method_exists($e, 'getMessage')) {
                    $this->log->write($e->getMessage());
                }
            }
        }

        $this->success(null);
    }

    private function success(string $message = null): void
    {
        $json = json_encode([
            'success' => true,
            'message' => $message,
        ]);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($json);
        $this->response->output();
        die();
    }

    private function error(string $header, string $message = null): void
    {
        $this->response->addHeader($header);
        $this->response->setOutput($message);
        $this->response->output();
        die();
    }
}
