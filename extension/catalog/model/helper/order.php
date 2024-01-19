<?php

namespace Opencart\Catalog\Model\Extension\WasaKredit\Helper;

class Order extends \Opencart\System\Engine\Model
{
    /**
     * @param int    $order_id
     * @param string $wasa_order_id
     *
     * @return void
     */
    public function addWasaOrderId(int $order_id, string $wasa_order_id): void
    {
        try {
            $this->db->query("
                UPDATE `" . DB_PREFIX . "order`
                SET `wasa_order_id` = '" . $this->db->escape((string) $wasa_order_id) . "', `date_modified` = NOW()
                WHERE `order_id` = '" . (int)$order_id . "'
            ");
        } catch (Exception $e) {
            return;
        }

        return;
    }
}
