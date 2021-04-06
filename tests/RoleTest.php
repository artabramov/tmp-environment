<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Interfaces/Sequenceable.php';
require_once __DIR__.'/../src/Models/Echidna.php';
require_once __DIR__.'/../src/Models/Role.php';
require_once __DIR__.'/../src/Utilities/Filter.php';

class RoleTest extends TestCase
{
    private $pdo;
    private $role;

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
        $this->role = new \artabramov\Echidna\Models\Role( $this->pdo );
    }

    protected function tearDown() : void {
        $this->pdo = null;
        $this->role = null;
    }

    /**
     * @dataProvider addCreate
     */
    public function testCreate( $hub_id, $user_id, $user_role, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_roles;" );

        $result = $this->callMethod( $this->role, 'create', [ $hub_id, $user_id, $user_role ] );
        $_id = $this->getProperty( $this->role, 'id' );
        $_hub_id = $this->getProperty( $this->role, 'hub_id' );
        $_user_id = $this->getProperty( $this->role, 'user_id' );
        $_user_role = $this->getProperty( $this->role, 'user_role' );
        $error = $this->getProperty( $this->role, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $_id, 1 );
            $this->assertEquals( $_hub_id, $hub_id );
            $this->assertEquals( $_user_id, $user_id );
            $this->assertEquals( $_user_role, $user_role );
            $this->assertEmpty( $error );

        } else {
            $this->assertEquals( $_id, null );
            $this->assertEquals( $_hub_id, null );
            $this->assertEquals( $_user_id, null );
            $this->assertEquals( $_user_role, null );
            $this->assertNotEmpty( $error );
        }
    }

    public function addCreate() {
        return [

            // correct cases
            [ 1, 1, 'admin', true ],
            [ 9223372036854775807, 1, 'admin', true ],
            [ 1, 9223372036854775807, 'admin', true ],
            [ 1, 1, 'a', true ],
            [ 1, 1, 'admin_admin_admin_ad', true ],

            // incorrect cases
            [ 0, 1, 'admin', false ],
            [ -1, 1, 'admin', false ],
            [ 1, 0, 'admin', false ],
            [ 1, -1, 'admin', false ],
            [ 1, 1, '', false ],
            [ 1, 1, ' ', false ],
            [ 1, 1, '0', false ],
            [ 1, 1, '0 ', false ],
            [ 1, 1, 'admin_admin_admin_adm', false ],

        ];
    }

    /**
     * @dataProvider addRerole
     */
    public function testRerole( $hub_id, $user_id, $user_role, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_roles;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_roles (id, date, hub_id, user_id, user_role) VALUES (1, '2000-01-01 00:00:00', 1, 1, 'admin');" );

        $result = $this->callMethod( $this->role, 'rerole', [ $hub_id, $user_id, $user_role ] );
        $_hub_id = $this->getProperty( $this->role, 'hub_id' );
        $_user_id = $this->getProperty( $this->role, 'user_id' );
        $_user_role = $this->getProperty( $this->role, 'user_role' );
        $error = $this->getProperty( $this->role, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $_hub_id, $hub_id );
            $this->assertEquals( $_user_id, $user_id );
            $this->assertEquals( $_user_role, $user_role );
            $this->assertEmpty( $error );

        } else {
            $this->assertEquals( $_hub_id, null );
            $this->assertEquals( $_user_id, null );
            $this->assertEquals( $_user_role, null );
            $this->assertNotEmpty( $error );
        }
    }

    public function addRerole() {
        return [

            // correct cases
            [ 1, 1, 'editor', true ],
            [ 1, 1, 'e', true ],
            [ 1, 1, 'editor_editor_editor', true ],

            // incorrect values
            [ 0, 1, 'editor', false ],
            [ -1, 1, 'editor', false ],
            [ 1, 0, 'editor', false ],
            [ 1, -1, 'editor', false ],
            [ 1, 1, '', false ],
            [ 1, 1, ' ', false ],
            [ 1, 1, '0', false ],
            [ 1, 1, '0 ', false ],
            [ 1, 1, 'editor_editor_editor_', false ],

        ];
    }

    /**
     * @dataProvider addFetch
     */
    public function testFetch( $hub_id, $user_id, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_roles;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_roles (id, date, hub_id, user_id, user_role) VALUES (1, '2000-01-01 00:00:00', 1, 1, 'admin');" );

        $result = $this->callMethod( $this->role, 'fetch', [ $hub_id, $user_id ] );

        $id = $this->getProperty( $this->role, 'id' );
        $date = $this->getProperty( $this->role, 'date' );
        $hub_id = $this->getProperty( $this->role, 'hub_id' );
        $user_id = $this->getProperty( $this->role, 'user_id' );
        $user_role = $this->getProperty( $this->role, 'user_role' );
        $error = $this->getProperty( $this->role, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $id, 1 );
            $this->assertEquals( $date, '2000-01-01 00:00:00' );
            $this->assertEquals( $hub_id, 1 );
            $this->assertEquals( $user_id, 1 );
            $this->assertEquals( $user_role, 'admin' );
            $this->assertEmpty( $error );

        } else {
            $this->assertEquals( $id, null );
            $this->assertEquals( $date, null );
            $this->assertEquals( $hub_id, null );
            $this->assertEquals( $user_id, null );
            $this->assertEquals( $user_role, null );
            $this->assertNotEmpty( $error );
        }
    }

    public function addFetch() {
        return [

            // correct case
            [ 1, 1, true ],

            // incorrect cases
            [ 0, 1, false ],
            [ 2, 1, false ],
            [ -1, 1, false ],
            [ 1, 0, false ],
            [ 1, 2, false ],
            [ 1, -1, false ],
        ];
    }

    /**
     * @dataProvider addGetone
     */
    public function testGetone( $role_id, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_roles;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_roles (id, date, hub_id, user_id, user_role) VALUES (1, '2000-01-01 00:00:00', 1, 1, 'admin');" );

        $result = $this->callMethod( $this->role, 'getone', [ $role_id ] );

        $id = $this->getProperty( $this->role, 'id' );
        $date = $this->getProperty( $this->role, 'date' );
        $hub_id = $this->getProperty( $this->role, 'hub_id' );
        $user_id = $this->getProperty( $this->role, 'user_id' );
        $user_role = $this->getProperty( $this->role, 'user_role' );
        $error = $this->getProperty( $this->role, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $id, 1 );
            $this->assertEquals( $date, '2000-01-01 00:00:00' );
            $this->assertEquals( $hub_id, 1 );
            $this->assertEquals( $user_id, 1 );
            $this->assertEquals( $user_role, 'admin' );
            $this->assertEmpty( $error );

        } else {
            $this->assertEquals( $id, null );
            $this->assertEquals( $date, null );
            $this->assertEquals( $hub_id, null );
            $this->assertEquals( $user_id, null );
            $this->assertEquals( $user_role, null );
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
