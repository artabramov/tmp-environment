<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Utilities/Filter.php';

class FilterTest extends TestCase
{
    private $validator;

    private function call( $object, string $method , array $parameters = [] ) {

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

    protected function setUp() : void {
        $this->validator = new \artabramov\Echidna\Utilities\Filter();
    }

    protected function tearDown() : void {
        $this->validator = null;
    }

    /**
     * @dataProvider addIsEmpty
     */
    public function testIsEmpty( $value, $expected ) {

        $result = $this->call( $this->validator, 'is_empty', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsEmpty() {
        return [

            // TRUE: various empty values
            [ 0, true ],
            [ 0.0, true ],
            [ '', true ],
            [ ' ', true ],
            [ '0', true ],
            [ '0 ', true ],
            

            // FALSE: various not empty values
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
     * @dataProvider addIsId
     */
    public function testIsId( $value, $expected ) {
        $result = $this->call( $this->validator, 'is_id', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsId() {
        return [

            // TRUE: various correct values (int|string)
            [ 0, true ],
            [ 1, true ],
            [ 9223372036854775807, true ],
 
            // FALSE: various not correct values (int|string)
            [ -1, false ],
            [ '', false ],
            [ '0', false ],
            [ '1', false ],
            [ '-1', false ],
            
        ];
    }

    /**
     * @dataProvider addIsKey
     */
    public function testIsKey( $value, $length, $expected ) {
        $result = $this->call( $this->validator, 'is_key', [ $value, $length ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsKey() {
        return [

            // TRUE: variuous correct values (int|string)
            [ '1', 20, true ],
            [ 'value', 20, true ],
            [ 'value_value_value_va', 20, true ],
            [ 'value-value-value-va', 20, true ],

            // FALSE: various incorrect values (int|string)
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
    public function testIsString( $value, $length, $expected ) {
        $result = $this->call( $this->validator, 'is_string', [ $value, $length ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsString() {
        return [

            // TRUE: variuous correct values (int|string)
            [ '', 255, true ],
            [ ' ', 255, true ],
            [ '0', 255, true ],
            [ '1', 255, true ],
            [ '-1', 255, true ],
            [ 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor i', 255, true ],

            // FALSE: various incorrect values (int|string)
            [ 0, 255, false ],
            [ 1, 255, false ],
            [ -1, 255, false ],
            [ 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in', 255, false ],
        ];
    }

    /**
     * @dataProvider addIsDatetime
     */
    public function testIsDatetime( $value, $expected ) {
        $result = $this->call( $this->validator, 'is_datetime', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsDatetime() {
        return [

            // TRUE: variuous correct values (int|string)
            [ '0001-01-01 01:01:01', true ],
            [ '2099-12-12 23:59:59', true ],

            // FALSE: various incorrect values (int|string)
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
     * @dataProvider addIsHex
     */
    public function testIsHex( $value, $length, $expected ) {
        $result = $this->call( $this->validator, 'is_hex', [ $value, $length ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsHex() {
        return [

            // TRUE: correct value (int|string)
            [ 'da39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd80719', 80, true ],

            // FALSE: various incorrect values (int|string)
            [ 0, 80, false ],
            [ 1, 80, false ],
            [ -1, 80, false ],            
            [ 'da39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd807190', 80, false ],
            [ 'da39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd8071', 80, false ],
            [ 'xa39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd8071', 80, false ],
        ];
    }

    /**
     * @dataProvider addIsEmail
     */
    public function testIsEmail( $value, $length, $expected ) {
        $result = $this->call( $this->validator, 'is_email', [ $value, $length ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsEmail() {
        return [

            // TRUE: variuous correct values (int|string)
            [ 'noreply@noreply.no', 255, true ],
            [ 'noreply.1@noreply.1.no', 255, true ],
            [ 'noreply.noreply@noreply.noreply.no', 255, true ],
            [ 'noreply-noreply.noreply@noreply-noreply.noreply.no', 255, true ],
            [ 'noreply_noreply.noreply@noreply_noreply.noreply.no', 255, true ],

            // FALSE: various incorrect values (int|string)
            [ 0, 255, false ],
            [ 1, 255, false ],
            [ -1, 255, false ],
            [ '@noreply.no', 255, false ],
            [ 'noreply@noreply', 255, false ],
            [ 'noreply@noreply.nono', 255, false ],

        ];
    }






}