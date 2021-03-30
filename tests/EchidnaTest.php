<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
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

        $dsn = 'mysql:host=' . PDO_HOST . ';dbname=' . PDO_DBASE . ';charset=' . PDO_CHARSET;
        $args = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new PDO( $dsn, PDO_USER, PDO_PASS, $args );
        $this->echidna = new \artabramov\Echidna\Echidna( $this->pdo );
    }

    protected function tearDown() : void {
        $this->pdo = null;
        $this->echidna = null;
    }

     
    






    /**
     * @dataProvider addIsset
     */
    public function testIsset( $value, $expected ) {

        $this->echidna->error = $value;
        $result = $this->call( $this->echidna, '__isset', [ 'error' ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsset() {
        return [

            // TRUE: various empty values
            [ 0, false ],
            [ 0.0, false ],
            [ '', false ],
            [ ' ', false ],
            [ '0', false ],
            [ '0 ', false ],
            

            // FALSE: various not empty values
            [ 1, true ],
            [ 1.0, true ],
            [ -1, true ],
            [ '0.0', true],
            [ '1', true ],
            [ '1 ', true ],
            [ 'value', true ],
        ];
    }

}