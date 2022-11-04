<?php
/**
 * ReferenceField - Store the serialized field data (selected value + reference JSON path)
 */

namespace simplicateca\referencefield\models;

use Craft;
use craft\base\Model;
use simplicateca\referencefield\ReferenceField;

class Reference extends Model
{
	public string $refpath;
	public string $value;
	public string $elementClass;
	public string $elementId;

    public function __construct(array $ref)
    {
        $this->refpath      = $ref['refpath'] ?? '';
		$this->value        = $ref['value']   ?? '';
		$this->elementClass = $ref['elementClass'] ?? '';
		$this->elementId    = $ref['elementId'] ?? '';
    }

	public function __toString(): string
	{
		return $this->value ?? '';
	}


	/**
	 * Returns the entire JSON config or a single field from the config
	 *
	 * @return object|null Either return the value from the config or null
	 */
	public function reference( $field = null ): mixed
	{
		$references = ReferenceField::$instance->referenceFile->parse( $this->refpath ?? null );
		$references = collect( $references );

		// we don't want to return nothing here.
		// so first we look for the matching value, then the first default field
		// we default to the first field in the reference file
		$selected = $references->whereIn('value', $this->value ?? null)->first()
					  ?? $references->whereIn('default', true)->first()
					  ?? $references->first();

		return $selected[$field] ?? $selected ?? [];
	}
}