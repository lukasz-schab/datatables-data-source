<?php
namespace Wookieb\Datatables\DataSource\Translator;

use Wookieb\Datatables\Exception\TranslatorNotFoundException;
use Wookieb\Datatables\DataSource\Translator\FilterTranslatorInterface;
use Wookieb\Datatables\Filter\FilterInterface;
use Assert\Assertion;

/**
 * Container for filter translators
 *
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
class Translator implements FilterTranslatorInterface
{
    private $translators = array();

    /**
     * Register filter translator used to translate filters which are instanceof $filterClass
     *
     * @param string $filterClass
     * @param FilterTranslatorInterface $translator
     *
     * @return self
     */
    public function registerTranslatorForFilterClass($filterClass, FilterTranslatorInterface $translator)
    {
        Assertion::notBlank($filterClass, 'Filter class cannot be empty');
        $this->translators[$filterClass] = $translator;
        return $this;
    }

    /**
     * @param string $filterClass
     *
     * @return FilterTranslatorInterface
     *
     * @throws TranslatorNotFoundException
     */
    private function getTranslatorForFilter(FilterInterface $filter)
    {
        foreach ($this->translators as $filterClass => $translator) {
            if ($filter instanceof $filterClass) {
                return $translator;
            }
        }
        throw new TranslatorNotFoundException('Cannot find translator for filter class "'.get_class($filter).'"');
    }

    /**
     * Translate filter (using filter translator registered for filter class) to value understandable for datasource
     *
     * @param FilterInterface $filter
     *
     * @return mixed
     * @throws TranslatorNotFoundException when there is not translator registered for given filter
     */
    public function translate(FilterInterface $filter)
    {
        return $this->getTranslatorForFilter($filter)->translate($filter);
    }
}
