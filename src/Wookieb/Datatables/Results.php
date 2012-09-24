<?php
namespace Wookieb\Datatables;

/**
 * Class that represent results from data source
 *
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
class Results
{
    private $totalRecords = 0;
    private $totalDisplayRecords = 0;
    private $echo;
    private $data = array();

    /**
     * Set "echo" value
     *
     * @param string $echo
     *
     * @return self
     */
    public function setEcho($echo)
    {
        $this->echo = (int)$echo;
        return $this;
    }

    /**
     * Return "echo" value
     *
     * @return string
     */
    public function getEcho()
    {
        return $this->echo;
    }

    /**
     * Set amount of display records
     *
     * @param integer $totalDisplayRecords
     *
     * @return self
     */
    public function setTotalDisplayRecords($totalDisplayRecords)
    {
        $this->totalDisplayRecords = $totalDisplayRecords;
        return $this;
    }

    /**
     * Return amount of display records
     *
     * @return integer
     */
    public function getTotalDisplayRecords()
    {
        return $this->totalDisplayRecords;
    }

    /**
     * Set amount of total records
     *
     * @param integer $totalRecords
     *
     * @return self
     */
    public function setTotalRecords($totalRecords)
    {
        $this->totalRecords = $totalRecords;
        return $this;
    }

    /**
     * Return amount of total records
     *
     * @return integer
     */
    public function getTotalRecords()
    {
        return $this->totalRecords;
    }

    /**
     * Set list of results
     *
     * @param array $results
     *
     * @return self
     */
    public function setData(array $results)
    {
        $this->data = array();
        foreach ($results as $result) {
            $this->addRecordToData($result);
        }
        return $this;
    }

    /**
     * Return list of results
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Add single record to results list
     *
     * @param array $record
     *
     * @return self
     */
    public function addRecordToData(array $record)
    {
        $this->data[] = $record;
        return $this;
    }
}
