<?php
namespace Wookieb\Datatables\DataSource\Translator;

use Wookieb\Datatables\Filter\FilterInterface;

/**
 * Interface for filter translators that converts filter object to value understandable for specific datasource
 *
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
interface FilterTranslatorInterface
{
    /**
     * Translate given filter to value that can be understand by specific datasource
     *
     * @param FilterInterface $filter
     *
     * @return mixed
     */
    public function translate(FilterInterface $filter);
}
