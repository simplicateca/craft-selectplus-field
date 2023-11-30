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
	public object $element;

    public function __construct(array $ref)
    {
		$this->value = $ref['value'] ?? '';
        if( $ref['refpath'] ) $this->refpath = $ref['refpath'];
		if( $ref['element'] ) $this->element = $ref['element'];
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
        $references = collect(
            ReferenceField::$instance->referenceFile->parse(
			    $this->refpath ?? null,
			    [ 'value'   => $this->value	  ?? null,
				  'element' => $this->element ?? null ]
		    )
        );

		// we want to try our best to return *something* here.
		// so first we look for the matching value, then the first default field
		// we default to the first field in the reference file
		$selected = $references->whereIn('value', $this->value ?? null)->first()
					  ?? $references->whereIn('default', true)->first()
					  ?? $references->first();

		return $selected[$field] ?? $selected ?? [];
	}
}
