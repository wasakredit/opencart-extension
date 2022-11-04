<?php
namespace Opencart\Catalog\Model\Extension\WasaKredit\Helper;

class Gateway extends \Opencart\System\Engine\Model
{
    public function getClient(string $extension_code): \Sdk\Client
    {
        require_once(DIR_EXTENSION . 'wasa_kredit/vendor/wasa/client-php-sdk/Wasa.php');

        $client_id = $this->config->get($extension_code . '_test_mode')
            ? $this->config->get($extension_code . '_test_mode_client_id')
            : $this->config->get($extension_code . '_client_id_');

        $secret_key = $this->config->get($extension_code . '_test_mode')
            ? $this->config->get($extension_code . '_test_mode_secret_key')
            : $this->config->get($extension_code . '_secret_key');

        $test_mode = $this->config->get($extension_code . '_test_mode')
            ? true
            : false;

        return \Sdk\ClientFactory::CreateClient($client_id, $secret_key, $test_mode);
    }
}
