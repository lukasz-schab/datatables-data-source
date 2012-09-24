<?php
namespace Wookieb\Datatables\DataSource\PDO;

use Assert\Assertion;

/**
 * Contains plain SQL query and his values to bind
 *
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
class SQLQuery
{
    private $bindValues;
    private $query;

    /**
     * @param string $query sql query
     * @param array $bindValues
     *
     * @throws \InvalidArgumentException when query is blank
     */
    public function __construct($query, array $bindValues = array())
    {
        $this->setQuery($query);
        $this->setBindValues($bindValues);
    }

    private function setQuery($query)
    {
        $query = trim($query);
        Assertion::notBlank($query, 'Query cannot be blank');
        $this->query = $query;
        return $this;
    }

    /**
     * Return query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    private function setBindValues(array $bindValues)
    {
        $this->bindValues = $bindValues;
        return $this;
    }

    /**
     * Return values to bind
     *
     * @return array
     */
    public function getBindValues()
    {
        return $this->bindValues;
    }
}
