<?php
namespace Wookieb\Datatables\DataSource;

use Wookieb\Datatables\Query;
use Wookieb\Datatables\Exception\InvalidDataSourceException;

/**
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
interface DataSourceInterface
{
    /**
     * Return results for given query
     *
     * @param Query $query
     *
     * @return mixed
     */
    public function getResults(Query $query);

    /**
     * Check whether data source instance is valid
     *
     * @throws InvalidDataSourceException
     */
    public function validDataSourceInstance();
}
