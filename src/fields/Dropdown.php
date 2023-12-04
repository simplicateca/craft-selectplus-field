<?php
/**
 * Reference Field - Dropdown
 */

namespace simplicateca\referencefield\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\base\SortableFieldInterface;
use craft\base\Field;
use yii\db\Schema;

use simplicateca\referencefield\ReferenceField;
use simplicateca\referencefield\models\Reference;
use simplicateca\referencefield\fields\conditions\ReferenceFieldConditionRule;

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
        $json = $value && !empty($value) && is_string( $value )
            ? json_decode( $value, true)
            : null;

        $value = $value->value ?? $value['value'] ?? $json['value'] ?? $value;

        return new Reference([
            'value'   => $value,
            'refpath' => $this->referenceFile,
            'element' => $element,
        ]);
	}


    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
		if( !$value || empty($value) ) return null;

        $json = $value && !empty($value) && is_string( $value )
            ? json_decode( $value, true)
            : null;

        return strval( $value->value ?? $value['value'] ?? $json['value'] ?? $value );
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
        return ReferenceFieldConditionRule::class;
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
		$references = ReferenceField::$instance->referenceFile->parse( $value->refpath ?? null, [
            'value'   => $value->value ?? null,
            'element' => $element
        ] );

        $options = ReferenceField::$instance->referenceFile->options( $references );
		$error   = false;

		// what to do when we load a reference value that no longer exists in the reference file?
		// probably have to do something in the
		if( $value->value && !empty($value->value) && !in_array( $value->value, $options->keys()->toArray() ) ) {
			$error = true;
			$options = $options->toArray();
			array_unshift( $options, [ 'value' => $value->value, 'label' => '[DEPRECATED]' ] );

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
			'value' 		=> $value->value,
			'element'       => $element
		]);
	}
}
