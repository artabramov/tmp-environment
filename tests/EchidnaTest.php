<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Models/Echidna.php';

class EchidnaTest extends TestCase
{
    private $pdo;
    private $echidna;

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
        $this->echidna = new \artabramov\Echidna\Models\Echidna( $this->pdo );
    }

    protected function tearDown() : void {
        $this->pdo = null;
        $this->echidna = null;
    }

    /**
     * @dataProvider addInsert
     */
    public function testInsert( $table, $data, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $result = $this->callMethod( $this->echidna, 'insert', [ $table, $data ] );
        $this->assertEquals( $expected, $result );
    }

    public function addInsert() {

        return [ 

            // correct cases
            [ 'hubs', [ 'id' => 1, 'date' => date('Y-m-d H:i:s'), 'user_id' => 1, 'hub_status' => 'public', 'hub_name' => 'hub name' ], 1 ],
            [ 'hubs', [ 'user_id' => 0, 'hub_status' => '', 'hub_name' => '' ], 1 ],

            // incorrect cases
            [ '_hubs', [ 'user_id' => 1, 'hub_status' => 'public', 'hub_name' => 'noname' ], false ],
            [ '', [ 'user_id' => 1, 'hub_status' => 'public', 'hub_name' => 'noname' ], false ],
            [ 'hubs', [ '_user_id' => 1, 'hub_status' => 'public', 'hub_name' => 'noname' ], false ],
            [ 'hubs', [ 'user_id' => 1, 'hub_status' => 'public_public_public_', 'hub_name' => 'noname' ], false ],
            [ 'hubs', [], false ],

        ];
    }

    public function testInsertTwice() {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );

        // correct case
        $result = $this->callMethod( $this->echidna, 'insert', [ 'users', [ 'user_token' => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'user_email' => 'noreply@noreply.no' ]] );
        $this->assertEquals( 1, $result );

        // again correct case (false)
        $result = $this->callMethod( $this->echidna, 'insert', [ 'users', [ 'user_token' => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'user_email' => 'noreply@noreply.no' ]] );
        $this->assertEquals( false, $result );
    }

    /**
     * @dataProvider addUpdate
     */
    public function testUpdate( $table, $args, $data, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'public', 'noname');" );

        $result = $this->callMethod( $this->echidna, 'update', [ $table, $args, $data ] );
        $this->assertEquals( $expected, $result );
    }

    public function addUpdate() {

        return [ 

            // correct cases
            [ 'hubs', [[ 'id', '=', 1 ]], [ 'date' => date('Y-m-d H:i:s') ], true ],
            [ 'hubs', [[ 'id', '=', 1 ]], [ 'user_id' => 1 ], true ],
            [ 'hubs', [[ 'id', '=', 1 ]], [ 'user_id' => 2 ], true ],
            [ 'hubs', [[ 'id', '=', 1 ]], [ 'hub_status' => 'private' ], true ],
            [ 'hubs', [[ 'id', '=', 2 ]], [ 'hub_status' => 'private' ], true ],
            [ 'hubs', [[ 'id', '=', 1 ]], [ 'hub_name' => 'hub name' ], true ],
            [ 'hubs', [[ 'id', '=', 1 ]], [ 'hub_status' => 'private', 'hub_name' => 'hub name' ], true ],
            [ 'hubs', [[ 'id', '=', 1 ], [ 'hub_status', '=', 'public' ]], [ 'user_id' => 2 ], true ],
            [ 'hubs', [[ 'id', '=', 1 ], [ 'hub_status', '<>', 'trash' ]], [ 'user_id' => 2 ], true ],

            // incorrect cases
            [ '', [[ 'id', '=', 1 ]], [ 'hub_status' => 'private' ], false ],
            [ '_hubs', [[ 'id', '=', 1 ]], [ 'hub_status' => 'private' ], false ],
            [ 'hubs', [[ '_id', '=', 1 ]], [ 'hub_status' => 'private' ], false ],
            [ 'hubs', [[ 'id', '=', 1 ]], [ '_hub_status' => 'private' ], false ],
            [ 'hubs', [[ 'id', '=', 1 ]], [ 'hub_status' => 'private_private_priva' ], false ],

        ];
        

    }

    /**
     * @dataProvider addSelect
     */
    public function testSelect( $fields, $table, $args, $limit, $offset, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'public', 'noname 1');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (2, '2000-01-01 00:00:00', 1, 'private', 'noname 2');" );

        $tmp = $this->callMethod( $this->echidna, 'select', [ $fields, $table, $args, $limit, $offset ] );
        $result = is_array( $tmp ) ? count( $tmp ) : $tmp;
        $this->assertEquals( $expected, $result );
    }

    public function addSelect() {

        return [ 

            // correct cases
            [ '*', 'hubs', [ ['id', '=', 1], ], 10, 0, 1 ],
            [ '*', 'hubs', [ ['user_id', '=', 1], ], 10, 0, 2 ],
            [ '*', 'hubs', [ ['user_id', '=', 1], ['hub_status', '=', 'public']], 10, 0, 1 ],
            [ '*', 'hubs', [ ['user_id', '=', 1], ['hub_status', '<>', 'trash']], 10, 0, 2 ],
            [ '*', 'hubs', [ ['user_id', '=', 2], ], 10, 0, 0 ],
            [ '*', 'hubs', [ ['_user_id', '=', 2], ], 10, 0, 0 ],
            [ '*', 'hubs', [ ['hub_status', '=', 'public_public_public_']], 10, 0, 0 ],
            [ '*', 'hubs', [], 10, 0, 2 ],

            // incorrect cases
            [ '*', '', [ ['id', '=', 1], ], 10, 0, false ],
            [ '*', '_hubs', [ ['id', '=', 1], ], 10, 0, false ],
            [ '*', 'hubs', [ ['_id', '=', 1], ], 10, 0, false ],

        ];
    }

    /**
     * @dataProvider addDelete
     */
    public function testDelete( $table, $args, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'public', 'noname');" );

        $result = $this->callMethod( $this->echidna, 'delete', [ $table, $args ] );
        $this->assertEquals( $expected, $result );

    }

    public function addDelete() {

        return [ 

            // correct cases
            [ 'hubs', [], true ], // all rows deleted
            [ 'hubs', [[ 'id', '=', 1 ]], true ], // one row deleted
            [ 'hubs', [[ 'id', '=', 2 ]], true ], // no rows deleted
            [ 'hubs', [[ 'user_id', '=', 1 ]], true ],
            [ 'hubs', [[ 'user_id', '=', 1 ], [ 'hub_status', '=', 'public' ]], true ],
            [ 'hubs', [[ 'user_id', '=', 1 ], [ 'hub_status', '<>', 'trash' ]], true ],
            [ 'hubs', [[ 'hub_status', '=', 'public_public_public_' ]], true ], // no rows

            // incorrect cases
            [ '', [[ 'id', '=', 1 ]], false ],
            [ '_hubs', [[ 'id', '=', 1 ]], false ],
            [ 'hubs', [[ '_id', '=', 1 ]], false ],

        ];

    }

    /**
     * @dataProvider addCount
     */
    public function testCount( $table, $data, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".hubs;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (1, '2000-01-01 00:00:00', 1, 'public', 'noname 1');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".hubs (id, date, user_id, hub_status, hub_name) VALUES (2, '2000-01-01 00:00:00', 1, 'private', 'noname 2');" );

        $result = $this->callMethod( $this->echidna, 'count', [ $table, $data ] );
        $this->assertEquals( $expected, $result );
    }

    public function addCount() {
        return [

            [ 'hubs', [['id', '=', 1]], 1 ],
            [ 'hubs', [['id', '=', 2]], 1 ],
            [ 'hubs', [['id', '<>', 3]], 2 ],
            [ 'hubs', [['id', '=', 3]], 0 ],
            [ 'hubs', [['_id', '=', 1]], 0 ],
            [ '_hubs', [['id', '=', 1]], 0 ],
            [ '', [['id', '=', 1]], 0 ],
            [ 'hubs', [], 2 ],

        ];
    }

    // datetime
    public function testDatetime() {
        $result = $this->callMethod( $this->echidna, 'datetime', [] );
        $this->assertMatchesRegularExpression( "/^\d{4}-((0[0-9])|(1[0-2]))-(([0-2][0-9])|(3[0-1])) (([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/", $result );
    }

}