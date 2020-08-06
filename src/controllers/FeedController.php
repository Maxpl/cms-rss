<?php
/**
 * @author Maks Sloboda <msloboda@ukr.net>
 * @link http://skeeks.com/
 * @copyright 2020 SkeekS (СкикС)
 * @date 04.08.2020
 */

namespace skeeks\cms\rss\controllers;

use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\Tree;
use skeeks\cms\seo\vendor\UrlHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * Class FeedController
 * @package skeeks\cms\rss\controllers
 */
class FeedController extends Controller
{
    /**
     * @param $code
     */
    public function actionFeed($code)
    {
        \Yii::$app->response->format = Response::FORMAT_XML;
        $this->layout = false;
        
        $filename = \Yii::getAlias('@frontend/web/assets/').\Yii::$app->request->pathInfo;
        
        self::_checkCache($filename);
        
        ini_set("memory_limit", "512M");

        $result = [];

        $this->_addElements($result, $code);
        
        $content = $this->render($this->action->id, [
            'data' => $result
        ]);
        
        self::_sendCache($filename, $content);
        
        \Yii::$app->response->content = $content;

        return;

    }

    /**
     * @param $code
     */
    public function actionFull($code)
    {
        \Yii::$app->response->format = Response::FORMAT_XML;
        $this->layout = false;
        
        $filename = \Yii::getAlias('@frontend/web/assets/').\Yii::$app->request->pathInfo;
        
        self::_checkCache($filename);
        
        ini_set("memory_limit", "512M");

        $result = [];

        $this->_addElements($result, $code, true);
        
        $content = $this->render('feed', [
            'data' => $result
        ]);
        
        self::_sendCache($filename, $content);
        
        \Yii::$app->response->content = $content;

        return;

    }
    
    /**
     * @param array $data
     * @param string $contentCode
     * @param bool $fullText default false
     * @return $this
     */
    protected function _addElements(&$data = [], $contentCode, $fullText = false)
    {
        if (!$cmsContent = CmsContent::findOne(['code' => $contentCode]))
            return;

        if (!in_array($cmsContent->id, \Yii::$app->rss->contentIds))
            return;
        
        $query = CmsContentElement::find()
            ->joinWith('cmsTree')
            ->andWhere([Tree::tableName() . '.cms_site_id' => \Yii::$app->skeeks->site->id]);

        $query->andWhere(['content_id' => $cmsContent->id]);

        $query->andWhere(
            ["<=", CmsContentElement::tableName() . '.published_at', \Yii::$app->formatter->asTimestamp(time())]
        );
        
        $query->andWhere(
            [
                'or',
                [">", 'published_to', \Yii::$app->formatter->asTimestamp(time())],
                ['published_to' => null],
            ]
        );

        $query->andWhere([CmsContentElement::tableName() . '.active' => 'Y']);

        $query->limit(\Yii::$app->rss->rss_content_element_page_size);

        $elements = $query->orderBy(['updated_at' => SORT_DESC, 'priority' => SORT_ASC])->all();

        //Добавление элементов
        if ($elements) {
            /**
             * @var CmsContentElement $model
             */
            foreach ($elements as $model) {
                $data[] =
                    [
                        "name" => $model->name,
                        "url"     => $model->absoluteUrl,
                        "dt_start" => date(DATE_RSS, $model->published_at),
                        "img_src" => \frontend\helpers\Image::getModelImageUrl($model),
                        "text" => $fullText ? $model->description_full : self::html_mb_substr($model->description_full,0,150),
                        "category" => $model->tree_id ? $model->cmsTree->name : '',
                    ];
            }
        }

        return $this;
    }
    
    /**
     * Если файл существует и кеш еще не протух то работа приложения
     * завершается отдачей файла
     * @param string $filename
     * @return type file
     */
    private static function _checkCache($filename) {
        
        $expire = \Yii::$app->cache->get('rss:'.\Yii::$app->request->pathInfo);
        
        if ($expire && $expire > date('Y-m-d H:i:s') && is_file($filename)) {
            
            \Yii::$app->response->content = file_get_contents($filename);
            
            \Yii::$app->end();
        }
    }
    
    private static function _sendCache($filename, $content, $delay='+1 hours') {
        if( is_dir(\Yii::getAlias('@frontend/web/assets/rss')) || @mkdir(\Yii::getAlias('@frontend/web/assets/rss'), 0777, true) ) {
            file_put_contents($filename, $content);
            @chmod($filename,0666);
            \Yii::$app->cache->set('rss:'.\Yii::$app->request->pathInfo, date('Y-m-d H:i:s',strtotime($delay)));
        } else
            \Yii::warning("Не могу создать директорию '".dirname($filename));
    }
    
    private static function html_mb_substr($str, $from, $to) {
        $str = strip_tags($str." ");
        $ss = mb_substr($str, $from, $to);
        $ctx = preg_match('~(.*)[\s]~sm', $ss, $matches);
        if ($ctx == 0) {
            $ctx = $ss . '...';
        } else {
            $ctx = $matches[1] . '...';
        }
        return self::ClearPHPTags($ctx);
    }
    
    private static function ClearPHPTags($param) {
        return str_replace(array('<?php', '?>', '<p>&nbsp;</p>'), array('&lt?php', '?&gt', ''), $param);
    }
}
