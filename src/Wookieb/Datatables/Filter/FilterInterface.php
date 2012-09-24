<?php
namespace Wookieb\Datatables\Filter;

/**
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
interface FilterInterface
{
    /**
     * Set list of filtered columns
     * List must be array of strings
     *
     * @param array $columns
     *
     * @return self
     */
    public function setColumns(array $columns);

    /**
     * Return list of columns
     *
     * @return array
     */
    public function getColumns();

    /**
     * Add column to filter by filter's value
     *
     * @param string $column
     *
     * @return self
     * @throws \InvalidArgumentException when attempt to add blank field name
     */
    public function addColumn($column);

    /**
     * Set filter value (phrase etc)
     *
     * @param mixed $value
     *
     * @return self
     */
    public function setValue($value);

    /**
     * Return filter value
     *
     * @return mixed
     */
    public function getValue();
}
