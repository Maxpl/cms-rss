<?php
return [
    'components' => [

        'i18n' => [
            'translations' => [
                'skeeks/rss' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@skeeks/cms/rss/messages',
                    'fileMap' => [
                        'skeeks/rss' => 'main.php',
                    ],
                ]
            ]
        ],
    ],
];