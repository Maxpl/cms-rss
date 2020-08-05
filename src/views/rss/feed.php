<?php echo '<?xml version="1.0" encoding="'.\Yii::$app->charset.'"?>'.PHP_EOL; ?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
    <title><?=\Yii::$app->cms->cmsSite->name;?></title>
    <link><?= \yii\helpers\Url::home(); ?></link>
    <language><?=\Yii::$app->language;?></language>
    <description><?=\Yii::$app->cms->cmsSite->description;?></description>
    <image>
        <url><?= \frontend\helpers\Url::to('@web/img/apple-touch-icon.png');?></url>
    </image>
    <generator>Skeeks CMS</generator>
    <?php foreach ($data as $item):?>
        <item>
            <title><?=htmlspecialchars($item['name'],ENT_QUOTES,\Yii::$app->charset);?></title>
            <link><?=$item['url'];?></link>
            <category><?php if ($item['category']) 
                echo htmlspecialchars($item['category'],ENT_QUOTES,\Yii::$app->charset); ?></category>
            <description><![CDATA[
                <?=$item['text'];?>]]>
            </description>
            <enclosure url="<?=$item['img_src'];?>" type="<?= \yii\helpers\FileHelper::getMimeType('@web'.$item['img_src']);?>" />
            <pubDate><?= $item['dt_start'];?></pubDate>
        </item>
    <?php endforeach;?>
    </channel>
</rss>