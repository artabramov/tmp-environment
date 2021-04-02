<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/../src/Echidna.php';
require_once __DIR__ . '/../src/Echidna/Hub.php';

class HubTest extends TestCase
{
    private $pdo;
    private $hub;

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
        $this->hub = new \artabramov\Echidna\Echidna\Hub( $this->pdo );
    }

    protected function tearDown() : void {
        $this->pdo = null;
        $this->hub = null;
    }

    /**
     * @dataProvider addSet
     */
    public function testSet( $user_id, $hub_status, $hub_name, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );

        // test
        $result = $this->call( $this->hub, 'set', [ $user_id, $hub_status, $hub_name ] );
        $this->assertEquals( $expected, $result );

    }

    public function addSet() {
        return [

            // TRUE: various correct user_id
            [ 1, 'public', 'hub name', true ],
            [ 9223372036854775807, 'private', 'hub name', true ],

            // TRUE: various correct hub_status
            [ 1, 'public', 'hub name', true ],
            [ 1, 'private', 'hub name', true ],
            [ 1, 'p', 'hub name', true ],
            [ 1, 'public_public_public', 'hub name', true ],

            // TRUE: various correct hub_name
            [ 1, 'public', 'h', true ],
            [ 1, 'public', 'hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub', true ],

            // FALSE: incorrect user_id (int only)
            [ 0, 'private', 'hub name', false ],
            [ -1, 'private', 'hub name', false  ],

            
            // FALSE: various incorrect hub_status (string only)
            [ 1, '', 'hub name', false ],
            [ 1, ' ', 'hub name', false ],
            [ 1, '.', 'hub name', false ],
            [ 1, '0', 'hub name', false ],
            [ 1, '0 ', 'hub name', false ],
            [ 1, 'public public', 'hub name', false ],
            [ 1, 'public_public_public_', 'hub name', false ],

            // FALSE: various incorrect hub_name (string only)
            [ 1, 'public', '', false ],
            [ 1, 'public', ' ', false ],
            [ 1, 'public', '0', false ],
            [ 1, 'public', '0 ', false ],
            [ 1, 'public', 'hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hubs', false ],

        ];
    }

    /**
     * @dataProvider addRename
     */
    public function testRename( $hub_id, $hub_name, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'public', 'Hub name');" );

        // test
        $result = $this->call( $this->hub, 'rename', [ $hub_id, $hub_name ] );
        $this->assertEquals( $expected, $result );

    }

    public function addRename() {
        return [

            // TRUE: various correct hub_name (string only)
            [ 1, 'h', true ],
            [ 1, 'hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub', true ],

            // FALSE: incorrect hub_id (int only)
            [ 0, 'hub name!', false ],
            [ -1, 'hub name!', false  ],

            // FALSE: various incorrect hub_name (string only)
            [ 1, '', false ],
            [ 1, ' ', false ],
            [ 1, '0', false ],
            [ 1, '0 ', false ],
            [ 1, 'hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hub name hubs', false ],

        ];
    }

    /**
     * @dataProvider addTrash
     */
    public function testTrash( $hub_id, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'public', 'Hub name');" );

        // test
        $result = $this->call( $this->hub, 'trash', [ $hub_id ] );
        $this->assertEquals( $expected, $result );

    }

    public function addTrash() {
        return [

            // TRUE
            [ 1, true ],

            // FALSE: incorrect hub_id
            [ 0, false ],
            [ -1, false  ],

        ];
    }

    /**
     * @dataProvider addRecover
     */
    public function testRecover( $hub_id, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'trash', 'Hub name');" );

        // test
        $result = $this->call( $this->hub, 'recover', [ $hub_id ] );
        $this->assertEquals( $expected, $result );

    }

    public function addRecover() {
        return [

            // TRUE
            [ 1, true ],

            // FALSE: incorrect hub_id
            [ 0, false ],
            [ -1, false  ],

        ];
    }

    /**
     * @dataProvider addRemove
     */
    public function testRemove( $hub_id, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'trash', 'Hub name');" );

        // test
        $result = $this->call( $this->hub, 'remove', [ $hub_id ] );
        $this->assertEquals( $expected, $result );

    }

    public function addRemove() {
        return [

            // TRUE
            [ 1, true ],

            // FALSE: incorrect hub_id
            [ 0, false ],
            [ -1, false  ],

        ];
    }

    /**
     * @dataProvider addOne
     */
    public function testOne( $hub_id, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'public', 'Hub name');" );

        // test
        $result = $this->call( $this->hub, 'one', [ $hub_id ] );
        $this->assertEquals( $expected, $result );

    }

    public function addOne() {
        return [

            // TRUE
            [ 1, true ],

            // FALSE: incorrect hub_id
            [ 0, false ],
            [ -1, false  ],

        ];
    }

}
