<?php
namespace simplicateca\selectplus;

use Craft;
use craft\web\View;

use yii\base\Event;
use craft\events\TemplateEvent;
use craft\events\RegisterTemplateRootsEvent; 
use craft\events\RegisterComponentTypesEvent;

use craft\services\Fields;
use simplicateca\selectplus\fields\SelectPlusField;

class SelectPlus extends \craft\base\Plugin
{
	public static SelectPlus $instance;

	public function __construct( $id, $parent = null, array $config = [] )
    {
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