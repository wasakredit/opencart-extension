<?php

require_once(DIR_SYSTEM . 'library/wasa/wasa/client-php-sdk/wasa.php');

class ControllerExtensionPaymentWasa extends Controller
{
    public function index()
    {
        $this->load->language('extension/payment/wasa');

        $this->load->model('extension/payment/wasa');
        $this->load->model('localisation/country');

        $this->client = Sdk\ClientFactory::CreateClient(
            $this->config->get('payment_wasa_client_id'),
            $this->config->get('payment_wasa_secret_key'),
            $this->config->get('payment_wasa_test_mode')
        );

        $payment_address = $this->session->data['payment_address'];

        $shipping_address = !empty($this->session->data['shipping_address'])
            ? $this->session->data['shipping_address']
            : $this->session->data['payment_address'];

        $payment_country = $this->model_localisation_country->getCountry($payment_address['country_id']);
        $shipping_country = $this->model_localisation_country->getCountry($shipping_address['country_id']);

        if ($this->customer->isLogged()) {
            $this->load->model('account/customer');
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
            $price = sprintf('%0.2f', $product['price']);
            $tax_amount = sprintf('%0.2f', $this->tax->getTax($product['price'], $product['tax_class_id']));
            $tax_rate = $this->tax->getTax(100, $product['tax_class_id']);

            $products[] = [
                'product_id' => $product['product_id'],
                'product_name' => $product['name'],
                'quantity' => $product['quantity'],
                'vat_percentage' => $tax_rate,
                'price_ex_vat' => [
                    'amount' => $price,
                    'currency' => $this->session->data['currency']
                ],
                'vat_amount' => [
                    'amount' => $tax_amount,
                    'currency' => $this->session->data['currency']
                ]
            ];
        }

        $shipping_cost = !empty($this->session->data['shipping_method']['cost'])
            ? $this->session->data['shipping_method']['cost']
            : 0;

        $shipping_cost = $this->currency->format($shipping_cost, $this->session->data['currency'], false, false);

        $domain = $this->config->get('config_ssl');
        $callback = $this->config->get('config_ssl');
        $ping = $this->config->get('config_ssl');

        $payload = [
            'payment_types' => 'leasing',
            'order_reference_id' => '',
            'order_references' => [
                [
                    'key' => 'partner_checkout_id',
                    'value' => $this->session->data['order_id'],
                ],
                [
                    'key' => 'partner_reserved_order_number',
                    'value' => $this->session->data['order_id'],
                ],
            ],
            'customer_organization_number' => '',
            'purchaser_name' => $name,
            'purchaser_email' => $email,
            'purchaser_phone' => $phone,
            'delivery_address' => [
                'company_name' => $shipping_address['company'],
                'street_address' => $shipping_address['address_1'],
                'postal_code' => $shipping_address['postcode'],
                'city' => $shipping_address['city'],
                'country' => $shipping_country['name']
            ],
            'billing_address' => [
                'company_name' => $payment_address['company'],
                'street_address' => $payment_address['address_1'],
                'postal_code' => $payment_address['postcode'],
                'city' => $payment_address['city'],
                'country' => $payment_country['name']
            ],
            'recipient_name' => $name,
            'recipient_phone' => $phone,
            'cart_items' => $products ?? [],
            'shipping_cost_ex_vat' => [
                'amount' => $shipping_cost,
                'currency' => $this->session->data['currency'],
            ],
            'request_domain' => $domain,
            'confirmation_callback_url' => $callback,
            'ping_url' => $ping
        ];

        if ($this->session->data['currency'] != 'SEK') {
            $error_message = $this->language->get('error_currency');
            $error = true;
        } else {
            $response = $this->client->create_checkout($payload);

            if (!empty($response->data['invalid_properties'][0]['error_message'])) {
                $error_message = $response->data['invalid_properties'][0]['error_message'];
                $error = true;
            } else {
                $error = false;
            }
        }

        if (isset($error) && $error == true) {
            $data['error'] = $error_message;
        } else {
            $data['response'] = $response->data;
        }

        $data['test_mode'] = $this->config->get('payment_wasa_test_mode');
        $data['order_id'] = $this->session->data['order_id'];
        $data['url_checkout'] = $this->url->link('checkout/checkout', '', true);
        $data['url_confirm'] = $this->url->link('extension/payment/wasa/send', '', true);

        return $this->load->view('extension/payment/wasa', $data);
    }

    public function send()
    {
        $this->load->model('checkout/order');

        if ($this->request->post['option'] !== 'checkout') {
            $json['processed'] = false;
        } else {
            if (!empty($this->request->post['id_wasakredit'])) {
                $message = sprintf('Wasa Kredit - Payment ID: %s', $this->request->post['id_wasakredit']);

                try {
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_wasa_order_status_id'), $message, false);
                } catch (Exception $e) {
                    //
                }

                $json['redirect'] = $this->url->link('checkout/checkout', '', true);
            }

            $json['processed'] = true;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json ?? []));
    }
}
