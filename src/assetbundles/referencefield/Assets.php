<?php
/**
 * Reference Field - Asset Bundle
 *
 */

namespace simplicateca\referencefield\assetbundles\referencefield;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class Assets extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = '@simplicateca/referencefield/assetbundles/referencefield/dist';
        $this->depends    = [CpAsset::class];
        $this->js         = ['js/ReferenceField.js'];
        $this->css        = ['css/ReferenceField.css'];

        parent::init();
    }
}