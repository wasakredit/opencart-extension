<?php

namespace Opencart\Catalog\Controller\Extension\WasaKredit\Payment;

class WasaInvoice extends \Opencart\System\Engine\Controller
{
    private string $extension_path = 'extension/wasa_kredit/payment/wasa_invoice';
    private string $extension_code = 'payment_wasa_invoice';

    public function index(): string
    {
        $this->load->language($this->extension_path);

        $this->load->model('extension/wasa_kredit/helper/gateway');

        $client = $this->model_extension_wasa_kredit_helper_gateway->getClient($this->extension_code);

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
            $tax_rate = $this->tax->getTax(100, $product['tax_class_id']);
            $price_ex_vat = $product['price'];
            $price_incl_vat = $this->tax->calculate($price_ex_vat, $product['tax_class_id'], true);
            $vat_amount = $this->tax->getTax($price_ex_vat, $product['tax_class_id']);
            $total_price_ex_vat = $price_ex_vat * $product['quantity'];
            $total_price_incl_vat = $price_incl_vat * $product['quantity'];
            $total_vat = $vat_amount * $product['quantity'];

            $products[] = [
                'product_id'     => $product['product_id'],
                'product_name'   => $product['name'],
                'quantity'       => $product['quantity'],
                'vat_percentage' => $tax_rate,
                'vat_amount'     => [
                    'amount'   => sprintf('%0.2f', $vat_amount),
                    'currency' => $this->session->data['currency']
                ],
                'price_ex_vat' => [
                    'amount'   => sprintf('%0.2f', $price_ex_vat),
                    'currency' => $this->session->data['currency']
                ],
                'price_incl_vat' => [
                    'amount'   => sprintf('%0.2f', $price_incl_vat),
                    'currency' => $this->session->data['currency']
                ],
                'total_price_ex_vat' => [
                    'amount'   => sprintf('%0.2f', $total_price_ex_vat),
                    'currency' => $this->session->data['currency']
                ],
                'total_price_incl_vat' => [
                    'amount'   => sprintf('%0.2f', $total_price_incl_vat),
                    'currency' => $this->session->data['currency']
                ],
                'total_vat' => [
                    'amount'   => sprintf('%0.2f', $total_vat),
                    'currency' => $this->session->data['currency']
                ],
            ];
        }

        if (!empty($this->session->data['shipping_method'])) {
            $shipping = explode('.', $this->session->data['shipping_method']);
            $shipping_method = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];

            $tax_rate = $this->tax->getTax(100, $shipping_method['tax_class_id']);
            $price_ex_vat = $shipping_method['cost'];
            $price_incl_vat = $this->tax->calculate($price_ex_vat, $shipping_method['tax_class_id'], true);
            $vat_amount = $this->tax->getTax($price_ex_vat, $shipping_method['tax_class_id']);
            $total_price_ex_vat = $price_ex_vat * 1;
            $total_price_incl_vat = $price_incl_vat * 1;
            $total_vat = $vat_amount * 1;

            $products[] = [
                'product_id'     => $shipping_method['code'],
                'product_name'   => $shipping_method['title'],
                'quantity'       => 1,
                'vat_percentage' => $tax_rate,
                'vat_amount'     => [
                    'amount'   => sprintf('%0.2f', $vat_amount),
                    'currency' => $this->session->data['currency']
                ],
                'price_ex_vat' => [
                    'amount'   => sprintf('%0.2f', $price_ex_vat),
                    'currency' => $this->session->data['currency']
                ],
                'price_incl_vat' => [
                    'amount'   => sprintf('%0.2f', $price_incl_vat),
                    'currency' => $this->session->data['currency']
                ],
                'total_price_ex_vat' => [
                    'amount'   => sprintf('%0.2f', $total_price_ex_vat),
                    'currency' => $this->session->data['currency']
                ],
                'total_price_incl_vat' => [
                    'amount'   => sprintf('%0.2f', $total_price_incl_vat),
                    'currency' => $this->session->data['currency']
                ],
                'total_vat' => [
                    'amount'   => sprintf('%0.2f', $total_vat),
                    'currency' => $this->session->data['currency']
                ],
            ];
        }

        $total_price_incl_vat = 0;
        $total_price_ex_vat = 0;
        $total_vat = 0;

        foreach ($products as $product) {
            $total_price_incl_vat += $product['total_price_incl_vat']['amount'];
            $total_price_ex_vat += $product['total_price_ex_vat']['amount'];
            $total_vat += $product['total_vat']['amount'];
        }

        $payload = [
            'order_references' => [
                [
                    'key'   => 'partner_order_number',
                    'value' => (string) $this->session->data['order_id'],
                ],
            ],
            'cart_items' => $products ?? [],
            'total_price_incl_vat' => [
                'amount'   => sprintf('%0.2f', $total_price_incl_vat),
                'currency' => $this->session->data['currency'],
            ],
            'total_price_ex_vat' => [
                'amount'   => sprintf('%0.2f', $total_price_ex_vat),
                'currency' => $this->session->data['currency'],
            ],
            'total_vat' => [
                'amount'   => sprintf('%0.2f', $total_vat),
                'currency' => $this->session->data['currency'],
            ],
            'billing_details' => [
                'billing_reference' => null,
                'billing_tag'       => null,
            ],
            'customer_organization_number' => '',
            'purchaser_name'               => $name,
            'purchaser_email'              => $email,
            'purchaser_phone'              => $phone,
            'partner_reference'            => null,
            'recipient_name'               => $name,
            'recipient_phone'              => $phone,
            'request_domain'               => $this->config->get('config_ssl'),
            'confirmation_callback_url'    => $this->url->link('checkout/success', '', true),
            'ping_url'                     => $this->url->link('extension/wasa_kredit/payment/wasa_invoice/callback', '', true),
        ];

        if ($this->session->data['currency'] != 'SEK') {
            $error_message = $this->language->get('error_currency');
        } else {
            $response = $client->create_invoice_checkout($payload);

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

        $data['create_url'] = $this->url->link('extension/wasa_kredit/payment/wasa_invoice|confirm', '', true);
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

        if (!in_array($wasa_order_status, ['initialized', 'pending'])) {
            $this->success(null);
        }

        $this->load->model('extension/wasa_kredit/helper/gateway');

        $client = $this->model_extension_wasa_kredit_helper_gateway->getClient($this->extension_code);

        $response = $client->get_order($wasa_order_id);

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
