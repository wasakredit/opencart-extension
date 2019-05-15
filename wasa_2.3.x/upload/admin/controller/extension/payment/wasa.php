<?php
class ControllerExtensionPaymentWasa extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('setting/setting');

		$this->load->model('extension/payment/wasa');
		$this->load->language('extension/payment/wasa');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('wasa', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/wasa', 'token=' . $this->session->data['token'], true)
		);

		$data['heading_title']         = $this->language->get('heading_title');

		$data['tab_settings']          = $this->language->get('tab_settings');

		$data['text_payment']          = $this->language->get('text_payment');
		$data['text_edit']             = $this->language->get('text_edit');
		$data['text_live']             = $this->language->get('text_live');
		$data['text_test']             = $this->language->get('text_test');
		$data['text_enabled']          = $this->language->get('text_enabled');
		$data['text_disabled']         = $this->language->get('text_disabled');
		$data['text_other']            = $this->language->get('text_other');

		$data['entry_email_address']   = $this->language->get('entry_email_address');
		$data['entry_password']        = $this->language->get('entry_password');
		$data['entry_currency']        = $this->language->get('entry_currency');
		$data['entry_warehouse']       = $this->language->get('entry_warehouse');
		$data['entry_country']         = $this->language->get('entry_country');
		$data['entry_merchant_number'] = $this->language->get('entry_merchant_number');
		$data['entry_secret_key']      = $this->language->get('entry_secret_key');
		$data['entry_environment']     = $this->language->get('entry_environment');
		$data['entry_order_status']    = $this->language->get('entry_order_status');
		$data['entry_status']          = $this->language->get('entry_status');
		$data['entry_logging']         = $this->language->get('entry_logging');
		$data['entry_sort_order']      = $this->language->get('entry_sort_order');
		$data['entry_api_key']         = $this->language->get('entry_api_key');
		$data['entry_card']            = $this->language->get('entry_card');

		$data['help_email_address']    = $this->language->get('help_email_address');
		$data['help_password']         = $this->language->get('help_password');
		$data['help_currency']         = $this->language->get('help_currency');
		$data['help_test']             = $this->language->get('help_test');
		$data['help_secret_key']       = $this->language->get('help_secret_key');
		$data['help_order_status']     = $this->language->get('help_order_status');
		$data['help_logging']          = $this->language->get('help_logging');

		$data['button_save']           = $this->language->get('button_save');
		$data['button_cancel']         = $this->language->get('button_cancel');
		$data['currencies']			   = ['sek'];

		$data['action'] = $this->url->link('extension/payment/wasa', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

		if (isset($this->request->post['wasa_environment'])) {
			$data['wasa_environment'] = $this->request->post['wasa_environment'];
		} elseif ($this->config->has('wasa_environment')) {
			$data['wasa_environment'] = $this->config->get('wasa_environment');
		} else {
			$data['wasa_environment'] = 'test';
		}

		if (isset($this->request->post['wasa_currency'])) {
			$data['wasa_currency'] = $this->request->post['wasa_currency'];
		} elseif ($this->config->has('wasa_currency')) {
			$data['wasa_currency'] = $this->config->get('wasa_currency');
		} else {
			$data['wasa_currency'] = 'usd';
		}


		if (isset($this->request->post['wasa_client_id'])) {
			$data['wasa_client_id'] = $this->request->post['wasa_client_id'];
		} elseif ($this->config->has('wasa_client_id')) {
			$data['wasa_client_id'] = $this->config->get('wasa_client_id');
		} else {
			$data['wasa_client_id'] = '';
		}

		if (isset($this->request->post['wasa_secret_key'])) {
			$data['wasa_secret_key'] = $this->request->post['wasa_secret_key'];
		} elseif ($this->config->has('wasa_secret_key')) {
			$data['wasa_secret_key'] = $this->config->get('wasa_secret_key');
		} else {
			$data['wasa_secret_key'] = '';
		}

		if (isset($this->request->post['wasa_order_status_id'])) {
			$data['wasa_order_status_id'] = $this->request->post['wasa_order_status_id'];
		} else {
			$data['wasa_order_status_id'] = $this->config->get('wasa_order_status_id');
		}

		if (isset($this->request->post['wasa_status'])) {
			$data['wasa_status'] = $this->request->post['wasa_status'];
		} elseif ($this->config->has('wasa_status')) {
			$data['wasa_status'] = $this->config->get('wasa_status');
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}


		$data['token'] = $this->session->data['token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/wasa', $data));
	}

	public function install() {
		if ($this->user->hasPermission('modify', 'extension/extension')) {
			$this->load->model('extension/payment/wasa');

			$this->model_extension_payment_wasa->install();
		}
	}

	public function uninstall() {
		if ($this->user->hasPermission('modify', 'extension/extension')) {
			$this->load->model('extension/payment/wasa');

			$this->model_extension_payment_wasa->uninstall();
		}
	}

	public function order() {

		if ($this->config->get('wasa_status')) {
			$this->load->model('extension/payment/wasa');
			$this->load->language('extension/payment/wasa');

			$data['order_id'] = $this->request->get['order_id'];
		}
	}


	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/wasa')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}
