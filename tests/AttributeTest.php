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
        $this->pdo = null;
        $this->attribute = null;
    }

    /**
     * @dataProvider addSet
     */
    public function testSet( $user_id, $attribute_key, $attribute_value, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );

        // test
        $result = $this->call( $this->attribute, 'set', [ $user_id, $attribute_key, $attribute_value ] );
        $this->assertEquals( $expected, $result );

    }

    public function addSet() {
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

    public function testSetTwice() {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );

        // insert one attribute twice
        $result = $this->call( $this->attribute, 'set', [ 1, 'attribute_key', 'attribute value' ] );
        $this->assertTrue( $result );

        $result = $this->call( $this->attribute, 'set', [ 1, 'attribute_key', 'attribute value' ] );
        $this->assertFalse( $result );
    }
    
    /**
     * @dataProvider addPut
     */
    public function testPut( $user_id, $attribute_key, $attribute_value, $expected ) {

        // truncate table before testing and prepare test dataset
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_attributes (id, date, user_id, attribute_key, attribute_value) VALUES (1, '2000-01-01 00:00:00', 1, 'user_name', 'John Doe');" );

        // test
        $result = $this->call( $this->attribute, 'put', [ $user_id, $attribute_key, $attribute_value ] );
        $this->assertEquals( $expected, $result );

    }

    public function addPut() {
        return [

            // TRUE: correct data
            [ 1, 'user_name', 'Sarah Connor', true ],

            // FALSE: empty user_id (int)
            [ 0, 'user_name', 'Sarah Connor', false ],

            // FALSE: incorrect user_id (int)
            [ 2, 'user_name', 'Sarah Connor', false ],

            // FALSE: incorrect attribute_key (str)
            [ 1, '', 'Sarah Connor', false ],
            [ 1, '_user_name_', 'Sarah Connor', false ],
            [ 1, 'attribute_key_attribu', 'Sarah Connor', false ],

            // FALSE: incorrect attribute_value (str)
            [ 1, 'user_name', '', false ],
            [ 1, 'user_name', 'Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Con', false ],

        ];
    }

    /**
     * @dataProvider addUnset
     */
    public function testUnset( $user_id, $attribute_key, $expected ) {

        // truncate table before testing and prepare test dataset
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_attributes (id, date, user_id, attribute_key, attribute_value) VALUES (1, '2000-01-01 00:00:00', 1, 'user_name', 'John Doe');" );

        // test
        $result = $this->call( $this->attribute, 'unset', [ $user_id, $attribute_key ] );
        $this->assertEquals( $expected, $result );
    }

    public function addUnset() {
        return [

            // TRUE: correct data
            [ 1, 'user_name', true ],

            // FALSE: empty user_id (int)
            [ 0, 'user_name', false ],

            // FALSE: incorrect user_id (int)
            [ 2, 'user_name', false ],

            // FALSE: incorrect attribute_key (str)
            [ 1, '', false ],
            [ 1, '_user_name_', false ],
            [ 1, 'attribute_key_attribu', false ],


        ];
    }

    /**
     * @dataProvider addGet
     */
    public function testGet( $user_id, $attribute_key, $expected ) {

        // truncate table user_attributes and insert datasets
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_attributes (id, date, user_id, attribute_key, attribute_value) VALUES (1, '2000-01-01 00:00:00', 1, 'user_name', 'John Doe');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_attributes (id, date, user_id, attribute_key, attribute_value) VALUES (2, '2000-01-01 00:00:00', 1, 'user_city', 'New York');" );

        // do test
        $result = $this->call( $this->attribute, 'get', [ $user_id, $attribute_key ] );
        $this->assertEquals( $expected, $result );

    }

    public function addGet() {
        return [

            // TRUE: correct args
            [ 1, 'user_name', true ],

            // FALSE: user_id not exists
            [ 2, 'user_name', false ],

            // FALSE: user_id is null
            [ 0, 'user_id', false ],

            // TRUE: empty attribute_key
            [ 1, '', false ],

            // TRUE: incorrect attribute_key
            [ 1, '_user_name_', false ],

            // TRUE: incorrect attribute_key
            [ 1, '_user_name_user_name_', false ],

        ];
    }

    /**
     * @dataProvider addAll
     */
    public function testAll( $user_id, $expected ) {

        // truncate table user_attributes and insert datasets
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_attributes (id, date, user_id, attribute_key, attribute_value) VALUES (1, '2000-01-01 00:00:00', 1, 'user_name', 'John Doe');" );

        // do test
        $result = $this->call( $this->attribute, 'all', [ $user_id ] );
        $this->assertEquals( $expected, $result );

    }

    public function addAll() {
        return [

            // TRUE: correct args
            [ 1, true ],

            // FALSE: user_id not exists
            [ 2, false ],

            // FALSE: user_id is null
            [ 0, false ],

        ];
    }

}
