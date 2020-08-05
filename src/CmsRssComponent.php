<?php
/**
 * @author Maks Sloboda <msloboda@ukr.net>
 * @link http://skeeks.com/
 * @copyright 2020 SkeekS (СкикС)
 * @date 04.08.2020
 */

namespace skeeks\cms\rss;

use kartik\datecontrol\DateControl;
use skeeks\cms\backend\BackendComponent;
use skeeks\cms\backend\widgets\ActiveFormBackend;
use skeeks\cms\base\Component;
use skeeks\cms\helpers\StringHelper;
use skeeks\cms\rss\assets\CmsRssAsset;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\TextField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\ActionEvent;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\Widget;
use yii\base\WidgetEvent;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Application;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\ListView;

/**
 *
 * @author Maks Sloboda <msloboda@ukr.net>
 */
class CmsRssComponent extends Component
{
    /**
     * @var int
     */
    public $rss_content_element_page_size = 20;

    /**
     * @var bool включить автогенерацию мета ссылок на рсс ленты
     */
    public $enableFeedsGenerator = false;
    
    /**
     * @var string
     */
    public $contentIds = [];

    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name'        => \Yii::t('skeeks/rss', 'Rss'),
            'description' => 'Rss feeds',
            'image'       => [
                CmsRssAsset::class,
                'icons/rss-icon.png',
            ],
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['enableFeedsGenerator'], 'integer'],
            [['contentIds'], 'safe'],
            ['rss_content_element_page_size', 'integer'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'enableFeedsGenerator'    => \Yii::t('skeeks/rss', 'Enable meta links to feeds'),
            'contentIds'              => \Yii::t('skeeks/cms', 'Elements of content'),
            'rss_content_element_page_size' => \Yii::t('skeeks/rss', 'Content Elements Page Size'),
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'contentIds'              => \Yii::t('skeeks/rss', 'If nothing is selected, then all'),
            'rss_content_element_page_size'        => \Yii::t('skeeks/rss',
                'Количество елементов контента на одной странице'),

        ]);
    }


    /**
     * @return ActiveForm
     */
    public function beginConfigForm()
    {
        return ActiveFormBackend::begin();
    }

    /**
     * @return array
     */
    public function getConfigFormFields()
    {
        return [
            'rss' => [
                'class'          => FieldSet::class,
                'name'           => \Yii::t('skeeks/rss', 'Rss settings'),
                'elementOptions' => [
                    'isOpen' => true,
                ],
                'fields'         => [
                    'enableFeedsGenerator' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'contentIds'        => [
                        'class' => SelectField::class,
                        'multiple' => true,
                        'items' => \skeeks\cms\models\CmsContent::getDataForSelect(),
                    ],
                    'rss_content_element_page_size'  => [
                        'class'        => TextField::class,
                    ],
                ],
            ],
        ];
    }


//    public function bootstrap($application)
//    {
//        if (!$application instanceof \yii\web\Application) {
//            return true;
//        }
//
//        /**
//         * Генерация Rss feed links по дереву сайта
//         */
//        $application->view->on(View::EVENT_BEGIN_BODY, function (Event $e) {
//
//            /**
//             * @var $view View
//             */
//            $view = $e->sender;
//
//            if ($this->enableFeedsGenerator && !BackendComponent::getCurrent()) {
//                if (!\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax) {
//                    $this->_autoGenerateFeed($view);
//                }
//            }
//        });
//    }

    /**
     * @param View $view
     */
    protected function _autoGenerateFeed(\yii\web\View $view)
    {
//        if (!isset($view->metaTags['keywords'])) {
//            $view->registerMetaTag([
//                "name"    => 'keywords',
//                "content" => $this->_getKeywordsByContent($content),
//            ], 'keywords');
//        }
    }

}