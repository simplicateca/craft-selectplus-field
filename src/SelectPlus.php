<?php
namespace simplicateca\selectplus;

use Craft;
use craft\web\View;
use craft\services\Fields;
use craft\events\TemplateEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;

use yii\base\Event;
use yii\base\InvalidConfigException;

use simplicateca\selectplus\fields\SelectPlusField;

class SelectPlus extends \craft\base\Plugin
{
	public static SelectPlus $instance;

	public function __construct( $id, $parent = null, array $config = [] )
    {
        Event::on( View::class, View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
            $e->roots['_selectplus'] = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates'  . DIRECTORY_SEPARATOR . 'samples';
        });

        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $e) {
                $e->roots['_selectplus'] = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates'  . DIRECTORY_SEPARATOR . 'samples';
            }
        );

        static::setInstance($this);
		parent::__construct($id, $parent, $config);
	}


    public function init()
    {
        parent::init();
		self::$instance = $this;

        // Register our field
        Event::on( Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function ( RegisterComponentTypesEvent $event ) {
            $event->types[] = SelectPlusField::class;
        });

        // Load AssetBundle for Control Panel Requests
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on( View::class, View::EVENT_BEFORE_RENDER_TEMPLATE, function ( TemplateEvent $event ) {
                try {
                    Craft::$app->getView()->registerAssetBundle(
                        \simplicateca\selectplus\assetbundles\selectplus\SelectPlusAssets::class
                    );
                } catch ( InvalidConfigException $e ) {
                    Craft::error( 'Error registering AssetBundle - '.$e->getMessage(), __METHOD__ );
                }
            });
        }
    }

}