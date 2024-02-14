<?php

namespace simplicateca\selectplus\fields;

use craft\fields\data\MultiOptionsFieldData;
use craft\fields\data\SingleOptionFieldData;
use craft\base\conditions\BaseMultiSelectConditionRule;
use craft\fields\conditions\FieldConditionRuleTrait;
use craft\fields\conditions\FieldConditionRuleInterface;

use simplicateca\selectplus\helpers\ConfigHelper;

class SelectPlusConditionRule extends BaseMultiSelectConditionRule implements FieldConditionRuleInterface
{
    use FieldConditionRuleTrait;

    protected function options(): array
    {
        $field = $this->_field;
        $options = ConfigHelper::options( $field->configFile );
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
            $value = array_map( fn(OptionData $option) => $option->value, (array)$value );
        } elseif ($value instanceof SingleOptionFieldData) {
            $value = $value->value;
        }

        return $this->matchValue($value);
    }
}
