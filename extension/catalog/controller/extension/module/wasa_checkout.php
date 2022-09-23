<?php
namespace Opencart\Catalog\Controller\Extension\WasaKredit\Module;

class WasaCheckout extends \Opencart\System\Engine\Controller
{
    private string $extension_path = 'extension/wasakredit/module/wasa_checkout';
    private string $extension_code = 'module_wasa_checkout';

    public function index(): string
    {
        $this->loadExtensionLanguage();

        return $this->load->view($this->$extension_path, $data);
    }

    private function loadExtensionLanguage(): void
    {
        $this->load->language($this->extension_path);
    }
}
