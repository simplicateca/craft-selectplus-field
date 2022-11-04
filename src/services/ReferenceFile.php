<?php
/**
 * Reference Field - Load reference json/twig file
 */

namespace simplicateca\referencefield\services;

use Craft;
use craft\base\Component;
use craft\helpers\FileHelper;

class ReferenceFile extends Component
{
    
    public function parse( $filename = null ): array {

        if( !$filename ) { return []; }

        $view = Craft::$app->getView();
		$templateMode = $view->getTemplateMode();
		$view->setTemplateMode($view::TEMPLATE_MODE_SITE);
			
		$twigVars   = [];
        $saneVal    = preg_replace('/[{}%]/', '', $filename, -1);
        $include    = '{% include "' . trim($saneVal) . '" ignore missing %}';
        $references = json_decode('[' . $view->renderString($include, $twigVars) . ']', true);
		$references = is_array( $references[0][0] ?? null ) ? $references[0] : $references;

		$view->setTemplateMode($templateMode);

        return $references ?? [];
    }

    // return all options as a "value => name" pair for a file path or an existing references array
    public function options( $source = null ): object {
        
        $references = is_array( $source )
            ? $source
            : $this->parse($source);

        return collect( $references )
            ->mapWithKeys( function ($item, $key) {
			    return [$item['value'] => $item['label']];
		    }
        );
    }


    // return a single reference by value
    public function reference( $source = null, $value = null ): mixed {
        
        $references = is_array( $source )
            ? $source
            : $this->parse($source);

        return collect( $references )
            ->whereIn('value', $value)->first();

    }    


    // get a list of possible reference files for autosuggest
    public function findAll(): array {

        $referenceFiles = FileHelper::findFiles( Craft::getAlias("@templates"), [
			'only' => ['*.json'],
		]);

		return collect($referenceFiles)
			->map(function ($path) {
				$baseDir = Craft::getAlias("@templates") . DIRECTORY_SEPARATOR;
				return str_replace( $baseDir, '', $path );
			})
            ->toArray();
    }

}