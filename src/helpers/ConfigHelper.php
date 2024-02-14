<?php
/**
 * Utility functions for managing SelectPlus JSON config files
 */

namespace simplicateca\selectplus\helpers;

use Craft;
use craft\helpers\FileHelper;

class ConfigHelper
{
    public static function load( string $filename = null, array $model = [] ): array
    {
        if( !$filename ) { return []; }

        $view = Craft::$app->getView();
		$templateMode = $view->getTemplateMode();
		$view->setTemplateMode($view::TEMPLATE_MODE_SITE);

        $cleanPath  = preg_replace('/[{}%]/', '', $filename, -1);
        $include    = '{% include "' . trim($cleanPath) . '" ignore missing %}';

        $twigVars   = $model ?? [];
        $twigString = '[' . $view->renderString($include, $twigVars) . ']';
        $view->setTemplateMode($templateMode);

        $options = json_decode($twigString, true);
		$options = is_array( $options[0][0] ?? null ) ? $options[0] : $options;

        return $options ?? [];
    }

    // return all options as a "value => name" pair for a file path or an existing options array
    public static function options( $source = null ): object
    {
        $options = is_array( $source ) ? $source : $this->load( $source );

        return collect( $options )
            ->mapWithKeys( function ($item, $key) {
			    return [$item['value'] => $item['label']];
		    }
        );
    }


    // return a single option hash by key value
    public static function option( $source = null, $value = null ): mixed
    {
        $options = is_array( $source ) ? $source : $this->load($source);

        $filter  = array_filter($options, function ($item) use ($value) {
            return $item['value'] === $value;
        });

        return $filter[0] ?? null;
    }


    // get a list of possible json config files for autosuggest
    public static function findJsonFiles(): array
    {
        $configFiles = FileHelper::findFiles(
            Craft::getAlias("@templates"),
            [ 'only' => ['*.json']
        ]);

		return
            collect( $configFiles )
            ->map( function ($path) {
                $baseDir = Craft::getAlias("@templates") . DIRECTORY_SEPARATOR;
                return str_replace( $baseDir, '', $path );
            })
            ->toArray();
    }

}