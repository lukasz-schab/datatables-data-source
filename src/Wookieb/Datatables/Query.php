<?php
namespace Wookieb\Datatables;

use Wookieb\Datatables\Filter\FilterInterface;
use Assert\Assertion;

/**
 * Datatables query that contains filters, sort order definition, limit and offset params from request
 *
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
class Query
{
    private $limit;
    private $offset;
    private $echo;

    private $sort = array();
    private $filters = array();

    /**
     * Set ECHO param
     *
     * @param integer $echo
     *
     * @return self
     */
    public function setEcho($echo)
    {
        $this->echo = (int)$echo;
        return $this;
    }

    /**
     * Return ECHO param
     *
     * @return integer
     */
    public function getEcho()
    {
        return $this->echo;
    }

    /**
     * @param Filter\FilterInterface $filter
     *
     * @return self
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Set entire list of filters
     *
     * @param array $filters
     *
     * @return self
     */
    public function setFilters(array $filters)
    {
        $this->filters = array();
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param $field
     * @param $order
     *
     * @return Query
     */
    public function addSort($field, $order)
    {
        Assertion::notBlank($field, 'Sort field cannot be empty');
        $newOrder = strtolower($order);
        Assertion::inArray($newOrder, array('asc', 'desc'), 'Unsupported sort direction "'.$order.'"');
        $this->sort[] = array(
            'field' => $field,
            'direction' => $newOrder
        );
        return $this;
    }

    public function setSort(array $sort)
    {
        $this->sort = array();
        foreach ($sort as $field => $order) {
            $this->addSort($field, $order);
        }
        return $this;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setLimit($limit)
    {
        Assertion::nullOrnumeric($limit, 'Limit must be a number');
        if ($limit !== null) {
            $limit = (int)$limit;
            Assertion::min($limit, 1, 'Limit must be greater or equal 1');
        }
        $this->limit = $limit;
        return $this;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setOffset($offset)
    {
        Assertion::nullOrnumeric($offset, 'Offset must be a number');
        if ($offset !== null) {
            $offset = (int)$offset;
            Assertion::min($offset, 0, 'Offset must be greater or equal 0');
        }
        $this->offset = $offset;
        return $this;
    }

    public function getOffset()
    {
        return $this->offset;
    }
}
