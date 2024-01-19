<?php

namespace Opencart\Catalog\Controller\Extension\WasaKredit\Event;

class WasaLeasing extends \Opencart\System\Engine\Controller
{
    private string $extension_code = 'payment_wasa_leasing';

    public function syncBefore(string &$route, array &$args): void
    {
        //
    }

    public function syncAfter(string &$route, array &$args, mixed &$output): void
    {
        $this->load->model('checkout/order');

        if (!$order_info = $this->model_checkout_order->getOrder($args[0])) {
            return;
        }

        if (!$wasa_order_id = $order_info['wasa_kredit_id'] ?? null) {
            return;
        }

        if (!$status_id = $order_info['order_status_id'] ?? null) {
            return;
        }

        $client = $this->getWasaClient();

        if (!$wasa_order_status = $this->getWasaOrderStatus($wasa_order_id)) {
            return;
        }

        $complete_statuses = (array) $this->config->get($this->extension_code . '_complete_order_status');
        $cancel_statuses = (array) $this->config->get($this->extension_code . '_cancel_order_status');

        if (in_array($status_id, $complete_statuses)) {
            if (in_array($wasa_order_status, ['ready_to_ship'])) {
                $this->shipWasaOrder($wasa_order_id);
            }
        } elseif (in_array($status_id, $cancel_statuses)) {
            if (in_array($wasa_order_status, ['initialized', 'pending', 'ready_to_ship'])) {
                $this->cancelWasaOrder($wasa_order_id);
            }
        }
    }

    protected function getWasaOrderStatus(string $orderId, $client)
    {
        try {
            $order = $client->get_order($orderId);

            if (empty($order['status']) || empty($order['status']['status'])) {
                return;
            }
        } catch (Exception $e) {
            return;
        }

        return $order['status']['status'];
    }

    protected function shipWasaOrder(string $orderId, $client)
    {
        try {
            $response = $client->ship_order($orderId);
        } catch (Exception $e) {
            return;
        }

        return $response;
    }

    protected function cancelWasaOrder(string  $orderId, $client)
    {
        try {
            $response = $client->cancel_order($orderId);
        } catch (Exception $e) {
            return;
        }

        return $response;
    }

    protected function getWasaClient()
    {
        $this->load->model('extension/wasa_kredit/helper/gateway');

        return $this->model_extension_wasa_kredit_helper_gateway->getClient($this->extension_code);
    }
}
