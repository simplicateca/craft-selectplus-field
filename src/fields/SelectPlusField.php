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

    public static function displayName(): string
	{
		return Craft::t('selectplus', 'Dropdown (SelectPlus)');
	}


    protected function optionsSettingLabel(): string
    {
        return Craft::t('selectplus', 'Options');
    }


	public function getContentColumnType(): string
	{
		return \yii\db\Schema::TYPE_TEXT;
	}


	public function normalizeValue( $value, ElementInterface $element = null ): mixed
	{
        if( $value instanceof SelectPlusData ) {
            return $value;
        }

        $data = [
            'value'   => '',
            'json'    => '{}',
            'element' => [],
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

        // find the overall owner of this field (if it's different from the immediate element)
        // i.e. is this field attached directly to an entry element or is it part of a matrix
        // or a super table field that is itself attached to an entry element?
        $owner = $element->owner ?? null;
        if( $owner && get_class( $owner ) == get_class( $element ) ) {
            $owner = null;
        }

        $data['element'] = [
            'id'     => $element->id,
            'class'  => get_class( $element ),
            'type'   => $element->type->handle        ?? null,
            'field'  => $element->type->field->handle ?? null,
            'section'=> $element->section->handle     ?? null,
            'owner'  => $owner ? [
                'id'     => $owner->id,
                'class'  => get_class( $owner ),
                'section'=> $owner->section->handle ?? null,
                'type'   => $owner->type->handle    ?? null,
            ] : null,
        ];

        return new SelectPlusData( $data );
	}


	public function getTableAttributeHtml( $value, ElementInterface $element = null ): string
	{
		return ucwords(
			preg_replace('/(?<!\ )[A-Z]/', ' $0', ( $value->value ?? $value['value'] ?? $value ?? null ) )
		);
	}


    public function getElementConditionRuleType(): array|string|null
    {
        return \simplicateca\selectplus\fields\SelectPlusConditionRule::class;
    }


	public function getSettingsHtml(): ?string
	{
		// get a list of json files in the craftcms `templates` directory for the autosuggest
		return Craft::$app->getView()->renderTemplate('selectplus/field-dropdown-settings', [
			'field'   => $this,
			'options' => ConfigHelper::findJsonFiles(),
		]);
	}


	public function getInputHtml( $value, ElementInterface $element = null ): string
	{
		$options = ConfigHelper::load( $value->config ?? null, $value->asArray() );
		$error   = false;

        $optIndex = array_combine(
            array_column( $options, 'value' ),
            array_column( $options, 'label' )
        );

		// what to do when we load an option that no longer exists in the field config?
		// probably have to do something in the
		if( $value->value && !empty($value->value) && !in_array( $value->value, array_keys( $optIndex ) ) ) {

            array_unshift( $optIndex, [ 'value' => $value->value, 'label' => '[UNAVAILABLE]', 'disabled' => true ] );
            $error = true;

		 	// try to find & set a new default value
			// .... TODO
		}

		$id = Craft::$app->getView()->formatInputId($this->handle);
        $namespace = Craft::$app->getView()->namespaceInputId($id);

        return Craft::$app->getView()->renderTemplate('selectplus/field-dropdown-input', [
			'field' 	=> $this,
			'id' 		=> $id,
			'namespace' => $namespace,
			'optIndex' 	=> $optIndex,
			'options' 	=> $options,
			'error' 	=> $error,
			'value' 	=> $value
		]);
	}
}
