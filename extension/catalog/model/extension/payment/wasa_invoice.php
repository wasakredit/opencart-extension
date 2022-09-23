<?php
namespace Opencart\Catalog\Model\Extension\WasaKredit\Payment;

class WasaInvoice extends \Opencart\System\Engine\Model
{
    private string $extension_path = 'extension/wasakredit/payment/wasa_invoice';
    private string $extension_code = 'payment_wasa_invoice';

    public function getMethod(array $address): array
    {
        $this->load->language($this->extension_path);

        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone`
            WHERE `geo_zone_id` = '" . (int)$this->config->get($this->extension_code . '_geo_zone_id') . "'
                AND `country_id` = '" . (int)$address['country_id'] . "'
                AND (`zone_id` = '" . (int)$address['zone_id'] . "'
                OR `zone_id` = '0')
        ");

        if ($this->cart->hasSubscription()) {
            $status = false;
        } elseif (!$this->cart->hasShipping()) {
            $status = false;
        } elseif (!$this->config->get($this->extension_code . '_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = [];

        if ($status) {
            $method_data = [
                'code'       => str_replace('payment_', '', $this->extension_code),
                'title'      => $this->language->get('heading_title'),
                'sort_order' => $this->config->get($this->extension_code . '_sort_order')
            ];
        }

        return $method_data;
    }
}
