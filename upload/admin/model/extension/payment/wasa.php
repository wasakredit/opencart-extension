<?php

class ModelExtensionPaymentWasa extends Model
{
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wasakredit` (
            `cart_id` int(11) unsigned NOT NULL,
            `id_wasakredit` varchar(36) NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wasakredit`");
    }
}
