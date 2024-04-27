<?php
/**
 * Utility functions for managing SelectPlus JSON config files
 */

namespace simplicateca\selectplus\helpers;

use Craft;

class ConfigHelper
{
    public static function load( string $filename = null, array|null $twigVars = [] ): array {

        if( !$filename ) { return []; }

        // Tell Craft we don't want to render to the regular output right now
        $view = Craft::$app->getView();
		$templateMode = $view->getTemplateMode();
		$view->setTemplateMode($view::TEMPLATE_MODE_SITE);

        // Force the config to render as an array rather than risk it being a single object.
        // Its easy enough to pull the inner array out and move on with life than risk throwing errors.
        $jsonEncodedOptions = '[' . $view->renderString( ConfigHelper::getConfigInclude( $filename ), [
            'object' => ConfigHelper::minimizeElement( $twigVars['element'] ?? null ),
            'value'  => $twigVars['value'] ?? null,
        ] ) . ']';

        // Decode the the json and make sure we don't have a nested array situation per the above comment
        $options = json_decode( $jsonEncodedOptions, true );
        $options = is_array( $options[0][0] ?? null ) ? $options[0] : $options;

        // Tell Craft to return to whatever template render mode it was in before
        $view->setTemplateMode($templateMode);

        return $options;
    }


    // return all options as a "value => name" pair for a file path or an existing options array
    public static function options( $source = null ): object {
        $options = is_array( $source ) ? $source : ConfigHelper::load( $source );
        return collect( $options )
            ->mapWithKeys( function ($item, $key) {
			    return [$item['value'] => $item['label']];
		    }
        );
    }


    // return a single option hash by key value
    public static function option( $source = null, $value = null ): mixed {
        $options = is_array( $source ) ? $source : ConfigHelper::load( $source );
        return array_filter($options, function ($item) use ($value) {
            return $item['value'] === $value;
        })[0] ?? null;
    }


    // get a list of possible json config files for autosuggest
    public static function findJsonFiles(): array {

        $configFiles = \craft\helpers\FileHelper::findFiles(
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

    private static function getConfigInclude( string $filename ): string {
        $sanitized = trim( preg_replace('/[{}%]/', '', $filename, -1) );
        return "{% include '{$sanitized}' ignore missing %}";
    }


    public static function minimizeElement( $element = null ): array {

        if( !$element ) { return []; }

        if( is_array( $element ) ) {
            return $element;
        }

        // find the overall owner of this field (if it's different from the immediate element)
        // i.e. is this field attached directly to an entry element or is it part of a matrix
        // or a super table field that is itself attached to an entry element?
        $owner = $element->owner ?? null;
        if( $owner && get_class( $owner ) == get_class( $element ) ) {
            $owner = null;
        }

        return [
            'id'     => $element->id ?? null,
            'class'  => get_class( $element ),
            'type'   => $element->type->handle        ?? null,
            'field'  => $element->type->field->handle ?? null,
            'section'=> $element->section->handle     ?? null,
            'owner'  => $owner ? [
                'id'     => $owner->id ?? null,
                'class'  => get_class( $owner ),
                'section'=> $owner->section->handle ?? null,
                'type'   => $owner->type->handle    ?? null,
            ] : null,
        ];
    }
}