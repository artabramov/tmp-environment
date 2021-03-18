<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/../src/Echidna.php';
require_once __DIR__ . '/../src/Echidna/Attribute.php';

class AttributeTest extends TestCase
{
    private $pdo;
    private $attribute;

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
        $this->attribute = new \artabramov\Echidna\Echidna\Attribute( $this->pdo );
    }

    protected function tearDown() : void {
        $this->db = null;
        $this->attribute = null;
    }


    /**
     * @dataProvider addInsert
     */
    public function testInsert( $user_id, $attribute_key, $attribute_value, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );

        // test
        $result = $this->call( $this->attribute, 'insert', [ $user_id, $attribute_key, $attribute_value ] );
        $this->assertEquals( $expected, $result );

    }

    public function addInsert() {
        return [

            // TRUE: various correct user_id (int)
            [ 1, 'attribute_key', 'attribute value', true ],
            [ 9223372036854775807, 'attribute_key', 'attribute value', true ],

            // TRUE: various correct attribute_key (string)
            [ 1, 'a', 'attribute value', true ],
            [ 1, 'attribute_key_attrib', 'attribute value', true ],

            // TRUE: various correct attribute_value (string)
            [ 1, 'attribute_key', 'a', true ],
            [ 1, 'attribute_key', 'attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value', true ],

            // FALSE: incorrect user_id (int)
            [ 0, 'attribute_key', 'attribute value', false ],

            // FALSE: various incorrect attribute_key (string)
            [ 1, '', 'attribute value', false ],
            [ 1, ' ', 'attribute value', false ],
            [ 1, ' .', 'attribute value', false ],
            [ 1, 'attribute key', 'attribute value', false ],
            [ 1, 'attribute_key_attribu', 'attribute value', false ],

            // FALSE: various incorrect attribute_value (string)
            [ 1, 'attribute_key', '', false ],
            [ 1, 'attribute_key', ' ', false ],
            [ 1, 'attribute_key', 'attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value ', false ],

        ];
    }

    public function testInsertTwice() {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );

        // insert one attribute twice
        $result = $this->call( $this->attribute, 'insert', [ 1, 'attribute_key', 'attribute value' ] );
        $this->assertTrue( $result );

        $result = $this->call( $this->attribute, 'insert', [ 1, 'attribute_key', 'attribute value' ] );
        $this->assertFalse( $result );
    }


    




}
