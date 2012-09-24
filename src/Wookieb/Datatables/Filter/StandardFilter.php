<?php
namespace Wookieb\Datatables\Filter;

use Assert\Assertion;

/**
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
class StandardFilter extends AbstractFilter
{
    private $operator = '=';

    const EQUAL = '=';
    const NOT_EQUAL = '!=';
    const LOWER_THAN = '<';
    const LOWER_OR_EQUAL = '<=';
    const GREATER_THAN = '>';
    const GREATER_OR_EQUAL = '>=';
    const IN_ARRAY = 'IN';
    const STARTS_WITH = 'STARTS_WITH';
    const ENDS_WITH = 'ENDS_WITH';
    const CONTAINS = 'CONTAINS';

    /**
     * Set filter operator
     *
     * @param string $operator
     *
     * @throws \InvalidArgumentException
     */
    public function setOperator($operator)
    {
        Assertion::inArray($operator, $this->getAvailableOperators(), 'Unsupported operator "'.$operator.'"');
        $this->operator = $operator;
        return $this;
    }

    /**
     * Return list of available operators
     *
     * @return array
     */
    protected function getAvailableOperators()
    {
        $reflection = new \ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }

    /**
     * {@inheritDoc}
     */
    public function getOperator()
    {
        return $this->operator;
    }

    public function __toString()
    {
        return '('.(implode(', ', $this->getColumns()) ? : '[empty]').') '.$this->operator.' "'.$this->getValue().'"';
    }
}
