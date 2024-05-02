<?php
/**
 * SelectPlus Field
 */

namespace simplicateca\selectplus\fields;

use Craft;
use craft\base\Field;
use craft\base\ElementInterface;
use craft\base\SortableFieldInterface;
use craft\base\PreviewableFieldInterface;

use simplicateca\selectplus\helpers\ConfigHelper;
use simplicateca\selectplus\fields\SelectPlusData;

class SelectPlusField extends Field implements PreviewableFieldInterface, SortableFieldInterface
{
	public string $configFile = '';

    public ?string $columnType = null;

    public static function displayName(): string {
		return Craft::t('selectplus', 'Dropdown (SelectPlus)');
	}


    protected function optionsSettingLabel(): string {
        return Craft::t('selectplus', 'Options');
    }


	public function getContentColumnType(): string {
		return \yii\db\Schema::TYPE_TEXT;
	}


	public function normalizeValue( $value, ElementInterface $element = null ): mixed {

        if( $value instanceof SelectPlusData ) {
            return $value;
        }

        $data = [
            'value'   => '',
            'json'    => '{}',
            'element' => $element,
            'config'  => $this->configFile,
        ];

        if( !empty($value) ) {

            if( \is_string($value) ) {
                $jsonValue = \craft\helpers\Json::decodeIfJson($value);

                if( \is_string($jsonValue) ) {
                    $data['value'] = $jsonValue;
                }

                if( \is_array($jsonValue) ) {

                    $value = ( !array_diff_key($data, $jsonValue) && !array_diff_key($jsonValue, $data) )
                        ? $jsonValue
                        : [ 'value' => $value, 'json'  => '{}' ];
                }
            }

            if( \is_array($value) ) {
                $data = array_merge( $data, array_filter($value) );
            }
        }

        return new SelectPlusData( $data );
	}


	public function getTableAttributeHtml( $value, ElementInterface $element = null ): string {
		return ucwords(
			preg_replace('/(?<!\ )[A-Z]/', ' $0', ( $value->value ?? $value['value'] ?? $value ?? null ) )
		);
	}


    public function getElementConditionRuleType(): array|string|null {
        return \simplicateca\selectplus\fields\SelectPlusConditionRule::class;
    }


	public function getSettingsHtml(): ?string {
		return Craft::$app->getView()->renderTemplate('selectplus/field-dropdown-settings', [
			'field'   => $this,
			'options' => ConfigHelper::findJsonFiles() // autosuggest json files in the `templates` directory
		]);
	}


	public function getInputHtml( $value, ElementInterface $element = null ): string {

        $config = ConfigHelper::load( $this->configFile, [
            'value'   => $value->value,
            'element' => $element,
        ] );

        $options = array_combine(
            array_column( $config, 'value' ),
            array_column( $config, 'label' )
        );

		// $id = Craft::$app->getView()->formatInputId( $this->handle );
        // $namespace = Craft::$app->getView()->namespaceInputId($id);

		// what to do when we load an option that no longer exists in the field config?
		// TODO: try to find & set a new default value ??
        $error = ( $value->value && !empty($value->value) && !in_array( $value->value, array_keys( $options ) ) );
        if( $error ) {
            array_unshift( $options, [ 'value' => $value->value, 'label' => '[UNAVAILABLE]', 'disabled' => true ] );
		}

        return Craft::$app->getView()->renderTemplate('selectplus/field-dropdown-input', [
			'field' 	=> $this,
			'element' 	=> ConfigHelper::minimizeElement( $element ),
            'options' 	=> $options,
			'config' 	=> $config,
			'error' 	=> $error,
			'value' 	=> $value,
            'configfile' => $this->configFile
		]);
	}
}
