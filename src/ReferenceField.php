<?php
namespace simplicateca\referencefield;

use Craft;
use craft\web\View;
use craft\base\Plugin;
use craft\services\Fields;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\TemplateEvent;

use yii\base\Event;

use simplicateca\referencefield\fields\Dropdown;
use simplicateca\referencefield\models\Settings;
use simplicateca\referencefield\services\DomManipulator;
use simplicateca\referencefield\services\ReferenceFile;
use simplicateca\referencefield\twigextensions\Functions;
use simplicateca\referencefield\assetbundles\referencefield\Assets;

class ReferenceField extends Plugin
{
	public static ReferenceField $instance;

	public function __construct($id, $parent = null, array $config = [])
	{
		// Define an alias to the module for any paths we use
		Craft::setAlias('@modules/referencefield', $this->getBasePath());

		// Base template directory
		Event::on(
			View::class,
			View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
			function (RegisterTemplateRootsEvent $e
		) {
			if( is_dir($baseDir = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates') ) {
				$e->roots[$this->id] = $baseDir;
			}
		});

		// Set this as the global instance of this module class
		static::setInstance($this);
		parent::__construct($id, $parent, $config);
	}

    public function init()
    {
        parent::init();
		self::$instance = $this;

		// load components
		$this->setComponents([
			'domManipulator' => DomManipulator::class,
            'referenceFile'  => ReferenceFile::class,
		]);
		
        // load twig extensions
		Craft::$app->view->registerTwigExtension(
            new Functions()
        );

        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = Dropdown::class;
            }
        );

        // Load our AssetBundle
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(
                View::class,
                View::EVENT_BEFORE_RENDER_TEMPLATE,
                function (TemplateEvent $event) {
                    try {
                        Craft::$app->getView()->registerAssetBundle(Assets::class);
                    } catch (InvalidConfigException $e) {
                        Craft::error(
                            'Error registering AssetBundle - '.$e->getMessage(),
                            __METHOD__
                        );
                    }
                }
            );
        }

        // move the entry type field into the content area
        // and turn it into a reference field
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(
                View::class,
                View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
                function (TemplateEvent $event) {
                    return $this->domManipulator->entryTypeAsReference( $event );
                }
            );
        }
    }

    protected function createSettingsModel(): ?craft\base\Model
    {
    	return new Settings();
	}
}