<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Interfaces/Sequenceable.php';
require_once __DIR__.'/../src/Models/Echidna.php';
require_once __DIR__.'/../src/Models/Hub.php';
require_once __DIR__.'/../src/Utilities/Filter.php';

class HubTest extends TestCase
{
    private $pdo;
    private $hub;

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
        $this->hub = new \artabramov\Echidna\Models\Hub( $this->pdo );
    }

    protected function tearDown() : void {
        $this->pdo = null;
        $this->hub = null;
    }

    /**
     * @dataProvider addCreate
     */
    public function testCreate( $user_id, $hub_status, $hub_name, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );

        $result = $this->callMethod( $this->hub, 'create', [ $user_id, $hub_status, $hub_name, 2, 40 ] );
        $_user_id = $this->getProperty( $this->hub, 'user_id' );
        $_hub_status = $this->getProperty( $this->hub, 'hub_status' );
        $_hub_name = $this->getProperty( $this->hub, 'hub_name' );
        $error = $this->getProperty( $this->hub, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $_user_id, $user_id );
            $this->assertEquals( $_hub_status, $hub_status );
            $this->assertEquals( $_hub_name, $hub_name );
            $this->assertEmpty( $error );

        } else {
            $this->assertEquals( $_user_id, null );
            $this->assertEquals( $_hub_status, null );
            $this->assertEquals( $_hub_name, null );
            $this->assertNotEmpty( $error );
        }
    }

    public function addCreate() {
        return [

            // correct cases
            [ 1, 'status', 'hub name', true ],
            [ 9223372036854775807, 'status', 'hub name', true ],
            [ 1, 'status_status_status', 'hub name', true ],
            [ 1, 'status', 'hu', true ],
            [ 1, 'status', 'hub name hub name hub name hub name hubn', true ],

            // incorrect cases
            [ 0, 'status', 'hub name', false ],
            [ -1, 'status', 'hub name', false ],
            [ 1, '', 'hub name', false ],
            [ 1, '0', 'hub name', false ],
            [ 1, 'status_status_status_', 'hub name', false ],
            [ 1, 'status', '', false ],
            [ 1, 'status', '0', false ],
            [ 1, 'status', 'h', false ],
            [ 1, 'status', 'hub name hub name hub name hub name hub n', false ],

        ];
    }

    /**
     * @dataProvider addRename
     */
    public function testRename( $hub_id, $hub_name, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'public', 'hub name');" );

        $result = $this->callMethod( $this->hub, 'rename', [ $hub_id, $hub_name, 2, 40 ] );
        $_hub_name = $this->getProperty( $this->hub, 'hub_name' );
        $error = $this->getProperty( $this->hub, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $_hub_name, $hub_name );
            $this->assertEmpty( $error );

        } else {
            $this->assertEquals( $_hub_name, null );
            $this->assertNotEmpty( $error );
        }
    }

    public function addRename() {
        return [

            // correct cases
            [ 1, 'hub name', true ],
            [ 1, 'hu', true ],
            [ 1, 'hub name hub name hub name hub name hubn', true ],

            // incorrect cases
            [ 1, '', false ],
            [ 1, '0', false ],
            [ 1, 'h', false ],
            [ 1, 'hub name hub name hub name hub name hub n', false ],

        ];
    }

    /**
     * @dataProvider addTrash
     */
    public function testTrash( $hub_id, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'public', 'hub name');" );

        $result = $this->callMethod( $this->hub, 'trash', [ $hub_id ] );
        $_hub_id = $this->getProperty( $this->hub, 'id' );
        $_hub_status = $this->getProperty( $this->hub, 'hub_status' );
        $error = $this->getProperty( $this->hub, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $_hub_id, $hub_id );
            $this->assertEquals( $_hub_status, 'trash' );
            $this->assertEmpty( $error );

        } else {
            $this->assertEquals( $_hub_id, null );
            $this->assertEquals( $_hub_status, null );
            $this->assertNotEmpty( $error );
        }
    }

    public function addTrash() {
        return [

            // correct case
            [ 1, true ],

            // incorrect cases
            [ 0, false ],
            [ 2, false ],
            [ -1, false  ],

        ];
    }

    /**
     * @dataProvider addRecover
     */
    public function testRecover( $hub_id, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'trash', 'hub name');" );

        $result = $this->callMethod( $this->hub, 'recover', [ $hub_id ] );
        $_hub_id = $this->getProperty( $this->hub, 'id' );
        $_hub_status = $this->getProperty( $this->hub, 'hub_status' );
        $error = $this->getProperty( $this->hub, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $_hub_id, $hub_id );
            $this->assertEquals( $_hub_status, 'public' );
            $this->assertEmpty( $error );

        } else {
            $this->assertEquals( $_hub_id, null );
            $this->assertEquals( $_hub_status, null );
            $this->assertNotEmpty( $error );
        }
    }

    public function addRecover() {
        return [

            // correct case
            [ 1, true ],

            // incorrect cases
            [ 0, false ],
            [ 2, false ],
            [ -1, false  ],

        ];
    }

    /**
     * @dataProvider addRemove
     */
    public function testRemove( $hub_id, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'trash', 'hub name');" );

        $result = $this->callMethod( $this->hub, 'remove', [ $hub_id ] );
        $error = $this->getProperty( $this->hub, 'error' );

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
            [ 1, true ],

            // incorrect cases
            [ 0, false ],
            [ 2, false ],
            [ -1, false  ],

        ];
    }

    /**
     * @dataProvider addGetone
     */
    public function testGetone( $hub_id, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'public', 'hub name');" );

        $result = $this->callMethod( $this->hub, 'getone', [ $hub_id ] );

        $id = $this->getProperty( $this->hub, 'id' );
        $date = $this->getProperty( $this->hub, 'date' );
        $user_id = $this->getProperty( $this->hub, 'user_id' );
        $hub_status = $this->getProperty( $this->hub, 'hub_status' );
        $hub_name = $this->getProperty( $this->hub, 'hub_name' );
        $error = $this->getProperty( $this->hub, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $id, 1 );
            $this->assertEquals( $date, '2000-01-01 00:00:00' );
            $this->assertEquals( $user_id, 1 );
            $this->assertEquals( $hub_status, 'public' );
            $this->assertEquals( $hub_name, 'hub name' );
            $this->assertEmpty( $error );

        } else {
            $this->assertEquals( $id, null );
            $this->assertEquals( $date, null );
            $this->assertEquals( $user_id, null );
            $this->assertEquals( $hub_status, null );
            $this->assertEquals( $hub_name, null );
            $this->assertNotEmpty( $error );
        }
    }

    public function addGetone() {
        return [

            // correct case
            [ 1, true ],

            // incorrect case
            [ 0, false ],
            [ 2, false ],
            [ -1, false ],

        ];
    }

}
