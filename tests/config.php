<?php

return [
    'id' => 'Mailtank client',
    'basePath' => __DIR__ . '/../../../..',

    // application components
    'components' => [
        'mailtankClient' => [
            'class' => 'mailtank\MailtankClient',
            'host' => $params['host'],
            'token' => $params['token'],
        ],
    ],
];