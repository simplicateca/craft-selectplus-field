<?php

namespace simplicateca\selectplus\assetbundles\selectplus;

class SelectPlusAssets extends \craft\web\AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = '@simplicateca/selectplus/assetbundles/selectplus/dist';
        $this->depends    = [\craft\web\assets\cp\CpAsset::class];
        $this->js         = ['js/SelectPlus.js'];
        $this->css        = ['css/SelectPlus.css'];

        parent::init();
    }
}