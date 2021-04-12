<?php echo '<?xml version="1.0" encoding="'.\Yii::$app->charset.'"?>'.PHP_EOL; ?>
<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" version="2.0">
    <channel>
    <title><?=(!empty($tree) ? ($tree->meta_title ? $tree->meta_title : $tree->name) : \Yii::$app->cms->cmsSite->name);?></title>
    <link><?= \frontend\helpers\Url::to('/'.$code, 'https'); ?></link>
    <language><?=\Yii::$app->language;?></language>
    <description><?=(!empty($tree) ? 
            ($tree->meta_description ? $tree->meta_description : \Yii::$app->cms->cmsSite->description) 
            : \Yii::$app->cms->cmsSite->description);?></description>
    <image>
    <url><?= \frontend\helpers\Url::to(\frontend\assets\AppAsset::getAssetUrl('img/apple-touch-icon.png'), 'https');?></url>
    </image>
    <generator>Skeeks CMS</generator>
<?php foreach ($data as $item):?>
    <item>
        <title><?=htmlspecialchars($item['name'],ENT_QUOTES,\Yii::$app->charset);?></title>
        <link><?=$item['url'];?></link>
        <?php if ($item['category']):?>
            <category><![CDATA['<?=htmlspecialchars($item['category'],ENT_QUOTES,\Yii::$app->charset);?>']]></category>
        <?php endif;?>
        <description><![CDATA[<?=$item['text'];?>]]></description>
        <?php if (is_file(ROOT_DIR.'/frontend/web'.$item['img_src'])):?>
        <enclosure url="<?=$item['img_src'];?>" type="<?= \yii\helpers\FileHelper::getMimeType(ROOT_DIR.'/frontend/web'.$item['img_src']);?>"/>
        <?php endif;?>
        <pubDate><?= $item['dt_start'];?></pubDate>
        <yandex:full-text><?= $item['full-text'];?></yandex:full-text>
    </item>
<?php endforeach;?>
    </channel>
</rss>