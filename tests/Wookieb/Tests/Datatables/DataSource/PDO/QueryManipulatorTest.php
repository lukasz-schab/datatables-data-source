<?php
namespace Wookieb\Tests\Datatables\DataSource\PDO;

use Wookieb\Datatables\DataSource\PDO\QueryManipulator;

/**
 * @author Łukasz Kużyński "wookieb" <lukasz.kuzynski@gmail.com>
 */
class QueryManipulatorTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldAcceptQueryStringFromConstructor()
    {
        $query = 'SELECT * FROM table';
        $object = new QueryManipulator($query);
        $this->assertSame($query, $object->getModifiedQuery());
    }

    public function testShouldThrowExceptionWhenEmptyQueryProvided()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Query cannot be blank');
        new QueryManipulator(' ');
    }

    public function whereTokenTestCases()
    {
        $query = 'SELECT * FROM table';
        $filter = '(field = 1 AND field2 >= :test)';
        return array(
            array($query.' [where]', $filter, $query.' WHERE '.$filter, 'plain [where] token'),
            array($query.' [where]', '', $query.' ', 'empty filter string'),
            array($query, $filter, $query.' WHERE '.$filter, 'append filter at the end of query'),
            array($query.' [WheRe]', $filter, $query.' WHERE '.$filter, 'plain case insensitive [where] token'),
            array(
                $query.' [where] UNION ALL '.$query.' WHERE some_field >= 4 [:where] UNION ALL '.
                    $query.' WHERE [where:] some_field < 2',
                $filter,
                $query.' WHERE '.$filter.' UNION ALL '.$query.' WHERE some_field >= 4 AND '.$filter.' UNION ALL '.
                    $query.' WHERE '.$filter.' AND some_field < 2',
                'multiple [WHERE] tokens with and without "append and" side indicator'
            ),
        );
    }

    /**
     * @testdox Should replace [WHERE] (prefix and suffix care) token with given string
     * @depends testShouldAcceptQueryStringFromConstructor
     * @dataProvider whereTokenTestCases
     */
    public function testShouldReplaceWHERETokenWithGivenFilterString($query, $filter, $resultQuery, $errorMessage)
    {
        $object = new QueryManipulator($query);
        $result = $object->setFilterConditions($filter);
        $this->assertSame($object, $result, '"setFilterConditions" method violates method chaining');
        $this->assertSame($resultQuery, $object->getModifiedQuery(), 'Does not handle '.$errorMessage);
    }

    public function orderByTokenTestCases()
    {
        $query = 'SELECT * FROM table';
        $orderBy = 'field ASC, field2 DESC';
        return array(
            array($query.' [order_by]', $orderBy, $query.' ORDER BY '.$orderBy, 'plain [order_by] token'),
            array($query.' [order_by]', '', $query.' ', 'empty order by string'),
            array($query, $orderBy, $query.' ORDER BY '.$orderBy, 'append order by at the end of query'),
            array(
                $query.' [ORDER_BY]',
                $orderBy,
                $query.' ORDER BY '.$orderBy,
                'plain case insensitive [order_by] token'
            ),
            array(
                $query.' [ORDER_BY] UNION ALL '.$query.' ORDER BY some ASC [:order_by] UNION ALL '.
                    $query.' ORDER BY [order_by:] some2 DESC',
                $orderBy,
                $query.' ORDER BY '.$orderBy.' UNION ALL '.$query.' ORDER BY some ASC ,'.$orderBy.' UNION ALL '.
                    $query.' ORDER BY '.$orderBy.', some2 DESC',
                'multiple [ORDER_BY] tokens with and without "append comma" side indicator'
            ),
        );
    }

    /**
     * @testdox Should replace [ORDER_BY] (prefix and suffix care) token with given string
     * @depends testShouldAcceptQueryStringFromConstructor
     * @dataProvider orderByTokenTestCases
     */
    public function testShouldReplaceORDER_BYTokenWithGivenOrderByString($query, $orderBy, $resultQuery, $errorMessage)
    {
        $object = new QueryManipulator($query);
        $result = $object->setOrderBy($orderBy);
        $this->assertSame($object, $result, '"setOrderBy" method violates method chaining');
        $this->assertSame($resultQuery, $object->getModifiedQuery(), 'Does not handle '.$errorMessage);
    }

    public function limitTokenTestCases()
    {
        $query = 'SELECT * FROM table';
        $limit = 5;
        return array(
            array($query.' [limit]', $limit, $query.' LIMIT '.$limit, 'plain [limit] token'),
            array($query.' [limit]', '', $query.' ', 'empty order by string'),
            array($query, $limit, $query.' LIMIT '.$limit, 'append limit at the end of query'),
            array($query.' [LIMIT]', $limit, $query.' LIMIT '.$limit, 'plain case insensitive [limit] token'),
            array(
                $query.' [limit] UNION ALL '.$query.' [limit] UNION ALL '.$query.' [limit]',
                $limit,
                $query.' LIMIT '.$limit.' UNION ALL '.$query.' LIMIT '.$limit.' UNION ALL '.$query.' LIMIT '.$limit,
                'multiple [limit] tokens'
            ),
        );
    }

    /**
     * @testdox Should replace [LIMIT] token with given numeric value
     * @depends testShouldAcceptQueryStringFromConstructor
     * @dataProvider limitTokenTestCases
     */
    public function testShouldReplaceLIMITTokenWithGivenLimitValue($query, $limit, $resultQuery, $errorMessage)
    {
        $object = new QueryManipulator($query);
        $result = $object->setLimit($limit);
        $this->assertSame($object, $result, '"setLimit" method violates method chaining');
        $this->assertSame($resultQuery, $object->getModifiedQuery(), 'Does not handle '.$errorMessage);
    }

    public function offsetTokenTestCases()
    {
        $query = 'SELECT * FROM table';
        $offset = 5;
        return array(
            array($query.' [offset]', $offset, $query.' OFFSET '.$offset, 'plain [offset] token'),
            array($query.' [offset]', '', $query.' ', 'empty order by string'),
            array($query, $offset, $query.' OFFSET '.$offset, 'append offset at the end of query'),
            array($query.' [OFFSET]', $offset, $query.' OFFSET '.$offset, 'plain case insensitive [offset] token'),
            array(
                $query.' [offset] UNION ALL '.$query.' [offset] UNION ALL '.$query.' [offset]',
                $offset,
                $query.' OFFSET '.$offset.' UNION ALL '.$query.' OFFSET '.$offset.' UNION ALL '.$query.' OFFSET '.$offset,
                'multiple [offset] tokens'
            ),
        );
    }

    /**
     * @testdox Should replace [OFFSET] token with given numeric value
     * @depends testShouldAcceptQueryStringFromConstructor
     * @dataProvider offsetTokenTestCases
     */
    public function testShouldReplaceOFFSETTokenWithGivenLimitValue($query, $offset, $resultQuery, $errorMessage)
    {
        $object = new QueryManipulator($query);
        $result = $object->setOffset($offset);
        $this->assertSame($object, $result, '"setOffset" method violates method chaining');
        $this->assertSame($resultQuery, $object->getModifiedQuery(), 'Does not handle '.$errorMessage);
    }
}
