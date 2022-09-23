<?php

namespace Opencart\Admin\Controller\WasaKredit\Payment;

class WasaInvoice extends \Opencart\System\Engine\Controller
{
    private string $extension_path = 'extension/wasakredit/payment/wasa_invoice';
    private string $extension_code = 'payment_wasa_invoice';

    public function index(): void
    {
        $this->loadExtensionLanguage();

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($this->extension_path, 'user_token=' . $this->session->data['user_token'])
        ];

        $data['save'] = $this->url->link($this->extension_path . '|save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

        $this->load->model('localisation/order_status');
        $this->load->model('localisation/geo_zone');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $settings = [
            'test_mode',
            'order_status_id',
            'geo_zone_id',
            'status',
            'sort_order',
        ];

        foreach ($settings as $key => $value) {
            $data[$this->extension_code . '_' . $value] = $this->config->get($this->extension_code . '_' . $value);
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this->extension_path, $data));
    }

    public function save(): void
    {
        $this->loadExtensionLanguage();

        $json = [];

        if (!$this->user->hasPermission('modify', $this->extension_path)) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting($this->extension_code, $this->request->post);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function loadExtensionLanguage(): void
    {
        $this->load->language($this->extension_path);
    }
}
