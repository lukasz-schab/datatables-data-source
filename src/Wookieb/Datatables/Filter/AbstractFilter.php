<?php
namespace Wookieb\Datatables\Filter;

use Assert\Assertion;

/**
 * Abstract basic implementation of filter
 *
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * @var array
     */
    private $columns = array();
    private $value;

    /**
     * {@inheritDoc}
     */
    public function setColumns(array $columns)
    {
        $this->columns = array();
        foreach ($columns as $column) {
            $this->addColumn($column);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * {@inheritDoc}
     */
    public function addColumn($column)
    {
        Assertion::notBlank($column, 'Filter field cannot be blank');
        $this->columns[] = $column;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {
        return $this->value;
    }
}
