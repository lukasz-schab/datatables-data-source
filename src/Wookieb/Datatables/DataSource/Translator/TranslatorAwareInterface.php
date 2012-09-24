<?php
namespace Wookieb\Datatables\DataSource\Translator;

/**
 * Interface that describes way to inject filter translator in datasources
 *
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
interface TranslatorAwareInterface
{
    /**
     * Set translator object
     *
     * @param Translator $translator
     */
    public function setTranslator(Translator $translator);

    /**
     * Return translator object
     *
     * @return Translator
     */
    public function getTranslator();
}
