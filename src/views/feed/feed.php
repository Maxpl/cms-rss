<?php echo '<?xml version="1.0" encoding="'.\Yii::$app->charset.'"?>'.PHP_EOL; ?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/">
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
        <guid isPermaLink="true"><?=$item['url'];?></guid>
        <link><?=$item['url'];?></link>
        <?php if ($item['category']):?>
            <category><![CDATA['<?=htmlspecialchars($item['category'],ENT_QUOTES,\Yii::$app->charset);?>']]></category>
        <?php endif;?>
        <description><![CDATA[<?=$item['text'];?>]]></description>
        <?php if (is_file(ROOT_DIR.'/frontend/web'.$item['img_src'])):?>
        <enclosure url="<?=$item['img_src'];?>" type="<?= \yii\helpers\FileHelper::getMimeType(ROOT_DIR.'/frontend/web'.$item['img_src']);?>" />
        <?php endif;?>
        <pubDate><?= $item['dt_start'];?></pubDate>
    </item>
<?php endforeach;?>
    </channel>
</rss>