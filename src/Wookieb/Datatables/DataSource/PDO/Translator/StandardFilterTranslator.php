<?php
namespace Wookieb\Datatables\DataSource\PDO\Translator;

use Wookieb\Datatables\DataSource\Translator\FilterTranslatorInterface;
use Wookieb\Datatables\Filter\FilterInterface;
use Assert\Assertion;
use Wookieb\Datatables\Filter\StandardFilter;

/**
 * Translate StandardFilter to SQL
 *
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
class StandardFilterTranslator implements FilterTranslatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function translate(FilterInterface $filter)
    {
        Assertion::isInstanceOf($filter, 'Wookieb\Datatables\Filter\StandardFilter', 'Invalid filter instance');

        if ($filter->getColumns() === array()) {
            throw new \InvalidArgumentException('List of columns in filter cannot be empty');
        }
        /* @var $filter StandardFilter */
        $placeholderName = $this->getPlaceholderName($filter);
        $predicate = $this->getPredicate($filter, $placeholderName);
        $value = $this->getValueForStatement($filter, $placeholderName);

        $queryFilterParts = array();
        foreach ($filter->getColumns() as $column) {
            $queryFilterParts[] = '`'.$column.'` '.$predicate;
        }
        return array('('.implode(' OR ', $queryFilterParts).')', $value);
    }

    private function getPlaceholderName(StandardFilter $filter)
    {
        $base = ':dsf_'.uniqid();
        if ($filter->getOperator() === StandardFilter::IN_ARRAY) {
            return $this->generatePlaceholderNamesForInStatement($base, count($filter->getValue()));
        }
        return $base;
    }

    private function getPredicate(StandardFilter $filter, $placeholderName)
    {
        $sqlOperators = $this->getSqlOperators();
        $predicate = $sqlOperators[$filter->getOperator()].' ';
        if ($filter->getOperator() === StandardFilter::IN_ARRAY) {
            $predicate .= '('.implode(', ', $placeholderName).')';
        } else {
            $predicate .= $placeholderName;
        }
        return $predicate;
    }

    private function getSqlOperators()
    {
        return array(
            StandardFilter::ENDS_WITH => 'LIKE',
            StandardFilter::CONTAINS => 'LIKE',
            StandardFilter::STARTS_WITH => 'LIKE',
            StandardFilter::GREATER_OR_EQUAL => '>=',
            StandardFilter::GREATER_THAN => '>',
            StandardFilter::LOWER_OR_EQUAL => '<=',
            StandardFilter::LOWER_THAN => '<',
            StandardFilter::NOT_EQUAL => '!=',
            StandardFilter::EQUAL => '=',
            StandardFilter::IN_ARRAY => 'IN',
        );
    }

    private function getValueForStatement(StandardFilter $filter, $placeholderName)
    {
        switch ($filter->getOperator()) {
            case StandardFilter::IN_ARRAY:
                $value = array_combine($placeholderName, $filter->getValue());
                break;
            case StandardFilter::CONTAINS:
                $value = array($placeholderName => '%'.$filter->getValue().'%');
                break;
            case StandardFilter::STARTS_WITH:
                $value = array($placeholderName => $filter->getValue().'%');
                break;
            case StandardFilter::ENDS_WITH:
                $value = array($placeholderName => '%'.$filter->getValue());
                break;
            default:
                $value = array($placeholderName => $filter->getValue());
                break;
        }
        return $value;
    }

    private function generatePlaceholderNamesForInStatement($basePlaceholderName, $length)
    {
        $placeholderNames = array_fill(0, $length, $basePlaceholderName);
        $suffixes = range(1, $length);
        foreach ($placeholderNames as &$name) {
            $name .= current($suffixes);
            next($suffixes);
        }
        return $placeholderNames;
    }
}

