<?php

namespace simplicateca\referencefield\fields\conditions;

use craft\base\conditions\BaseMultiSelectConditionRule;
use craft\fields\conditions\FieldConditionRuleInterface;
use craft\fields\conditions\FieldConditionRuleTrait;
use craft\fields\BaseOptionsField;
use simplicateca\referencefield\ReferenceField;
use craft\fields\data\MultiOptionsFieldData;
use craft\fields\data\OptionData;
use craft\fields\data\SingleOptionFieldData;

/**
 * Options field condition rule.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 4.0.0
 */
class ReferenceFieldConditionRule extends BaseMultiSelectConditionRule implements FieldConditionRuleInterface
{
    use FieldConditionRuleTrait;

    protected function options(): array
    {
        /** @var BaseOptionsField $field */
        $field = $this->_field;
        $options = ReferenceField::$instance->referenceFile->options( $field->referenceFile );
        return $options->all() ?? [];
    }


    /**
     * @inheritdoc
     */
    protected function elementQueryParam(): ?array
    {
        return $this->paramValue();
    }


    /**
     * @inheritdoc
     */
    protected function matchFieldValue($value): bool
    {
        if ($value instanceof MultiOptionsFieldData) {
            /** @phpstan-ignore-next-line */
            $value = array_map(fn(OptionData $option) => $option->value, (array)$value);
        } elseif ($value instanceof SingleOptionFieldData) {
            $value = $value->value;
        }

        return $this->matchValue($value);
    }
}
