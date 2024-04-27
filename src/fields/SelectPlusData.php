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


    private array $_settings = [];
    private array $current  = [];
    private array $virtuals = [];

    public function __construct( array $data )
    {
        $data = array_merge([
            'value'   => '',
            'json'    => '{}',
            'config'  => '',
            'element' => null,
        ], $data);

        $this->element  = ConfigHelper::minimizeElement( $data['element'] );
        $this->value    = $data['value'];
        $this->json     = $data['json'];
        $this->config   = $data['config'];

        $options = collect( ConfigHelper::load( $this->config, [
            'value'   => $this->value,
            'element' => $this->element,
        ] ) );

        $this->virtuals = json_decode( $this->json, true );

        $this->current  = $options->firstWhere( 'value', $this->value )
                       ?? $options->first()
                       ?? [];

        $this->_settings = array_merge(
            $this->current['settings'] ?? [],
            $this->virtuals ?? []
        );
    }


	public function __toString(): string{
        return (string) $this->value;
	}


    public function __isset($name) {
        return strtolower($name) == 'settings' || isset( $this->_settings[$name] );
    }


    public function __call($name, $arguments) {
        if( (strtolower($name) == 'settings') ) return $this->_settings;
        return isset( $this->_settings[$name] ) ? $this->_settings[$name] : null;
    }


    public function __get($name) {
        if( (strtolower($name) == 'settings') ) return $this->_settings;
        return isset( $this->_settings[$name] ) ? $this->_settings[$name] : null;
    }


    public function rules(): array {
        return array_merge( parent::rules(), [
            ['value',  'string'],
            ['json',   'string'],
            ['config', 'string'],
        ]);
    }
}