<?php
return [

    'bootstrap' => ['rss'],

    'components' => [
        'rss' => [
            'class' => 'skeeks\cms\rss\CmsRssComponent',
        ],

        'i18n' => [
            'translations' => [
                'skeeks/rss' => [
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@skeeks/cms/rss/messages',
                    'fileMap'  => [
                        'skeeks/rss' => 'main.php',
                    ],
                ]
            ]
        ],

        'urlManager' => [
            'rules' => [
                'rss/all-elkz.xml' => '/rss/feed/all-elkz',
                'rss/<code:[\w-]+>-ukrnet.xml' => '/rss/feed/ukrnet',
                'rss/<code:[\w-]+>-yandex.xml' => '/rss/feed/yandex',
                'rss/<code:[\w-]+>-full.xml' => '/rss/feed/full',
                'rss/<code:[\w-]+>.xml' => '/rss/feed/feed',
            ]
        ]
    ],

    'modules' => [
        'rss' => [
            'class' => 'skeeks\cms\rss\CmsRssModule',
        ]
    ]
];