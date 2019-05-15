<?php

use Sdk\AccessToken;
use Sdk\Api;
use Sdk\Client;
use Sdk\Response;

require_once(DIR_SYSTEM.'library/Wasa.php');

class ControllerExtensionPaymentWasa extends Controller {
	public function index() {
		$this->load->language('extension/payment/wasa');
		$this->load->model('extension/payment/wasa');
		$this->load->model('account/customer');
		$this->load->model('account/address');
		$this->load->model('localisation/country');

		$data['publishable_key'] = $this->config->get('wasa_client_id');
		$data['wasa_secret_key'] = $this->config->get('wasa_secret_key');
		$data['wasa_environment'] = $this->config->get('wasa_environment');
		$wasa_environment  = false;
		if ($data['wasa_environment'] == 'true') {
			$wasa_environment  = true;
		}

        $this->_client = new Client(
            $data['publishable_key'],
            $data['wasa_secret_key'],
            $wasa_environment
        ); 

        $customer = $this->model_account_customer->getCustomer($this->customer->getId());

        $cart_products = $this->cart->getProducts();
        $cart_items = array();

        $b_address = $this->session->data['payment_address'];

        if (empty($this->session->data['shipping_address'])) {
            $d_address = $this->session->data['payment_address'];
        } else {
            $d_address = $this->session->data['shipping_address'];
        }

        $purchaser_name = $b_address['firstname'].' '.$b_address['lastname'];
        $recipient_name = $d_address['firstname'].' '.$d_address['lastname'];

		    $b_country = $this->model_localisation_country->getCountry($b_address['country_id']);
		    $d_country = $this->model_localisation_country->getCountry($d_address['country_id']);

        $billing_address = array(
            'company_name' => $b_address['company'],
            'street_address' => $b_address['address_1'],
            'postal_code' => $b_address['postcode'],
            'city' => $b_address['city'],
            'country' => $b_country['name']
        );

        $delivery_address = array(
            'company_name' => $d_address['company'],
            'street_address' => $d_address['address_1'],
            'postal_code' => $d_address['postcode'],
            'city' => $d_address['city'],
            'country' => $d_country['name']
        );

        foreach ($cart_products as $product) {
        	$tax = $this->currency->format(
              $this->tax->getTax($product['price'], $product['tax_class_id']),
              $this->session->data['currency'],
              false,
              false
            );

        	$price = $this->currency->format(
              $product['price'],
              $this->session->data['currency'],
              false,
              false
            );

        	$tax = sprintf('%0.2f', $tax);
        	$price = sprintf('%0.2f', $price);
        	$tax_rate = $this->tax->getTax(100, $product['tax_class_id']);

            $item = array(
				'product_id' => $product['product_id'],
				'product_name' => $product['name'],
				'price_ex_vat' => array(
				    'amount' => $price,
				    'currency' => 'SEK'
				),
				'quantity' => $product['quantity'],
				'vat_percentage' => $tax_rate,
				'vat_amount' => array(
				'amount' => $tax,
				'currency' => 'SEK'
				)
            );

            array_push($cart_items, $item);
        }

        $shipping_method_cost = 0;
        if (!empty($this->session->data['shipping_method']['cost'])) {
            $shipping_method_cost = $this->session->data['shipping_method']['cost'];
        }

        if ($this->customer->isLogged()) {
            $this->load->model('account/customer');
            $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

            $firstname = $customer_info['firstname'];
            $lastname  = $customer_info['lastname'];
            $email     = $customer_info['email'];
            $telephone = $customer_info['telephone'];
        } elseif (isset($this->session->data['guest'])) {
            $firstname = $this->session->data['guest']['firstname'];
            $lastname  = $this->session->data['guest']['lastname'];
            $email     = $this->session->data['guest']['email'];
            $telephone = $this->session->data['guest']['telephone'];
        }

        $payload = array(
          'payment_types' => 'leasing',
          'order_reference_id' => '',
          'order_references' => array(
                    array(
                        'key' => 'partner_checkout_id',
                        'value' => $this->session->data['order_id'],
                    ),
                    array(
                        'key' => 'partner_reserved_order_number',
                        'value' => $this->session->data['order_id'],
                    ),
          ),
          'purchaser_name' => $firstname.' '.$lastname,
          'purchaser_email' => $email,
          'customer_organization_number' => '',
          'purchaser_phone' => $telephone,
          'delivery_address' => $delivery_address,
          'billing_address' => $billing_address,
          'recipient_name' => $firstname.' '.$lastname,
          'recipient_phone' => $telephone,
          'cart_items' => $cart_items,
          'shipping_cost_ex_vat' => array(
              'amount' => $this->currency->format(
              $shipping_method_cost,
              $this->session->data['currency'],
              false,
              false
            ),
            'currency' => 'SEK',
          ),
          'request_domain' => $this->config->get('config_url'),
          'confirmation_callback_url' => $this->config->get('config_url'),
          'ping_url' => $this->config->get('config_url')
        );

		$currency = $this->session->data['currency'];
		if ($currency != 'SEK') {
            $error_message = 'Wasa leasing supports only Swedish Krona. Please change shop currency to Swedish Krona.';
            $error = true;
		} else {
	        $response = $this->_client->create_checkout($payload);
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

		$data['order_reference_id'] = $this->session->data['order_id'];
		$data['redirect'] = $this->url->link('checkout/success');
		$data['ajax'] = 'index.php?route=extension/payment/wasa/send';

		return $this->load->view('extension/payment/wasa', $data);
	}

	public function send() {
		$json = array();

		$this->load->model('checkout/order');
        $option = $this->request->post['option'];

        if ($option == 'checkout') {
            $id_wasakredit = $this->request->post['id_wasakredit'];
            if (!empty($id_wasakredit)) {
                $message = 'Wasa payment ID: '.$id_wasakredit;
                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('wasa_order_status_id'), $message, false);
                $json['processed'] = true;  
            } else {
                $json['processed'] = false;
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));      
        }
	}
}
