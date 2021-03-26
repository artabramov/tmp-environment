<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/../src/Echidna.php';
require_once __DIR__ . '/../src/Echidna/Role.php';

class RoleTest extends TestCase
{
    private $pdo;
    private $role;

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
        $this->role = new \artabramov\Echidna\Echidna\Role( $this->pdo );
    }

    protected function tearDown() : void {
        $this->pdo = null;
        $this->role = null;
    }

    /**
     * @dataProvider addSet
     */
    public function testSet( $user_id, $hub_id, $user_role, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_roles;" );

        // test
        $result = $this->call( $this->role, 'set', [ $user_id, $hub_id, $user_role ] );
        $this->assertEquals( $expected, $result );

    }

    public function addSet() {
        return [

            // TRUE: various correct cases
            [ 1, 1, 'admin', true ],
            [ 9223372036854775807, 1, 'admin', true ],
            [ 1, 9223372036854775807, 'admin', true ],
            [ 1, 1, 'a', true ],
            [ 1, 1, 'admin_admin_admin_ad', true ],

            // FALSE: incorrect user_id (int only)
            [ 0, 1, 'admin', false ],
            [ -1, 1, 'admin', false ],

            // FALSE: incorrect hub_id (int only)
            [ 1, 0, 'admin', false ],
            [ 1, -1, 'admin', false ],

            // FALSE: various incorrect user_role (string only)
            [ 1, 1, '', false ],
            [ 1, 1, ' ', false ],
            [ 1, 1, '0', false ],
            [ 1, 1, '0 ', false ],
            [ 1, 1, 'admin_admin_admin_adm', false ],

        ];
    }

    /**
     * @dataProvider addReset
     */
    public function testReset( $user_id, $hub_id, $user_role, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_roles;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_roles (id, date, hub_id, user_id, user_role) VALUES (1, '2000-01-01 00:00:00', 1, 1, 'admin');" );

        // test
        $result = $this->call( $this->role, 'reset', [ $user_id, $hub_id, $user_role ] );
        $this->assertEquals( $expected, $result );

    }

    public function addReset() {
        return [

            // TRUE: various correct cases
            [ 1, 1, 'editor', true ],
            [ 1, 1, 'e', true ],
            [ 1, 1, 'editor_editor_editor', true ],

            // FALSE: incorrect user_id (int only)
            [ 0, 1, 'editor', false ],
            [ -1, 1, 'editor', false ],

            // FALSE: incorrect hub_id (int only)
            [ 1, 0, 'editor', false ],
            [ 1, -1, 'editor', false ],

            // FALSE: various incorrect user_role (string only)
            [ 1, 1, '', false ],
            [ 1, 1, ' ', false ],
            [ 1, 1, '0', false ],
            [ 1, 1, '0 ', false ],
            [ 1, 1, 'editor_editor_editor_', false ],

        ];
    }

    /**
     * @dataProvider addGetOne
     */
    public function testGetOne( $user_id, $hub_id, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_roles;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_roles (id, date, hub_id, user_id, user_role) VALUES (1, '2000-01-01 00:00:00', 1, 1, 'admin');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_roles (id, date, hub_id, user_id, user_role) VALUES (2, '2000-01-01 00:00:00', 1, 2, 'editor');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_roles (id, date, hub_id, user_id, user_role) VALUES (3, '2000-01-01 00:00:00', 2, 1, 'reader');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_roles (id, date, hub_id, user_id, user_role) VALUES (4, '2000-01-01 00:00:00', 2, 2, 'invited');" );

        // test
        $result = $this->call( $this->role, 'get_one', [ $user_id, $hub_id ] );
        $this->assertEquals( $expected, $result );

    }

    public function addGetOne() {
        return [

            // TRUE: various correct cases
            [ 1, 1, true ],
            [ 1, 2, true ],
            [ 2, 1, true ],
            [ 2, 2, true ],

            // FALSE: incorrect user_id (int only)
            [ 0, 1, false ],
            [ -1, 1, false ],

            // FALSE: incorrect hub_id (int only)
            [ 1, 0, false ],
            [ 1, -1, false ],

        ];
    }





    /**
     * @dataProvider addRemove
     */
    public function testRemove( $user_id, $hub_id, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_roles;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".user_roles (id, date, hub_id, user_id, user_role) VALUES (1, '2000-01-01 00:00:00', 1, 1, 'admin');" );

        // test
        $result = $this->call( $this->role, 'remove', [ $user_id, $hub_id ] );
        $this->assertEquals( $expected, $result );

    }

    public function addRemove() {
        return [

            // TRUE: correct case
            [ 1, 1, true ],

            // FALSE: incorrect user_id (int only)
            [ 0, 1, false ],
            [ 2, 1, false ],
            [ -1, 1, false ],

            // FALSE: incorrect hub_id (int only)
            [ 1, 0, false ],
            [ 1, -1, false ],
            [ 1, 2, false ],

        ];
    }

}
