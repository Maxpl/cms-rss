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
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\models\Tree;
use yii\db\ActiveQuery;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;

/**
 * Class FeedController
 * @package skeeks\cms\rss\controllers
 */
class FeedController extends Controller
{
    public $tree = null;

    /**
     * @param $code
     */
    public function actionFeed($code)
    {
        \Yii::$app->response->format = Response::FORMAT_XML;
        $this->layout = false;

        $filename = \Yii::getAlias('@frontend/web/assets/').\Yii::$app->request->pathInfo;

        self::_checkCache($filename);

        $code = $this->getTree($code);

        ini_set("memory_limit", "512M");

        $result = [];

        $this->_addElements($result, $code);

        $content = $this->render($this->action->id, [
            'tree' => $this->tree,
            'code' => $code,
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

        $code = $this->getTree($code);

        ini_set("memory_limit", "512M");

        $result = [];

        $this->_addElements($result, $code, true);

        $content = $this->render('feed', [
            'tree' => $this->tree,
            'code' => $code,
            'data' => $result
        ]);

        self::_sendCache($filename, $content);

        \Yii::$app->response->content = $content;

        return;
    }
    
    /**
     * @param $code
     */
    public function actionUkrnet($code)
    {
        \Yii::$app->response->format = Response::FORMAT_XML;
        $this->layout = false;

        $filename = \Yii::getAlias('@frontend/web/assets/').\Yii::$app->request->pathInfo;

        self::_checkCache($filename);

        $code = $this->getTree($code);

        ini_set("memory_limit", "512M");

        $result = [];

        $this->_addElementsYa($result, $code);

        $content = $this->render($this->action->id, [
            'tree' => $this->tree,
            'code' => $code,
            'data' => $result
        ]);

        self::_sendCache($filename, $content);

        \Yii::$app->response->content = $content;

        return;
    }

    /**
     * @param $code
     */
    public function actionYandex($code)
    {
        \Yii::$app->response->format = Response::FORMAT_XML;
        $this->layout = false;

        $filename = \Yii::getAlias('@frontend/web/assets/').\Yii::$app->request->pathInfo;

        self::_checkCache($filename);

        $code = $this->getTree($code);

        ini_set("memory_limit", "512M");

        $result = [];

        $this->_addElementsYa($result, $code);

        $content = $this->render($this->action->id, [
            'tree' => $this->tree,
            'code' => $code,
            'data' => $result
        ]);

        self::_sendCache($filename, $content);

        \Yii::$app->response->content = $content;

        return;
    }

    public function actionAllElkz()
    {
        if (!\Yii::$app->rss->enableFeedsConcat) {
            throw new NotFoundHttpException("Feed not found or inactive");
        }

        \Yii::$app->response->format = Response::FORMAT_XML;
        $this->layout = false;

        $filename = \Yii::getAlias('@frontend/web/assets/').\Yii::$app->request->pathInfo;

        self::_checkCache($filename);

        ini_set("memory_limit", "512M");

        $result = [];

        $this->_addElementsYa($result, 'all');

        $content = $this->render($this->action->id, [
            'tree' => $this->tree,
            'code' => 'all-elkz',
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
        $query = self::getElementsQuery($contentCode, $this->tree);

        if (!is_null($query)) {
            $elements = $query->all();
        } else {
            return $this;
        }

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
                        "dt_start" => self::getRssDate($model->published_at),
                        "img_src" => \frontend\helpers\Image::getModelImageUrl($model),
                        "text" => $fullText ? htmlspecialchars($model->description_full) : self::html_mb_substr($model->description_full,0,150),
                        "category" => $model->tree_id ? $model->cmsTree->name : '',
                    ];
            }
        }

        return $this;
    }

    /**
     * @param array $data
     * @param string $contentCode
     * @param bool $fullText default false
     * @return $this
     */
    protected function _addElementsYa(&$data = [], $contentCode)
    {
        if ($contentCode == 'all') {
            $query = self::getAllElementsQuery();
        } else {
            $query = self::getElementsQuery($contentCode, $this->tree);
        }

        if (!is_null($query)) {
            $elements = $query->all();
        } else {
            return $this;
        }

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
                        "dt_start" => self::getRssDate($model->published_at),
                        "img_src" => \frontend\helpers\Image::getModelImageUrl($model),
                        "text" => self::html_mb_substr($model->description_full,0,150),
                        "full-text" => htmlspecialchars($model->description_full),
                        "category" => $model->tree_id ? $model->cmsTree->name : '',
                    ];
            }
        }

        return $this;
    }
    
    /**
     * Return ActiveQuery
     * @param string $contentCode
     * @param object Tree
     * @return object ActiveQuery
     */
    public static function getElementsQuery($contentCode, $tree): ?ActiveQuery
    {
        if (!$cmsContent = CmsContent::findOne(['code' => $contentCode])) {
            return null;
        }
        if (!in_array($cmsContent->id, \Yii::$app->rss->contentIds)) {
            return null;
        }
        $query = CmsContentElement::find()
            ->joinWith('cmsTree')
            ->andWhere([Tree::tableName() . '.cms_site_id' => \Yii::$app->skeeks->site->id]);

        $query->andWhere(['content_id' => $cmsContent->id]);

        //Add rubrics
        if ($tree && $tree->code != $contentCode) {
            $query->andWhere(['tree_id' => $tree->id]);        
        }
        if (\Yii::$app->controller->action->id == 'ukrnet' && \Yii::$app->rss->rss_filter_is_ukrnet) {

            if ($propertyModel = CmsContentProperty::find()->where(['code' => 'isUkrnet'])->one()) {

                $query->joinWith('cmsContentElementProperties map')
                    ->andWhere(['map.property_id' => $propertyModel->id])
                    ->andWhere(['map.value_enum' => 1]);
            }
        }

        return self::getBaseQuery($query);
    }

    /**
     * Return ActiveQuery
     * @return object ActiveQuery
     */
    public static function getAllElementsQuery(): ?ActiveQuery
    {
        if (count(\Yii::$app->rss->contentIds) < 1)
            return null;

        $query = CmsContentElement::find()
            ->joinWith('cmsTree')
            ->andWhere([Tree::tableName() . '.cms_site_id' => \Yii::$app->skeeks->site->id]);

        $query->andWhere(['content_id' => \Yii::$app->rss->contentIds]);

        return self::getBaseQuery($query);
    }

    protected static function getBaseQuery(ActiveQuery $query): ActiveQuery
    {
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

        return $query->orderBy(['updated_at' => SORT_DESC, 'priority' => SORT_DESC]);
    }
    
    /**
     * Get Tree model
     * @param string $code
     * @return string $code 
     * @throws NotFoundHttpException
     */
    public function getTree($code) {

        if (strpos($code, '-')) {
            $subCode = substr($code, strpos($code, '-') + 1, strlen($code));
            $code = stristr($code, '-', true);
            if ($subTree = Tree::findOne(['code' => $subCode]))
                $this->tree = $subTree;
        } else { 
            $this->tree = Tree::findOne(['code' => $code]);
        }

        if (!$this->tree)
            throw new NotFoundHttpException(\Yii::t('skeeks/cms', 'Page not found'));

        return $code;
    }

    /**
     * Если файл существует и кеш еще не протух то работа приложения
     * завершается отдачей файла
     * @param string $filename
     * @return type file
     */
    private static function _checkCache($filename) {

        if (!\Yii::$app->rss->isCache)
            return;

        $expire = \Yii::$app->cache->get('rss:'.\Yii::$app->request->pathInfo);

        if ($expire && $expire > date('Y-m-d H:i:s') && is_file($filename)) {

            \Yii::$app->response->content = file_get_contents($filename);

            \Yii::$app->end();
        }
    }
    
    /**
     * Check get and send to cache
     * @param string $filename
     * @param string $content
     * @param string $delay +1 hours default
     */
    private static function _sendCache($filename, $content, $delay='+1 hours') {
        if( is_dir(\Yii::getAlias('@frontend/web/assets/rss')) || @mkdir(\Yii::getAlias('@frontend/web/assets/rss'), 0777, true) ) {
            file_put_contents($filename, $content);
            @chmod($filename,0666);
            \Yii::$app->cache->set('rss:'.\Yii::$app->request->pathInfo, date('Y-m-d H:i:s',strtotime($delay)));
        } else
            \Yii::warning("Can not create directory '".dirname($filename));
    }

    /**
     * HTML to string shortener
     * @param string $str Text or HTML text
     * @param integer $from
     * @param integer $to
     * @return string
     */
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
    
    /**
     * Remove php tags
     * @param string $param
     * @return string
     */
    private static function ClearPHPTags($param) {
        return str_replace(array('<?php', '?>', '<p>&nbsp;</p>'), array('&lt?php', '?&gt', ''), $param);
    }

    /**
     * Get Rss date format with \Yii::$app->rss->timeZone or \Yii::$app->timeZone
     * @param type $time
     * @return string $rssDate rss date fornat
     */
    private static function getRssDate($time)
    {
        if (\Yii::$app->rss->timeZone && \Yii::$app->rss->timeZone <> \Yii::$app->timeZone)
        {
            date_default_timezone_set(\Yii::$app->rss->timeZone);

            $rssDate = date(DATE_RSS, $time);

            date_default_timezone_set(\Yii::$app->timeZone);
        } else 
            $rssDate = date(DATE_RSS, $time);

        return $rssDate;
    }
}
