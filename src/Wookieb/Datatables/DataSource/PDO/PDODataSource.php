<?php
namespace Wookieb\Datatables\DataSource\PDO;

use Wookieb\Datatables\Query;
use Wookieb\Datatables\DataSource\PDO\SQLQuery;
use Wookieb\Datatables\Results;
use Wookieb\Datatables\DataSource\Translator\Translator;
use Wookieb\Datatables\DataSource\Translator\TranslatorAwareInterface;
use Wookieb\Datatables\DataSource\DataSourceInterface;
use Wookieb\Datatables\Exception\InvalidDataSourceException;
use Wookieb\Datatables\DataSource\PDO\Translator\StandardFilterTranslator;

/**
 * Datasource that are able to fetch results from databases supported by PDO
 *
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
class PDODataSource implements DataSourceInterface, TranslatorAwareInterface
{
    /**
     * @var SQLQuery
     */
    private $countQuery;
    /**
     * @var SQLQuery
     */
    private $mainQuery;
    /**
     * @var \PDO
     */
    private $pdo;
    /**
     * @var Translator
     */
    private $translator;

    public function __construct()
    {
        $this->translator = new Translator();
        $translator = new StandardFilterTranslator();
        $this->translator->registerTranslatorForFilterClass('Wookieb\Datatables\Filter\StandardFilter', $translator);
    }

    /**
     * Set connection
     *
     * @param \PDO $pdo
     *
     * @return self
     */
    public function setConnection(\PDO $pdo)
    {
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * Return PDO connection instance
     *
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * Set count query that return amount of records in datatable
     * If query return one value then it will be treated as amount of record what we are looking for.
     * If query returns more than one column then our "amount of records" value should be named "total_records"
     *
     * @param string|SQLQuery $countQuery
     *
     * @throws \InvalidArgumentException when query does not contain [where] token
     * @return self
     */
    public function setCountQuery($countQuery)
    {
        if ((!$countQuery instanceof SQLQuery)) {
            $countQuery = new SQLQuery($countQuery);
        }
        $this->countQuery = $countQuery;
        return $this;
    }

    /**
     * @return SQLQuery
     */
    public function getCountQuery()
    {
        return $this->countQuery;
    }

    /**
     * Set main query that fetch results for datatable
     *
     * @param string|SQLQuery $mainQuery
     *
     * @return PDODataSource
     */
    public function setMainQuery($mainQuery)
    {
        if (!($mainQuery instanceof SQLQuery)) {
            $mainQuery = new SQLQuery($mainQuery);
        }
        $this->mainQuery = $mainQuery;
        return $this;
    }

    /**
     * @return SQLQuery
     */
    public function getMainQuery()
    {
        return $this->mainQuery;
    }

    /**
     * {@inheritDoc}
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * {@inheritDoc}
     */
    public function getResults(Query $query)
    {
        $this->validDataSourceInstance();

        $translatedFilters = $this->translateFilters($query->getFilters());

        $results = new Results();
        $results->setData($this->fetchResults($query, $translatedFilters));
        $results->setTotalRecords($this->fetchTotalRecords());
        $results->setEcho($query->getEcho());

        if (empty($translatedFilters)) {
            $results->setTotalDisplayRecords($results->getTotalRecords());
        } else {
            $results->setTotalDisplayRecords($this->fetchTotalFilteredRecords($translatedFilters));
        }

        return $results;
    }

    /**
     * Check whether data source instance is valid
     *
     * @throws InvalidDataSourceException
     */
    public function validDataSourceInstance()
    {
        if (!$this->pdo) {
            throw new InvalidDataSourceException('Cannot fetch results without connection to database');
        }
        if (!$this->translator) {
            throw new InvalidDataSourceException('Cannot fetch results without translator instance');
        }
        if (!$this->mainQuery) {
            throw new InvalidDataSourceException('Cannot fetch results without main sql query');
        }
        if (!$this->countQuery) {
            throw new InvalidDataSourceException('Cannot fetch results without count sql query');
        }
    }

    protected function fetchResults(Query $query, $filters)
    {
        list ($query, $bindValues) = $this->buildResultsQuery($query, $filters);
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($bindValues);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function buildResultsQuery(Query $query, $filters)
    {
        $mainQuery = clone $this->mainQuery;

        $filtersString = '';
        $filtersBindValues = array();
        if ($filters !== null) {
            $filtersString = $filters[0];
            $filtersBindValues = $filters[1];
        }

        $manipulator = new QueryManipulator($mainQuery->getQuery());
        $manipulator->setFilterConditions($filtersString);

        $sort = $this->translateSortingClause($query->getSort());
        $manipulator->setOrderBy($sort);

        $manipulator->setLimit($query->getLimit());
        $manipulator->setOffset($query->getOffset());

        $query = $manipulator->getModifiedQuery();
        $bindValues = array_merge($mainQuery->getBindValues(), $filtersBindValues);

        return array($query, $bindValues);
    }

    private function translateSortingClause(array $sort)
    {
        $parts = array();
        foreach ($sort as $field) {
            $parts[] = '`'.$field['field'].'` '.$field['direction'];
        }
        return implode(', ', $parts);
    }

    private function translateFilters(array $filters)
    {
        if ($filters === array()) {
            return null;
        }
        $sqlParts = array();
        $bindValues = array();
        foreach ($filters as $filter) {
            $translateResult = $this->translator->translate($filter);
            if (!is_array($translateResult) || count($translateResult) !== 2) {
                $msg = 'Translated filters should be an array with two elements (query, bind values) or null';
                throw new \UnexpectedValueException($msg);
            }
            $sqlParts[] = $translateResult[0];
            $bindValues = array_merge($bindValues, $translateResult[1]);
        }
        return array(implode(' AND ', $sqlParts), $bindValues);
    }

    private function fetchTotalRecords()
    {
        list($query, $bindValues) = $this->buildCountTotalRecordsQuery();
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($bindValues);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!is_array($result)) {
            throw new \UnexpectedValueException('Unexpected value of count query - it must return count result');
        }
        return current($result);
    }

    private function buildCountTotalRecordsQuery()
    {
        $countQuery = clone $this->countQuery;
        $manipulator = new QueryManipulator($countQuery->getQuery());
        $manipulator->setFilterConditions(null)
            ->setOffset(null)
            ->setLimit(null)
            ->setOrderBy(null);

        return array($manipulator->getModifiedQuery(), $countQuery->getBindValues());
    }

    private function fetchTotalFilteredRecords($filters)
    {
        list($query, $bindValues) = $this->buildCountTotalFilteredRecords($filters);
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($bindValues);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!is_array($result)) {
            throw new \UnexpectedValueException('Unexpected value of count query - it must return count result');
        }
        return current($result);
    }

    private function buildCountTotalFilteredRecords($filters)
    {
        $filtersQuery = '';
        $bindValues = array();
        if ($filters !== null) {
            $filtersQuery = $filters[0];
            $bindValues = $filters[1];
        }
        $countQuery = clone $this->countQuery;
        $manipulator = new QueryManipulator($countQuery->getQuery());
        $manipulator->setFilterConditions($filtersQuery)
            ->setLimit(null)
            ->setOffset(null)
            ->setOrderBy(null);

        $bindValues = array_merge($countQuery->getBindValues(), $bindValues);
        return array($manipulator->getModifiedQuery(), $bindValues);
    }
}
