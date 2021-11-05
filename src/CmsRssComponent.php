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
class CmsRssComponent extends Component implements BootstrapInterface
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
     * @var bool включить фильтр по атрибуту isUkrnet
     */
    public $rss_filter_is_ukrnet = false;
    
    /**
     * @var string
     */
    public $contentIds = [];
    
    /**
     * @var string
     */
    public $timeZone = '';

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
            [['timeZone'], 'string'],
            [['contentIds'], 'safe'],
            [['rss_content_element_page_size', 'rss_filter_is_ukrnet'], 'integer'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'enableFeedsGenerator'    => \Yii::t('skeeks/rss', 'Enable altenate links to feeds'),
            'contentIds'              => \Yii::t('skeeks/cms', 'Elements of content'),
            'timeZone'                => \Yii::t('skeeks/rss', 'Time zone which display date in feed'),
            'rss_content_element_page_size' => \Yii::t('skeeks/rss', 'Content Elements Page Size'),
            'rss_filter_is_ukrnet' => \Yii::t('skeeks/rss', 'Content Elements isUkrnet filter enable'),
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'contentIds'              => \Yii::t('skeeks/rss', 'If nothing is selected, then all'),
            'timeZone'                => \Yii::t('skeeks/rss', 'If nothing, then show date in UTC'),
            'rss_content_element_page_size'        => \Yii::t('skeeks/rss',
                'Count elements on one page'),
            'rss_filter_is_ukrnet'        => \Yii::t('skeeks/rss',
                'Add and tunning related property with code isUkrnet, type boolean'),

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
                    'rss_filter_is_ukrnet'  => [
                        'class'        => BoolField::class,
                        'allowNull' => false,
                    ],
                    'contentIds'        => [
                        'class' => SelectField::class,
                        'multiple' => true,
                        'items' => \skeeks\cms\models\CmsContent::getDataForSelect(),
                    ],
                    'timeZone'  => [
                        'class'        => TextField::class,
                    ],
                    'rss_content_element_page_size'  => [
                        'class'        => TextField::class,
                    ],
                ],
            ],
        ];
    }


    public function bootstrap($application)
    {
        if (!$application instanceof \yii\web\Application) {
            return true;
        }

        /**
         * Генерация Rss feed links по дереву сайта
         */
        $application->view->on(View::EVENT_END_PAGE, function (Event $e) {

            /**
             * @var $view View
             */
            $view = $e->sender;

            if ($this->enableFeedsGenerator && !BackendComponent::getCurrent()) {
                if (!\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax) {
                    $this->_autoGenerateFeed($view);
                }
            }
        });
    }

    /**
     * @param View $view
     */
    protected function _autoGenerateFeed(\yii\web\View $view)
    {
        if (empty($this->contentIds))
            return;
        if(!isset($view->context->model))
            return;
        if(!isset($view->context->model->code))
            return;
        if (!$cmsContent = \skeeks\cms\models\CmsContent::findOne(['code' => $view->context->model->code]))
            return;
        if (in_array($cmsContent->id, $this->contentIds)) {
            $view->registerLinkTag([
                'href' => \frontend\helpers\Url::to('/rss/'.$view->context->model->code.'.xml', \Yii::$app->request->isSecureConnection ? 'https' : 'http'),
                'rel' => 'alternate',
                'type' => 'application/rss+xml',
            ]);
        }
    }

}
