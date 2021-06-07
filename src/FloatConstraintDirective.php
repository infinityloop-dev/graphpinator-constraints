<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

final class FloatConstraintDirective extends \Graphpinator\Typesystem\Directive implements
    \Graphpinator\Typesystem\Location\FieldDefinitionLocation,
    \Graphpinator\Typesystem\Location\ArgumentDefinitionLocation,
    \Graphpinator\Typesystem\Location\VariableDefinitionLocation
{
    use TScalarConstraint;

    protected const NAME = 'floatConstraint';
    protected const DESCRIPTION = 'Graphpinator floatConstraint directive.';

    public function validateFieldUsage(
        \Graphpinator\Typesystem\Field\Field $field,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $field->getType()->getNamedType() instanceof \Graphpinator\Typesystem\Spec\FloatType;
    }

    public function validateArgumentUsage(
        \Graphpinator\Typesystem\Argument\Argument $argument,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $argument->getType()->getNamedType() instanceof \Graphpinator\Typesystem\Spec\FloatType;
    }

    public function validateVariableUsage(
        \Graphpinator\Normalizer\Variable\Variable $variable,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $variable->getType()->getNamedType() instanceof \Graphpinator\Typesystem\Spec\FloatType;
    }

    protected function getFieldDefinition() : \Graphpinator\Typesystem\Argument\ArgumentSet
    {
        return new \Graphpinator\Typesystem\Argument\ArgumentSet([
            \Graphpinator\Typesystem\Argument\Argument::create('min', \Graphpinator\Typesystem\Container::Float()),
            \Graphpinator\Typesystem\Argument\Argument::create('max', \Graphpinator\Typesystem\Container::Float()),
            \Graphpinator\Typesystem\Argument\Argument::create('oneOf', \Graphpinator\Typesystem\Container::Float()->notNull()->list()),
        ]);
    }

    protected function afterGetFieldDefinition() : void
    {
        $this->arguments['oneOf']->addDirective(
            $this->constraintDirectiveAccessor->getList(),
            ['minItems' => 1],
        );
    }

    protected function specificValidateValue(
        \Graphpinator\Value\Value $value,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void
    {
        $rawValue = $value->getRawValue();
        $min = $arguments->offsetGet('min')->getValue()->getRawValue();
        $max = $arguments->offsetGet('max')->getValue()->getRawValue();
        $oneOf = $arguments->offsetGet('oneOf')->getValue()->getRawValue();

        if (\is_float($min) && $rawValue < $min) {
            throw new Exception\MinConstraintNotSatisfied();
        }

        if (\is_float($max) && $rawValue > $max) {
            throw new Exception\MaxConstraintNotSatisfied();
        }

        if (\is_array($oneOf) && !\in_array($rawValue, $oneOf, true)) {
            throw new Exception\OneOfConstraintNotSatisfied();
        }
    }

    protected function specificValidateVariance(
        \Graphpinator\Value\ArgumentValueSet $biggerSet,
        \Graphpinator\Value\ArgumentValueSet $smallerSet,
    ) : void
    {
        $lhs = $biggerSet->getValuesForResolver();
        $rhs = $smallerSet->getValuesForResolver();

        if (\is_float($lhs['min']) && ($rhs['min'] === null || $rhs['min'] < $lhs['min'])) {
            throw new \Exception();
        }

        if (\is_float($lhs['max']) && ($rhs['max'] === null || $rhs['max'] > $lhs['max'])) {
            throw new \Exception();
        }

        if (\is_array($lhs['oneOf']) && ($rhs['oneOf'] === null || !self::varianceValidateOneOf($lhs['oneOf'], $rhs['oneOf']))) {
            throw new \Exception();
        }
    }
}
