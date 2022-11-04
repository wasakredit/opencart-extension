<?php
namespace Opencart\Admin\Model\Extension\WasaKredit\Helper;

class System extends \Opencart\System\Engine\Model
{
    public function getLocalVersionFile()
    {
        return json_decode(file_get_contents(DIR_EXTENSION . 'wasa_kredit/version.json'), true);
    }

    public function getRemoteVersionFile(string $url)
    {
        set_error_handler(null);

        $content = @file_get_contents($url);

        restore_error_handler();

        return json_decode($content, true);
    }

    public function getCurrentVersion()
    {
        $data = $this->getLocalVersionFile();

        return !empty($data['version'])
            ? $data['version']
            : null;
    }

    public function getLatestVersion()
    {
        $data = $this->getLocalVersionFile();

        if (empty($data['url'])) {
            return null;
        }

        $data = $this->getRemoteVersionFile($data['url']);

        return !empty($data['version'])
            ? $data['version']
            : null;
    }

    public function getOpenCartVersion()
    {
        return VERSION;
    }
}
