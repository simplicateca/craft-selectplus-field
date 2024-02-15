<?php
/**
 * SelectPlusData - Store the serialized field data
 */

namespace simplicateca\selectplus\fields;

use Craft;
use simplicateca\selectplus\helpers\ConfigHelper;

class SelectPlusData extends \craft\base\Model
{
    /**
     * @var string The primary value of the selected option
     */
    public string $value = '';


    /**
     * @var string The json encoded string of extra input settings
     */
    public string $json = '{}';


    /**
     * @var string The path to the configuration file
     */
    public string $config;


    /**
     * @var object The Craft CMS Element object this field is attached to.
     *
     * We store this so that it can be used to as a variable (if necessary) when
     * parsing the JSON config file (which can actually contain TWIG code)
     */
    public array $element;


    public function __construct( array $settings )
    {
        $settings = array_merge([
            'value'   => '',
            'json'    => '{}',
            'config'  => '',
            'element' => null,
        ], $settings);

        $this->value   = $settings['value'];
        $this->json    = $settings['json'];
        $this->config  = $settings['config'];
        $this->element = $settings['element'];
    }


    public function __get( $property ) {

        if( strtolower( $property ) == 'settings' ) {
            return array_merge( $this->setting('settings'), $this->data() );
        }

        return $this->data( $property )
            ?? $this->setting( $property )
            ?? null;
    }


	public function __toString(): string
	{
        return (string) $this->value;
	}


    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules = array_merge($rules, [
            ['value',  'string'],
            ['json',   'string'],
            ['config', 'string'],
        ]);

        return $rules;
    }


	/**
	 * Returns a JSON hash for the selected option (or the first option)options for a field from the JSON config -or- a single option by key
	 *
	 * @return object|null Either return the value from the config or null
	 */
	private function setting( $key = null ): mixed
	{
        $options = collect( ConfigHelper::load( $this->config, $this->asArray() ) );

        // we want to try our best to return *something* here.
		// so first we look for the matching value, then the first default key
		// we default to the first field in the config file
		$selected = $options->whereIn('value', $this->value)->first() ?? $options->first();

        $settings = [ 'value' => $selected['value'] ?? '', 'label' => $selected['label'] ?? '' ];
        $settings = array_merge( $settings, $selected['settings'] ?? [] );

        return $key
            ? $selected[$key] ?? null
            : $selected ?? [];
	}


	/**
	 * Returns the entire input data (json) as an object or a single field from the object
	 *
	 * @return object|null Either return the value from the input data object or null
	 */
	public function data( $field = null ): mixed
	{
        $json = json_decode( $this->json, true );

        return ( $field )
            ? $json[$field] ?? null
            : $json ?? [];
	}


    public function asArray(): array
    {
        return [
            'value'   => $this->value,
            'json'    => $this->json,
            'element' => $this->element,
        ];
    }
}
