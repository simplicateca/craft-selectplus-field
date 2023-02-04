<?php
/**
 * Reference Field - Dropdown
 */

namespace simplicateca\referencefield\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\base\SortableFieldInterface;
use craft\fields\conditions\TextFieldConditionRule;
use craft\base\Field;
use craft\helpers\Json;
use yii\db\Schema;

use simplicateca\referencefield\ReferenceField;
use simplicateca\referencefield\models\Reference;

class Dropdown extends Field implements PreviewableFieldInterface, SortableFieldInterface
{
	public $referenceFile;

	public static function displayName(): string
	{
		return Craft::t('referencefield', 'Dropdown (reference)');
	}

	public function getContentColumnType(): string
	{
		return Schema::TYPE_STRING;
	}

	public function normalizeValue( $value, ElementInterface $element = null ): mixed
	{
		if( $value instanceof Reference ) {
			return $value;
		}

		if( !$element ) {
			return null;
		}

		// we're here when we're saving
		if( is_array($value) ) {
			$value = Json::encode($value);
		}

		$data = json_decode( $value ?? '{}', true);

		if( $data ) {
			$data['elementClass'] = ( $element->owner ?? false ) ? get_class( $element->owner ) : '';
			$data['elementId']    = $element->ownerId ?? '';
			$data['refpath']      = $this->referenceFile;

			return new Reference($data);
		}

		return $value ?? null;
	}

	public function getTableAttributeHtml( $value, ElementInterface $element = null ): string
	{	 
		return ucwords(
			preg_replace('/(?<!\ )[A-Z]/', ' $0', ( $value ? $value->value : '' ) )
		);
	}

    /**
     * @inheritdoc
     */
    public function getElementConditionRuleType(): array|string|null
    {
        return TextFieldConditionRule::class;
    }


	public function getSettingsHtml(): ?string
	{
		// get a list of files for autosuggest	
		$referenceFiles = ReferenceField::$instance->referenceFile->findAll();

		return Craft::$app->getView()->renderTemplate('referencefield/field-dropdown-settings', [
			'field'   => $this,
			'options' => $referenceFiles,
		]);
	}

	public function getInputHtml($value, ElementInterface $element = null): string
	{
		$refVars = [
			'value'        => $value->value                ?? '',
			'elementClass' => get_class( $element->owner ) ?? '',
			'elementId'    => $element->ownerId            ?? ''
		];

		$references  = ReferenceField::$instance->referenceFile->parse( $this->referenceFile, $refVars );
		$options     = ReferenceField::$instance->referenceFile->options( $references );
		$valueActual = $value->value ?? null;
		$error       = false;

		// what to do when we load a reference value that no longer exists in the reference file?
		// probably have to do something in the 
		if( $valueActual && !empty($valueActual) && !in_array( $valueActual, $options->keys()->toArray() ) ) {
			$error = true;		
			$options = $options->toArray();
			array_unshift( $options, [ 'value' => $valueActual, 'label' => '[DEPRECATED]' ] );

		 	// try to find & set a new default value
			// $default = collect( $reference )->whereIn('default', true)->first();
		 	// $value = $default['value'] ?? $reference[0]['value'] ?? '';
		}

		$id = Craft::$app->getView()->formatInputId($this->handle);
		return Craft::$app->getView()->renderTemplate('referencefield/field-dropdown-input', [
			'error' 		=> $error,
			'field' 		=> $this,
			'id' 			=> $id,
			'name' 			=> $this->handle,
			'namespacedId' 	=> Craft::$app->getView()->namespaceInputId($id),
			'options' 		=> $options,
			'references' 	=> $references,
			'value' 		=> $valueActual,
			'element'       => $element
		]);
	}
}
