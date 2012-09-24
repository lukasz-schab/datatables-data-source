<?php
namespace Wookieb\Datatables;

use Wookieb\Datatables\Filter\StandardFilter;
use Assert\Assertion;

/**
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
class QueryBuilder
{
    private $request = array();
    /**
     * @var Query
     */
    private $query;
    private $columns = array();
    private $buildCallbacks = array();

    public function build(array $request, array $columns)
    {
        $this->query = new Query();
        $this->request = $request;
        $this->columns = $columns;

        $this->setEcho();
        $this->setLimitAndOffset();
        $this->setSort();
        $this->setSearch();
        $this->setIndividualColumnFilter();

        $this->triggerBuildCallbacks();

        $query = $this->query;
        $this->query = null;
        $this->request = array();
        $this->columns = array();
        return $query;
    }

    public function addBuildCallback($callback)
    {
        if (!is_callable($callback, true)) {
            throw new \InvalidArgumentException('Invalid callback');
        }
        $this->buildCallbacks[] = $callback;
        return $this;
    }

    private function setEcho()
    {
        $this->query->setEcho($this->getParam('sEcho'));
    }

    private function setLimitAndOffset()
    {
        $offset = $this->getParam('iDisplayStart');
        $limit = $this->getParam('iDisplayLength');
        if ($limit !== -1) {
            if ($offset) {
                $this->query->setOffset($offset);
            }
            if ($limit) {
                $this->query->setLimit($limit);
            }
        }
    }

    private function getParam($key, $default = null)
    {
        if (isset($this->request[$key]) && is_scalar($this->request[$key])) {
            return $this->request[$key];
        }
        return $default;
    }

    private function setSort()
    {
        $amountOfSortColumns = (int)$this->getParam('iSortingCols', 0);
        for ($i = 0; $i < $amountOfSortColumns; $i++) {
            $columnIndex = (int)$this->getParam('iSortCol_'.$i, 'false');
            if ($columnIndex >= 0 && $this->getParam('bSortable_'.$columnIndex) === 'true') {
                try {
                    $column = $this->getColumnAtPosition($columnIndex);
                    $direction = strtolower($this->getParam('sSortDir_'.$i, ''));
                    if ($direction === 'asc' || $direction === 'desc') {
                        $this->query->addSort($column, $direction);
                    }
                } catch (\OutOfBoundsException $e) {
                }
            }
        }
    }

    private function setSearch()
    {
        $phrase = $this->getParam('sSearch');
        if ($phrase) {
            $filter = new StandardFilter();
            $filter->setColumns($this->columns);
            $filter->setOperator(StandardFilter::CONTAINS);
            $filter->setValue($phrase);
            $this->query->addFilter($filter);
        }
    }

    private function setIndividualColumnFilter()
    {
        foreach ($this->columns as $index => $column) {
            $isSearchable = $this->getParam('bSearchable_'.$index) === 'true';
            $phrase = $this->getParam('sSearch_'.$index);
            if ($isSearchable && $phrase) {
                $filter = new StandardFilter();
                $filter->setColumns(array($column));
                $filter->setOperator(StandardFilter::CONTAINS);
                $filter->setValue($phrase);
                $this->query->addFilter($filter);
            }
        }
    }

    private function getColumnAtPosition($index)
    {
        if (!isset($this->columns[$index])) {
            throw new \OutOfBoundsException('There is no column at position '.$index);
        }
        return $this->columns[$index];
    }

    private function triggerBuildCallbacks()
    {
        foreach ($this->buildCallbacks as $callback) {
            call_user_func($callback, $this->query, $this->request, $this->columns);
        }
    }
}
