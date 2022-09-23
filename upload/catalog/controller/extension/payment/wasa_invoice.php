<?php

require_once(DIR_SYSTEM . 'library/wasa/wasa/client-php-sdk/Wasa.php');

class ControllerExtensionPaymentWasaInvoice extends Controller
{
    public function index()
    {
        $this->load->language('extension/payment/wasa_invoice');

        $this->load->model('extension/payment/wasa_invoice');
        $this->load->model('localisation/country');
        $this->load->model('account/customer');

        $this->client = Sdk\ClientFactory::CreateClient(
            $this->config->get('payment_wasa_invoice_client_id'),
            $this->config->get('payment_wasa_invoice_secret_key'),
            $this->config->get('payment_wasa_invoice_test_mode')
        );

        $payment_address = $this->session->data['payment_address'];

        $shipping_address = !empty($this->session->data['shipping_address'])
            ? $this->session->data['shipping_address']
            : $this->session->data['payment_address'];

        $payment_country = $this->model_localisation_country->getCountry($payment_address['country_id']);
        $shipping_country = $this->model_localisation_country->getCountry($shipping_address['country_id']);

        if ($this->customer->isLogged()) {
            $customer = $this->model_account_customer->getCustomer($this->customer->getId());

            $name = sprintf('%s %s', $customer['firstname'], $customer['lastname']);
            $email = $customer['email'];
            $phone = $customer['telephone'];
        } elseif (isset($this->session->data['guest'])) {
            $name = sprintf('%s %s', $this->session->data['guest']['firstname'], $this->session->data['guest']['lastname']);
            $email = $this->session->data['guest']['email'];
            $phone = $this->session->data['guest']['telephone'];
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

        if (!empty($this->session->data['shipping_method']['cost'])) {
            $tax_rate = $this->tax->getTax(100, $this->session->data['shipping_method']['tax_class_id']);
            $price_ex_vat = $this->session->data['shipping_method']['cost'];
            $price_incl_vat = $this->tax->calculate($price_ex_vat, $this->session->data['shipping_method']['tax_class_id'], true);
            $vat_amount = $this->tax->getTax($price_ex_vat, $this->session->data['shipping_method']['tax_class_id']);
            $total_price_ex_vat = $price_ex_vat * 1;
            $total_price_incl_vat = $price_incl_vat * 1;
            $total_vat = $vat_amount * 1;

            $products[] = [
                'product_id'     => $this->session->data['shipping_method']['code'],
                'product_name'   => $this->session->data['shipping_method']['title'],
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
                    'value' => $this->session->data['order_id'],
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
            'ping_url'                     => $this->url->link('extension/payment/wasa_invoice/callback', '', true),
        ];

        if ($this->session->data['currency'] != 'SEK') {
            $error_message = $this->language->get('error_currency');
        } else {
            $response = $this->client->create_invoice_checkout($payload);

            if (!empty($response->data['invalid_properties'][0]['error_message'])) {
                $error_message = $response->data['invalid_properties'][0]['error_message'];
            }
        }

        if (!empty($error_message)) {
            $data['error'] = $error_message;
        } else {
            $data['checkout'] = $response->data;
        }

        $data['test_mode'] = $this->config->get('payment_wasa_invoice_test_mode');
        $data['order_id'] = $this->session->data['order_id'];

        $data['create_url'] = $this->url->link('extension/payment/wasa_invoice/create', '', true);
        $data['success_url'] = $this->url->link('checkout/success', '', true);
        $data['cancel_url'] = $this->url->link('checkout/checkout', '', true);

        return $this->load->view('extension/payment/wasa_invoice', $data);
    }

    public function create()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->response->addHeader('HTTP/1.0 405 Method Not Allowed');
            exit('Method not allowed');
        } elseif (empty($this->request->post['data'])) {
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
            exit('Payload is empty');
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
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
            exit('References is empty');
        }

        $this->load->model('checkout/order');

        $order = $this->model_checkout_order->getOrder($order_id);

        if ($order['order_status_id'] > 0) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(null);
        }

        $message = sprintf('Signering slutförd av kunden (%s)', $wasa_order_id);

        try {
            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_wasa_invoice_created_order_status_id'), $message, true);
        } catch (Exception $e) {
            if (method_exists($e, 'getMessage')) {
                $this->log->write($e->getMessage());
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(null);
    }

    public function callback()
    {
        $request = (strpos($this->request->server['CONTENT_TYPE'], 'application/json') !== false)
            ? json_decode(file_get_contents('php://input'), true)
            : $this->request->post;

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->response->addHeader('HTTP/1.0 405 Method Not Allowed');
            exit('Method not allowed');
        } elseif (empty($request['order_id'])) {
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
            exit('Order id is empty');
        } elseif (empty($request['order_status'])) {
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
            exit('Order status is empty');
        } elseif ($request['order_status'] === 'initialized') {
            $this->response->addHeader('Content-Type: application/json');
            exit(null);
        } elseif ($request['order_status'] === 'pending') {
            $this->response->addHeader('Content-Type: application/json');
            exit(null);
        }

        $wasa_order_id = $request['order_id'];
        $wasa_order_status = $request['order_status'];

        $this->client = Sdk\ClientFactory::CreateClient(
            $this->config->get('payment_wasa_invoice_client_id'),
            $this->config->get('payment_wasa_invoice_secret_key'),
            $this->config->get('payment_wasa_invoice_test_mode')
        );

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
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
            exit('References is empty');
        }

        $this->load->model('checkout/order');

        $order_status = $this->convertOrderStatus($wasa_order_status);

        $message = sprintf('Avtalet godkänt as Wasa (%s)', $wasa_order_id);

        try {
            $this->model_checkout_order->addOrderHistory($order_id, $order_status, $message, false);
        } catch (Exception $e) {
            if (method_exists($e, 'getMessage')) {
                $this->log->write($e->getMessage());
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(null);
    }

    private function convertOrderStatus(string $order_status)
    {
        switch ($order_status) {
            case 'initialized':
                return $this->config->get('payment_wasa_invoice_initialized_order_status_id');

            case 'pending':
                return $this->config->get('payment_wasa_invoice_pending_order_status_id');

            case 'ready_to_ship':
                return $this->config->get('payment_wasa_invoice_ready_order_status_id');

            case 'shipped':
                return $this->config->get('payment_wasa_invoice_shipped_order_status_id');

            case 'canceled':
                return $this->config->get('payment_wasa_invoice_canceled_order_status_id');

            default:
                return null;
        }
    }
}
