<?php

class ControllerExtensionPaymentWasa extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('extension/payment/wasa');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_wasa', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/payment/wasa', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/wasa', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/wasa', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        if (isset($this->request->post['payment_wasa_test_mode'])) {
            $data['payment_wasa_test_mode'] = $this->request->post['payment_wasa_test_mode'];
        } elseif ($this->config->has('payment_wasa_test_mode')) {
            $data['payment_wasa_test_mode'] = $this->config->get('payment_wasa_test_mode');
        } else {
            $data['payment_wasa_test_mode'] = 1;
        }

        if (isset($this->request->post['payment_wasa_client_id'])) {
            $data['payment_wasa_client_id'] = $this->request->post['payment_wasa_client_id'];
        } elseif ($this->config->has('payment_wasa_client_id')) {
            $data['payment_wasa_client_id'] = $this->config->get('payment_wasa_client_id');
        } else {
            $data['payment_wasa_client_id'] = '';
        }

        if (isset($this->request->post['payment_wasa_secret_key'])) {
            $data['payment_wasa_secret_key'] = $this->request->post['payment_wasa_secret_key'];
        } elseif ($this->config->has('payment_wasa_secret_key')) {
            $data['payment_wasa_secret_key'] = $this->config->get('payment_wasa_secret_key');
        } else {
            $data['payment_wasa_secret_key'] = '';
        }

        if (isset($this->request->post['payment_wasa_status'])) {
            $data['payment_wasa_status'] = $this->request->post['payment_wasa_status'];
        } elseif ($this->config->has('payment_wasa_status')) {
            $data['payment_wasa_status'] = $this->config->get('payment_wasa_status');
        } else {
            $data['payment_wasa_status'] = 1;
        }

        if (isset($this->request->post['payment_wasa_show_widget'])) {
            $data['payment_wasa_show_widget'] = $this->request->post['payment_wasa_show_widget'];
        } elseif ($this->config->has('payment_wasa_show_widget')) {
            $data['payment_wasa_show_widget'] = $this->config->get('payment_wasa_show_widget');
        } else {
            $data['payment_wasa_show_widget'] = 0;
        }

        $order_statuses = [
            'payment_wasa_created_order_status_id',
            'payment_wasa_initialized_order_status_id',
            'payment_wasa_canceled_order_status_id',
            'payment_wasa_pending_order_status_id',
            'payment_wasa_ready_order_status_id',
            'payment_wasa_shipped_order_status_id',
        ];

        foreach ($order_statuses as $order_status) {
            if (isset($this->request->post[$order_status])) {
                $data[$order_status] = $this->request->post[$order_status];
            } else {
                $data[$order_status] = $this->config->get($order_status);
            }
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

        $data['user_token'] = $this->session->data['user_token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/wasa', $data));
    }

    public function install()
    {
        if ($this->user->hasPermission('modify', 'extension/extension')) {
            $this->load->model('extension/payment/wasa');

            $this->model_extension_payment_wasa->install();
        }
    }

    public function uninstall()
    {
        if ($this->user->hasPermission('modify', 'extension/extension')) {
            $this->load->model('extension/payment/wasa');

            $this->model_extension_payment_wasa->uninstall();
        }
    }

    public function order()
    {
        if ($this->config->get('wasa_status')) {
            $this->load->model('extension/payment/wasa');
            $this->load->language('extension/payment/wasa');

            $data['order_id'] = $this->request->get['order_id'];
        }
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/wasa')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }
}
