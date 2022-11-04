<?php return array(
    'root' => array(
        'name' => 'wasa/opencart-extension',
        'pretty_version' => '2.0.0',
        'version' => '2.0.0.0',
        'reference' => NULL,
        'type' => 'library',
        'install_path' => __DIR__ . '/../../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'wasa/client-php-sdk' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'a8eca895c63e531825fa291e8c4898dbe782635b',
            'type' => 'library',
            'install_path' => __DIR__ . '/../wasa/client-php-sdk',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'wasa/opencart-extension' => array(
            'pretty_version' => '2.0.0',
            'version' => '2.0.0.0',
            'reference' => NULL,
            'type' => 'library',
            'install_path' => __DIR__ . '/../../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
