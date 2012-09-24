<?php
namespace Wookieb\Tests\Datatables\DataSource\PDO;
use Wookieb\Datatables\DataSource\PDO\SQLQuery;

/**
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
class SqlQueryTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldStoreQueryAndBindValuesProvidedInConstructor()
    {
        $query = 'SELECT * FROM table';
        $bindValues = range(1, 5);
        $object = new SQLQuery($query, $bindValues);
        $this->assertSame($query, $object->getQuery(), 'contains invalid query');
        $this->assertSame($bindValues, $object->getBindValues(), 'contains invalid bind values');
    }

    public function testShouldThrowExceptionWhenEmptyQueryProvided() {
        $this->setExpectedException('\InvalidArgumentException', 'Query cannot be blank');
        new SQLQuery('', array());
    }
}
