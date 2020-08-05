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
     * @param array $data
     * @return $this
     */
//    protected function _addTrees(&$data = [])
//    {
//        $query = Tree::find()->where(['cms_site_id' => \Yii::$app->skeeks->site->id]);
//
//        if (\Yii::$app->seo->activeTree) {
//            $query->andWhere(['active' => 'Y']);
//        }
//
//        if (\Yii::$app->seo->treeTypeIds) {
//            $query->andWhere(['tree_type_id' => \Yii::$app->seo->treeTypeIds]);
//        }
//
//        $trees = $query->orderBy(['level' => SORT_ASC, 'priority' => SORT_ASC])->all();
//
//        if ($trees) {
//            /**
//             * @var Tree $tree
//             */
//            foreach ($trees as $tree) {
//                if (!$tree->redirect && !$tree->redirect_tree_id) {
//                    $data[] =
//                        [
//                            "loc"     => $tree->absoluteUrl,
//                            "lastmod" => $this->_lastMod($tree),
//                            "changefreq" => "weekly",
//                            "priority" => $this->_calculatePriority($tree)
//                        ];
//                }
//            }
//        }
//
//        return $this;
//    }


//    protected function _addContents(&$data = [])
//    {
//
//        if (!\Yii::$app->seo->contentIds) {
//            return;
//        }
//
//        $query = CmsContent::find()
//            ->select(['*', '(select count(*) FROM cms_content_element cce WHERE cce.content_id = cc.id AND cce.active = "Y" AND cce.published_at <= NOW()) as count'])
//            ->from('cms_content cc')
//            ->where(['id' => \Yii::$app->seo->contentIds]);
//
//        $contents = $query->orderBy(['updated_at' => SORT_DESC, 'priority' => SORT_ASC])->all();
//
//        //Добавление элементов в карту
//        if ($contents) {
//            /**
//             * @var CmsContent $model
//             */
//            foreach ($contents as $model) {
//
//                $pages = ceil($model->raw_row['count'] / \Yii::$app->seo->sitemap_content_element_page_size);
//
//                for ($p = 1; $p <= $pages; $p++) {
//                    $data[] = [
//                        "loc"     => Url::to(['/seo/sitemap/content', 'code' => $model->code, 'page' => $p], true),
//                        "lastmod" => $this->_lastMod($model),
//                    ];
//                }
//            }
//        }
//
//        return $this;
//    }

    /**
     * @param array $data
     * @return $this
     */
    protected function _addElements(&$data = [], $contentCode)
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

        //Добавление элементов в карту
        if ($elements) {
            /**
             * @var CmsContentElement $model
             */
            foreach ($elements as $model) {
                $data[] =
                    [
                        "name" => $model->name,
                        "url"     => $model->absoluteUrl,
                        "dt_start" => date(DATE_RSS, $model->publishet_at),
                        "img_src" => \frontend\helpers\Image::getModelImageUrl($model),
                        "text" => $model->description_full,
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
}
