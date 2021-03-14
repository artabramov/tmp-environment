<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../src/Echidna.php';

class EchidnaTest extends TestCase
{
    private $pdo;
    private $echidna;

    /**
     * Call private method from testing object.
     * @param $object
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
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

        $pdo_host    = 'localhost';
        $pdo_user    = 'root';
        $pdo_pass    = '123456';
        $pdo_dbase   = 'project';
        $pdo_charset = 'utf8';

        $dsn = 'mysql:host=' . $pdo_host . ';dbname=' . $pdo_dbase . ';charset=' . $pdo_charset;
        $args = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new PDO( $dsn, $pdo_user, $pdo_pass, $args );
        $this->echidna = new \artabramov\Echidna\Echidna( $this->pdo );
    }

    protected function tearDown() : void {
        $this->db = null;
        $this->echidna = null;
    }

    /**
     * @dataProvider addIsEmpty
     */
    public function testIsEmpty( $value, $expected ) {
        $result = $this->call( $this->echidna, 'is_empty', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsEmpty() {
        return [
            [ -1, false ],
            [ 0, true ],
            [ 1, false ],

            [ '', true ],
            [ ' ', true ],
            [ '0', true ],
            [ ' 0', true ],
            [ '0 ', true ],
            [ ' 0 ', true ],
            [ '1', false ],
            [ ' 1', false ],
            [ '1 ', false ],
            [ ' 1 ', false ],
        ];
    }

    /**
     * @dataProvider addIsString
     */
    public function testIsString( $value, $max_length, $expected ) {
        $result = $this->call( $this->echidna, 'is_string', [ $value, $max_length ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsString() {
        return [
            [ -1, 2, false ],
            [ 0, 2, false ],
            [ 1, 2, false ],

            [ '', 0, true ],
            [ '1', 1, true ],
            [ '11', 2, true ],
            [ '111', 2, false ],
        ];
    }


    /**
     * @dataProvider addIsInt
     */
    public function testIsInt( $value, $max_length, $expected ) {
        $result = $this->call( $this->echidna, 'is_int', [ $value, $max_length ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsInt() {
        return [
            [ '', 2, false ],
            [ '1', 2, false ],

            [ 0, 0, true ],
            [ 1, 0, false ],
            [ 1, 1, true ],
            [ 9, 1, true ],
            [ 10, 1, false ],
            [ 10, 2, true ],
            [ 99, 2, true ],
            [ 100, 2, false ],

            [ -1, 0, false ],
            [ -1, 1, true ],
            [ -9, 1, true ],
            [ -10, 1, false ],
            [ -10, 2, true ],
            [ -99, 2, true ],
            [ -100, 2, false ],
        ];
    }



    /**
     * @dataProvider addIsKey
     */
    public function testIsKey( $value, $max_length, $expected ) {
        $result = $this->call( $this->echidna, 'is_key', [ $value, $max_length ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsKey() {
        return [
            [ -1, 2, false ],
            [ 0, 2, false ],
            [ 1, 2, false ],

            [ 'a', 1, true ],
            [ 'aa', 2, true ],
            [ 'aaa', 2, false ],

            [ 'a1a', 3, true ],
            [ 'a_a', 3, true ],
            [ 'a-a', 3, true ],
            [ 'a.a', 3, false ],
            [ 'a,a', 3, false ],
            [ 'a a', 3, false ],
            
        ];
    }

}
