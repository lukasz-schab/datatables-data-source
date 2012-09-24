<?php
namespace Wookieb\Datatables;

use Wookieb\Datatables\DataSource\DataSourceInterface;

/**
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
class DataTableDataSource
{
    /**
     * @var DataSourceInterface
     */
    private $datasource;
    private $columns = array();
    private $queryBuilder;

    public function __construct(DataSourceInterface $datasource, array $columns = array())
    {
        $this->queryBuilder = new QueryBuilder();
        $this->setDataSource($datasource);
        $this->setColumns($columns);
    }

    private function setDataSource(DataSourceInterface $datasource)
    {
        $datasource->validDataSourceInstance();
        $this->datasource = $datasource;
    }

    private function setColumns(array $columns)
    {
        $newColumns = array_filter($columns, 'is_string');
        if ($newColumns != $columns) {
            throw new \InvalidArgumentException('Columns list should be array of strings');
        }
        $this->columns = $columns;
    }

    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Build query from given request params (for example GET, POST)
     *
     * @param array $request
     *
     * @return Query
     */
    public function buildQueryFromRequest(array $request)
    {
        return $this->queryBuilder->build($request, $this->columns);
    }


    /**
     * Fetch results from datasource
     *
     * @param Query $query
     *
     * @return Results
     */
    public function fetchResults(Query $query)
    {
        return $this->datasource->getResults($query);
    }

    public function getResultsAsJson(array $request)
    {
        $query = $this->buildQueryFromRequest($request);
        $results = $this->fetchResults($query);
        return json_encode(
            array(
                'sEcho' => $results->getEcho(),
                'iTotalRecords' => $results->getTotalRecords(),
                'iTotalDisplayRecords' => $results->getTotalDisplayRecords(),
                'aaData' => $results->getData()
            )
        );
    }
}
