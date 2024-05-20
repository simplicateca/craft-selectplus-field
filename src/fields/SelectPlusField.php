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

    public static function icon(): string {
        return 'list-check';
    }

    public static function displayName(): string {
		return Craft::t('selectplus', 'Dropdown (SelectPlus)');
	}

    protected function optionsSettingLabel(): string {
        return Craft::t('selectplus', 'Options');
    }

	public function getContentColumnType(): string {
		return \yii\db\Schema::TYPE_TEXT;
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
		return Craft::$app->getView()->renderTemplate('selectplus/fields/settings', [
			'field'   => $this,
			'options' => ConfigHelper::findJsonFiles()
		]); // autosuggest json files in the `templates` directory
	}

	public function getInputHtml( mixed $value, ElementInterface $element = null ): string
    {
        $options = $value->config == $this->configFile
            ? $value->options
            : ConfigHelper::load( $this->configFile, $element );

		// when we load an option that no longer exists in the field configuration
        $deprecated = ( $value->value && !empty($value->value) && !in_array( $value->value, array_column( $options, 'value' ) ) );
        if( $deprecated ) {
            array_unshift( $options, [ 'value' => $value->value, 'label' => '[UNAVAILABLE: ' . $value->value . ']', 'disabled' => true ] );
		}

        return Craft::$app->getView()->renderTemplate('selectplus/fields/dropdown', [
            'field' 	 => $this,
            'value' 	 => $value,
            'options' 	 => $options,
			'deprecated' => $deprecated,
            'namespace'  => Craft::$app->getView()->getNamespace()
		]);
	}

	public function normalizeValue( $value, ElementInterface $element = null ): mixed
    {
        if( $value instanceof SelectPlusData ) {
            return $value;
        }

        $data = [
            'value' => '',
            'json'  => '{}',
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
                        : [ 'value' => $value ];
                }
            }

            if( \is_array($value) ) {
                $data = array_merge( $data, array_filter($value) );
            }
        }

        $data['config']  = $this->configFile;
        $data['element'] = $element;

        return new SelectPlusData( $data );
	}
}