<?php

namespace Opencart\Admin\Model\Extension\WasaKredit\Payment;

class WasaLeasing extends \Opencart\System\Engine\Model
{
    public function install(): void
    {
        $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order` LIKE 'wasa_order_id'");

        if ($query->num_rows === 0) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD COLUMN `wasa_order_id` VAR_CHAR(128) AFTER `order_id`");
        }

        $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order` LIKE 'wasa_status'");

        if ($query->num_rows === 0) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD COLUMN `wasa_status` VAR_CHAR(64) AFTER `order_id`");
        }
    }

    public function uninstall(): void
    {
        //
    }
}
