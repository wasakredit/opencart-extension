<?php

class ControllerPaymentWasa extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->model('setting/setting');
        $this->load->model('payment/wasa');
        $this->load->model('localisation/order_status');

        $this->load->language('payment/wasa');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('wasa', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/wasa', 'token=' . $this->session->data['token'], true)
        );

        $data['heading_title']      = $this->language->get('heading_title');

        $data['tab_settings']       = $this->language->get('tab_settings');

        $data['text_edit']          = $this->language->get('text_edit');
        $data['text_enabled']       = $this->language->get('text_enabled');
        $data['text_disabled']      = $this->language->get('text_disabled');

        $data['entry_client_id']    = $this->language->get('entry_client_id');
        $data['entry_secret_key']   = $this->language->get('entry_secret_key');
        $data['entry_test_mode']    = $this->language->get('entry_test_mode');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_status']       = $this->language->get('entry_status');

        $data['button_save']        = $this->language->get('button_save');
        $data['button_cancel']      = $this->language->get('button_cancel');
        $data['currencies']         = ['SEK'];

        $data['action'] = $this->url->link('payment/wasa', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true);

        if (isset($this->request->post['wasa_test_mode'])) {
            $data['wasa_test_mode'] = $this->request->post['wasa_test_mode'];
        } elseif ($this->config->has('wasa_test_mode')) {
            $data['wasa_test_mode'] = $this->config->get('wasa_test_mode');
        } else {
            $data['wasa_test_mode'] = 1;
        }

        if (isset($this->request->post['wasa_currency'])) {
            $data['wasa_currency'] = $this->request->post['wasa_currency'];
        } elseif ($this->config->has('wasa_currency')) {
            $data['wasa_currency'] = $this->config->get('wasa_currency');
        } else {
            $data['wasa_currency'] = 'SEK';
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
        } else {
            $data['wasa_status'] = 1;
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

        $this->response->setOutput($this->load->view('payment/wasa.tpl', $data));
    }

    public function install()
    {
        if ($this->user->hasPermission('modify', 'extension/payment')) {
            $this->load->model('payment/wasa');

            $this->model_payment_wasa->install();
        }
    }

    public function uninstall()
    {
        if ($this->user->hasPermission('modify', 'extension/payment')) {
            $this->load->model('payment/wasa');

            $this->model_payment_wasa->uninstall();
        }
    }

    public function order()
    {
        if ($this->config->get('wasa_status')) {
            $this->load->model('payment/wasa');
            $this->load->language('payment/wasa');

            $data['order_id'] = $this->request->get['order_id'];
        }
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/wasa')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }
}
