<?php
namespace Wookieb\Datatables\DataSource\PDO;

use Assert\Assertion;

/**
 * Query manipulator which replace tokens like [OFFSET], [WHERE] etc with provided values
 *
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
class QueryManipulator
{
    private $modifiedQuery;

    /**
     * @param string $query query to modify
     */
    public function __construct($query)
    {
        $query = trim($query);
        Assertion::notBlank($query, 'Query cannot be blank');
        $this->modifiedQuery = $query;
    }

    /**
     * Replace [where] tokens with given $conditions string.
     * Consider the following replace rules:
     * [where] is replaced by "WHERE $conditions"
     * [where:] is replaced by "$conditions AND"
     * [:where] is replaced by ",$conditions"
     *
     * When $conditions is empty then all [where] tokens are just removed.
     * All tokens are case insensitive.
     *
     * @param string $conditions
     *
     * @return self
     */
    public function setFilterConditions($conditions)
    {
        if (!$this->hasTokenWithColonIndicator($this->modifiedQuery, 'where') && $conditions) {
            $this->modifiedQuery .= ' [where]';
        }

        $find = array('[where]', '[:where]', '[where:]');
        $replace = array(
            $conditions ? 'WHERE '.$conditions : '',
            $conditions ? 'AND '.$conditions : '',
            $conditions ? $conditions.' AND' : '');
        $this->modifiedQuery = str_ireplace($find, $replace, $this->modifiedQuery);
        return $this;
    }

    private function hasTokenWithColonIndicator($string, $tokenName)
    {
        return (bool)preg_match('/\[\:?'.$tokenName.'\:?\]/i', $string);
    }

    /**
     * Replace [order_by] tokens with given $orderBy string.
     * Consider the following replace rules:
     * [order_by] is replaced by "ORDER BY $orderBy"
     * [order_by:] is replaced by "$orderBy,"
     * [:order_by] is replaced by ",$orderBy"
     *
     * When $orderBy is empty then all [order_by] tokens are just removed.
     * All tokens are case insensitive.
     *
     * @param string $orderBy
     *
     * @return self
     */
    public function setOrderBy($orderBy)
    {
        if (!$this->hasTokenWithColonIndicator($this->modifiedQuery, 'order_by') && $orderBy) {
            $this->modifiedQuery .= ' [order_by]';
        }

        $find = array('[order_by]', '[:order_by]', '[order_by:]');
        $replace = array(
                $orderBy ? 'ORDER BY '.$orderBy : '',
                $orderBy ? ','.$orderBy : '',
                $orderBy ? $orderBy.',' : '');
        $this->modifiedQuery = str_ireplace($find, $replace, $this->modifiedQuery);
        return $this;
    }

    /**
     * Replace [limit] tokens with "LIMIT $limit" string.
     * When $limit is empty or null then all [limit] tokens are just removed.
     *
     * All tokens are case insensitive.
     *
     * @param integer $limit
     *
     * @return self
     */
    public function setLimit($limit)
    {
        if ($limit === '') {
            $limit = null;
        }
        Assertion::nullOrnumeric($limit, 'limit must be null or number');
        if (!$this->hasToken($this->modifiedQuery, 'limit') && $limit) {
            $this->modifiedQuery .= ' [limit]';
        }
        $this->modifiedQuery = str_ireplace('[limit]', $limit ? 'LIMIT '.$limit : '', $this->modifiedQuery);
        return $this;
    }

    private function hasToken($string, $tokenName)
    {
        $token = '['.$tokenName.']';
        return stripos($string, $token) !== false;
    }

    /**
     * Replace [offset] tokens with "OFFSET $offset" string.
     * When $offset is empty or null then all [offset] tokens are just removed.
     *
     * All tokens are case insensitive.
     *
     * @param integer $offset
     *
     * @return self
     */
    public function setOffset($offset)
    {
        if ($offset === '') {
            $offset = null;
        }
        Assertion::nullOrnumeric($offset, 'offset must be null or number');
        if (!$this->hasToken($this->modifiedQuery, 'offset') && $offset) {
            $this->modifiedQuery .= ' [offset]';
        }
        $this->modifiedQuery = str_ireplace('[offset]', $offset ? 'OFFSET '.$offset : '', $this->modifiedQuery);
        return $this;
    }

    /**
     * Return modified query
     *
     * @return string
     */
    public function getModifiedQuery()
    {
        return $this->modifiedQuery;
    }
}
