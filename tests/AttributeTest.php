<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Interfaces/Sequenceable.php';
require_once __DIR__.'/../src/Models/Echidna.php';
require_once __DIR__.'/../src/Models/Attribute.php';
require_once __DIR__.'/../src/Utilities/Filter.php';

class AttributeTest extends TestCase
{
    private $pdo;
    private $attribute;

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

        $dsn = 'mysql:host=' . PDO_HOST . ';dbname=' . PDO_DBASE . ';charset=' . PDO_CHARSET;
        $args = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new PDO( $dsn, PDO_USER, PDO_PASS, $args );
        $this->attribute = new \artabramov\Echidna\Models\Attribute( $this->pdo );
    }

    protected function tearDown() : void {
        $this->pdo = null;
        $this->attribute = null;
    }

    /**
     * @dataProvider addCreate
     */
    public function testCreate( $user_id, $attribute_key, $attribute_value, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );

        $result = $this->callMethod( $this->attribute, 'create', [ $user_id, $attribute_key, $attribute_value, 2, 255 ] );
        $_user_id = $this->getProperty( $this->attribute, 'user_id' );
        $_attribute_key = $this->getProperty( $this->attribute, 'attribute_key' );
        $_attribute_value = $this->getProperty( $this->attribute, 'attribute_value' );
        $error = $this->getProperty( $this->attribute, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $_user_id, $user_id );
            $this->assertEquals( $_attribute_key, $attribute_key );
            $this->assertEquals( $_attribute_value, $attribute_value );
            $this->assertEmpty( $error );
            
        } else {
            $this->assertEquals( $_user_id, null );
            $this->assertEquals( $_attribute_key, null );
            $this->assertEquals( $_attribute_value, null );
            $this->assertNotEmpty( $error );
        }
        
    }

    public function addCreate() {
        return [

            // correct cases
            [ 1, 'attribute_key', 'attribute value', true ],
            [ 9223372036854775807, 'attribute_key', 'attribute value', true ],
            [ 1, 'a', 'attribute value', true ],
            [ 1, 'attribute_key', 'at', true ],
            [ 1, 'attribute_key_attrib', 'attribute value', true ],
            [ 1, 'attribute_key', 'attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value', true ],

            // incorrect cases
            [ 0, 'attribute_key', 'attribute value', false ],
            [ -1, 'attribute_key', 'attribute value', false ],
            [ 1, '', 'attribute value', false ],
            [ 1, ' ', 'attribute value', false ],
            [ 1, '0', 'attribute value', false ],
            [ 1, '0 ', 'attribute value', false ],
            [ 1, 'attribute key', 'attribute value', false ],
            [ 1, 'attribute_key_attribu', 'attribute value', false ],
            [ 1, 'attribute_key', '', false ],
            [ 1, 'attribute_key', ' ', false ],
            [ 1, 'attribute_key', '0', false ],
            [ 1, 'attribute_key', '0 ', false ],
            [ 1, 'attribute_key', 'attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value attribute value ', false ],

        ];
    }

    /**
     * @dataProvider addRevalue
     */
    public function testRevalue( $user_id, $attribute_key, $attribute_value, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_attributes (id, date, user_id, attribute_key, attribute_value) VALUES (1, '2000-01-01 00:00:00', 1, 'user_name', 'John Doe');" );

        $result = $this->callMethod( $this->attribute, 'revalue', [ $user_id, $attribute_key, $attribute_value, 2, 255 ] );
        $_user_id = $this->getProperty( $this->attribute, 'user_id' );
        $_attribute_key = $this->getProperty( $this->attribute, 'attribute_key' );
        $_attribute_value = $this->getProperty( $this->attribute, 'attribute_value' );
        $error = $this->getProperty( $this->attribute, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $_user_id, $user_id );
            $this->assertEquals( $_attribute_key, $attribute_key );
            $this->assertEquals( $_attribute_value, $attribute_value );
            $this->assertEmpty( $error );

        } else {
            $this->assertEquals( $_user_id, null );
            $this->assertEquals( $_attribute_key, null );
            $this->assertEquals( $_attribute_value, null );
            $this->assertNotEmpty( $error );
        }
    }

    public function addRevalue() {
        return [

            // correct case
            [ 1, 'user_name', 'Sarah Connor', true ],

            // incorrect cases
            [ 0, 'user_name', 'Sarah Connor', false ],
            [ 2, 'user_name', 'Sarah Connor', false ],
            [ -1, 'user_name', 'Sarah Connor', false ],
            [ 1, '', 'Sarah Connor', false ],
            [ 1, '0', 'Sarah Connor', false ],
            [ 1, '0 ', 'Sarah Connor', false ],
            [ 1, '_user_name_', 'Sarah Connor', false ],
            [ 1, 'attribute_key_attribu', 'Sarah Connor', false ],
            [ 1, 'user_name', '', false ],
            [ 1, 'user_name', '0', false ],
            [ 1, 'user_name', '0 ', false ],
            [ 1, 'user_name', 'S', false ],
            [ 1, 'user_name', 'Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Connor Sarah Con', false ],

        ];
    }

    /**
     * @dataProvider addRemove
     */
    public function testRemove( $user_id, $attribute_key, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_attributes (id, date, user_id, attribute_key, attribute_value) VALUES (1, '2000-01-01 00:00:00', 1, 'user_name', 'John Doe');" );

        $result = $this->callMethod( $this->attribute, 'remove', [ $user_id, $attribute_key ] );
        $error = $this->getProperty( $this->attribute, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEmpty( $error );

        } else {
            $this->assertNotEmpty( $error );
        }
    }

    public function addRemove() {
        return [

            // correct case
            [ 1, 'user_name', true ],

            // incorrect cases
            [ 0, 'user_name', false ],
            [ 2, 'user_name', false ],
            [ -1, 'user_name', false  ],
            [ 1, '', false ],
            [ 1, '_user_name', false ],

        ];
    }

    /**
     * @dataProvider addFetch
     */
    public function testFetch( $user_id, $attribute_key, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_attributes (id, date, user_id, attribute_key, attribute_value) VALUES (1, '2000-01-01 00:00:00', 1, 'user_name', 'John Doe');" );

        $result = $this->callMethod( $this->attribute, 'fetch', [ $user_id, $attribute_key ] );

        $_id = $this->getProperty( $this->attribute, 'id' );
        $_date = $this->getProperty( $this->attribute, 'date' );
        $_user_id = $this->getProperty( $this->attribute, 'user_id' );
        $_attribute_key = $this->getProperty( $this->attribute, 'attribute_key' );
        $_attribute_value = $this->getProperty( $this->attribute, 'attribute_value' );
        $error = $this->getProperty( $this->attribute, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $_id, 1 );
            $this->assertEquals( $_date, '2000-01-01 00:00:00' );
            $this->assertEquals( $_user_id, 1 );
            $this->assertEquals( $_attribute_key, 'user_name' );
            $this->assertEquals( $_attribute_value, 'John Doe' );
            $this->assertEmpty( $error );

        } else {
            $this->assertEquals( $_id, null );
            $this->assertEquals( $_date, null );
            $this->assertEquals( $_user_id, null );
            $this->assertEquals( $_attribute_key, null );
            $this->assertEquals( $_attribute_value, null );
            $this->assertNotEmpty( $error );
        }
    }

    public function addFetch() {
        return [

            // correct case
            [ 1, 'user_name', true ],

            // incorrect cases
            [ 0, 'user_name', false ],
            [ 2, 'user_name', false ],
            [ -1, 'user_name', false ],
            [ 1, '', false ],
            [ 1, '_user_name', false ],
        ];
    }

    /**
     * @dataProvider addGetone
     */
    public function testGetone( $attribute_id, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_attributes;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_attributes (id, date, user_id, attribute_key, attribute_value) VALUES (1, '2000-01-01 00:00:00', 1, 'user_name', 'John Doe');" );

        $result = $this->callMethod( $this->attribute, 'getone', [ $attribute_id ] );

        $_id = $this->getProperty( $this->attribute, 'id' );
        $_date = $this->getProperty( $this->attribute, 'date' );
        $_user_id = $this->getProperty( $this->attribute, 'user_id' );
        $_attribute_key = $this->getProperty( $this->attribute, 'attribute_key' );
        $_attribute_value = $this->getProperty( $this->attribute, 'attribute_value' );
        $error = $this->getProperty( $this->attribute, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $_id, 1 );
            $this->assertEquals( $_date, '2000-01-01 00:00:00' );
            $this->assertEquals( $_user_id, 1 );
            $this->assertEquals( $_attribute_key, 'user_name' );
            $this->assertEquals( $_attribute_value, 'John Doe' );
            $this->assertEmpty( $error );

        } else {
            $this->assertEquals( $_id, null );
            $this->assertEquals( $_date, null );
            $this->assertEquals( $_user_id, null );
            $this->assertEquals( $_attribute_key, null );
            $this->assertEquals( $_attribute_value, null );
            $this->assertNotEmpty( $error );
        }
    }

    public function addGetone() {
        return [

            // correct case
            [ 1, true ],

            // incorrect cases
            [ 0, false ],
            [ 2, false ],
            [ -1, false ],
        ];
    }

}
