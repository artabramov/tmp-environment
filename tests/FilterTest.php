<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Utilities/Filter.php';

class FilterTest extends TestCase
{
    private $filter;

    /**
     * @param $object
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    private function callMethod( $object, string $method , array $parameters = [] ) {

        try {
            $className = get_class($object);
            $reflection = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
           throw new \Exception($e->getMessage());
        }

        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @param $object
     * @param string $property
     * @return mixed
     */
    public function getProperty( $object, $property ) {
        $reflectedClass = new \ReflectionClass($object);
        $reflection = $reflectedClass->getProperty($property);
        $reflection->setAccessible(true);
        return $reflection->getValue($object);
    }

    protected function setUp() : void {
        $this->filter = new \artabramov\Echidna\Utilities\Filter();
    }

    protected function tearDown() : void {
        $this->filter = null;
    }

    /**
     * @dataProvider addIsEmpty
     */
    public function testIsEmpty( $value, $expected ) {

        $result = $this->callMethod( $this->filter, 'is_empty', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsEmpty() {
        return [

            // correct cases
            [ 0, true ],
            [ 0.0, true ],
            [ '', true ],
            [ ' ', true ],
            [ '0', true ],
            [ '0 ', true ],
            
            // incorrect cases
            [ 1, false ],
            [ 1.0, false ],
            [ -1, false ],
            [ '0.0', false],
            [ '1', false ],
            [ '1 ', false ],
            [ 'value', false ],
        ];
    }

    /**
     * @dataProvider addIsInt
     */
    public function testIsInt( $value, $expected ) {
        $result = $this->callMethod( $this->filter, 'is_int', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsInt() {
        return [

            // correct cases
            [ 0, true ],
            [ 1, true ],
            [ 9223372036854775807, true ],
            [ '0', true ],
            [ '1', true ],
            [ '9223372036854775807', true ],
 
            // incorrect cases
            [ -1, false ],
            [ '', false ],
            [ '-1', false ],
            [ '1.0', false ],
            [ 'a', false ],
        ];
    }

    /**
     * @dataProvider addIsKey
     */
    public function testIsKey( $value, $max_length, $expected ) {
        $result = $this->callMethod( $this->filter, 'is_key', [ $value, $max_length ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsKey() {
        return [

            // correct cases
            [ '1', 20, true ],
            [ 'value', 20, true ],
            [ 'value_value_value_va', 20, true ],
            [ 'value-value-value-va', 20, true ],

            // incorrect cases
            [ 0, 20, false ],
            [ 1, 20, false ],
            [ -1, 20, false ],
            [ 'value value value va', 20, false ],
            [ 'value,value-value.va', 20, false ],
            [ 'value_value_value_val', 20, false ],
        ];
    }

    /**
     * @dataProvider addIsString
     */
    public function testIsString( $value, $min_length, $max_length, $expected ) {
        $result = $this->callMethod( $this->filter, 'is_string', [ $value, $min_length, $max_length ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsString() {
        return [

            // correct cases
            [ '11', 2, 255, true ],
            [ 'Lo', 2, 255, true ],
            [ 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor i', 2, 255, true ],

            // incorrect cases
            [ '', 2, 255, false ],
            [ ' ', 2, 255, false ],
            [ '1', 2, 255, false ],
            [ 'L', 2, 255, false ],
            [ 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in', 2, 255, false ],

        ];
    }

    /**
     * @dataProvider addIsHex
     */
    public function testIsHex( $value, $length, $expected ) {
        $result = $this->callMethod( $this->filter, 'is_hex', [ $value, $length ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsHex() {
        return [

            // correct case
            [ 'da39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd80719', 80, true ],

            // incorrect cases
            [ 0, 80, false ],
            [ 1, 80, false ],
            [ -1, 80, false ],            
            [ 'da39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd807190', 80, false ],
            [ 'da39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd8071', 80, false ],
            [ 'xa39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd8071', 80, false ],
        ];
    }

    /**
     * @dataProvider addIsDatetime
     */
    public function testIsDatetime( $value, $expected ) {
        $result = $this->callMethod( $this->filter, 'is_datetime', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsDatetime() {
        return [

            // correct cases
            [ '0001-01-01 01:01:01', true ],
            [ '2099-12-12 23:59:59', true ],

            // incorrect cases
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],
            [ '0000-00-00 00:00:00', false ],
            [ '1970-00-00 00:00:00', false ],
            [ '2021-13-01 00:00:00', false ],
            [ '2021-01-32 00:00:00', false ],
            [ '2021-01-01 25:00:00', false ],
            [ '2021-01-01 00:60:00', false ],
            [ '2021-01-01 00:00:60', false ],
            [ '2021-02-29 00:00:00', false ],
            [ '2021-02-30 00:00:00', false ],
            [ '2021-02-31 00:00:00', false ],
            [ '2021-04-31 00:00:00', false ],
            [ 'yyyy-mm-dd hh:mm:ss', false ],
        ];
    }

    /**
     * @dataProvider addIsEmail
     */
    public function testIsEmail( $value, $expected ) {
        $result = $this->callMethod( $this->filter, 'is_email', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsEmail() {
        return [

            // correct cases
            [ 'noreply@noreply.no', true ],
            [ '1.noreply@noreply.no', true ],
            [ 'noreply.1@noreply.1.no', true ],
            [ 'noreply@noreply.1.no', true ],
            [ 'noreply@1.noreply.no', true ],
            [ 'noreply.noreply@noreply.noreply.no', true ],
            [ '1-noreply-noreply.noreply-1@noreply-noreply-1.noreply-1.no', true ],
            [ 'noreply_noreply.noreply@noreply_noreply.noreply.no', true ],

            // incorrect cases
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],
            [ '@noreply.no', false ],
            [ 'noreply@noreply', false ],
            [ 'noreply@noreply.nono', false ],

        ];
    }

}