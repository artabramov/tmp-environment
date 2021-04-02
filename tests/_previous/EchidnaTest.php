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
     * @dataProvider addIsEmpty
     */
    public function testIsEmpty( $value, $expected ) {
        $result = $this->call( $this->echidna, 'is_empty', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsEmpty() {
        return [

            // TRUE: various empty values
            [ '', true ],
            [ ' ', true ],
            [ '0', true ],
            [ ' 0', true ],
            [ '0 ', true ],
            [ ' 0 ', true ],
            [ 0, true ],

            // FALSE: various not empty values
            [ 1, false ],
            [ -1, false ],
            [ '1', false ],
            [ ' 1', false ],
            [ '1 ', false ],
            [ ' 1 ', false ],
        ];
    }

    /**
     * @dataProvider addIsId
     */
    public function testIsId( $value, $expected ) {
        $result = $this->call( $this->echidna, 'is_id', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsId() {
        return [

            // TRUE: various correct values (int|string)
            [ 0, true ],
            [ 1, true ],
            [ 9223372036854775807, true ],
 
            // FALSE: various not correct values (int|string)
            [ -1, false ],
            [ '', false ],
            [ '0', false ],
            [ '1', false ],
            [ '-1', false ],
            
        ];
    }

    /**
     * @dataProvider addIsKey
     */
    public function testIsKey( $value, $expected ) {
        $result = $this->call( $this->echidna, 'is_key', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsKey() {
        return [

            // TRUE: variuous correct values (int|string)
            [ 'a', true ],
            [ 'abcdefghijklmnopqrst', true ],
            [ 'abcde_fghij_klmno_pq', true ],
            [ 'abcde-fghij-klmno-pq', true ],
            [ 'abcde-1-fghij-2-klmn', true ],

            // FALSE: various incorrect values (int|string)
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],
            [ 'abcde fghij klmno pq', false ],
            [ 'abcde,fghij.klmno+pq', false ],
            [ 'abcdefghijklmnopqrstu', false ],
        ];
    }

    /**
     * @dataProvider addIsValue
     */
    public function testIsValue( $value, $expected ) {
        $result = $this->call( $this->echidna, 'is_value', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsValue() {
        return [

            // TRUE: variuous correct values (int|string)
            [ '', true ],
            [ ' ', true ],
            [ '0', true ],
            [ '1', true ],
            [ '-1', true ],
            [ 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor i', true ],

            // FALSE: various incorrect values (int|string)
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],
            [ 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in', false ],
        ];
    }

    /**
     * @dataProvider addIsDatetime
     */
    public function testIsDatetime( $value, $expected ) {
        $result = $this->call( $this->echidna, 'is_datetime', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsDatetime() {
        return [

            // TRUE: variuous correct values (int|string)
            [ '0001-01-01 01:01:01', true ],
            [ '2099-12-12 23:59:59', true ],

            // FALSE: various incorrect values (int|string)
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],
            [ '0000-00-00 00:00:00', false ],
            [ '1970-00-00 00:00:00', false ],
            [ '2021-13-01 00:00:00', false ],
            [ '2021-01-32 00:00:00', false ],
            [ '2021-01-01 25:00:00', false ],
            [ '2021-01-01 00:60:00', false ],
            [ '2021-01-01 00:00:60', false ],
            [ '2021-02-29 00:00:00', false ],
            [ '2021-02-30 00:00:00', false ],
            [ '2021-02-31 00:00:00', false ],
            [ '2021-04-31 00:00:00', false ],
            [ 'yyyy-mm-dd hh:mm:ss', false ],
        ];
    }

    /**
     * @dataProvider addIsToken
     */
    public function testIsToken( $value, $expected ) {
        $result = $this->call( $this->echidna, 'is_token', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsToken() {
        return [

            // TRUE: correct value (int|string)
            [ 'da39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd80719', true ],

            // FALSE: various incorrect values (int|string)
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],            
            [ 'da39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd807190', false ],
            [ 'da39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd8071', false ],
            [ 'ga39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd8071', false ],
        ];
    }

    /**
     * @dataProvider addIsHash
     */
    public function testIsHash( $value, $expected ) {
        $result = $this->call( $this->echidna, 'is_hash', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsHash() {
        return [

            // TRUE: variuous correct values (int|string)
            [ 'da39a3ee5e6b4b0d3255bfef95601890afd80709', true ],

            // FALSE: various incorrect values (int|string)
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],            
            [ 'da39a3ee5e6b4b0d3255bfef95601890afd8070', false ],
            [ 'da39a3ee5e6b4b0d3255bfef95601890afd807091', false ],
            [ 'ga39a3ee5e6b4b0d3255bfef95601890afd80709', false ],
        ];
    }

    /**
     * @dataProvider addIsEmail
     */
    public function testIsEmail( $value, $expected ) {
        $result = $this->call( $this->echidna, 'is_email', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsEmail() {
        return [

            // TRUE: variuous correct values (int|string)
            [ 'noreply@noreply.no', true ],
            [ 'noreply.1@noreply.1.no', true ],
            [ 'noreply.noreply@noreply.noreply.no', true ],
            [ 'noreply-noreply.noreply@noreply-noreply.noreply.no', true ],
            [ 'noreply_noreply.noreply@noreply_noreply.noreply.no', true ],

            // FALSE: various incorrect values (int|string)
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],
            [ '@noreply.no', false ],
            [ 'noreply@noreply', false ],
            [ 'noreply@noreply.nono', false ],

        ];
    }

    /**
     * @dataProvider addInsert
     */
    public function testInsert( $table, $data, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );

        // test
        $result = $this->call( $this->echidna, 'insert', [ $table, $data ] );
        $this->assertEquals( $expected, $result );
    }

    public function addInsert() {

        return [ 

            // 1: correct data, full dataset
            [ 
                'users', [
                'id'          => 1,
                'date'        => date('Y-m-d H:i:s'),
                'user_status' => 'user_status',
                'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 
                'user_email'  => 'noreply@noreply.no',
                'user_hash'   =>  '1542850d66d8007d620e4050b5715dc83f4a921d', 
                ], 1
            ],

            // 1: correct data, not full dataset
            [ 
                'users', [
                'date'        => date('Y-m-d H:i:s'),
                'user_status' => 'user_status',
                'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 
                'user_email'  => 'noreply@noreply.no',
                'user_hash'   =>  '1542850d66d8007d620e4050b5715dc83f4a921d', 
                ], 1
            ],

            // 1: correct data, not full dataset
            [ 
                'users', [
                'user_status' => 'user_status',
                'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 
                'user_email'  => 'noreply@noreply.no',
                'user_hash'   =>  '1542850d66d8007d620e4050b5715dc83f4a921d', 
                ], 1
            ],

            // 1: correct data, not full dataset
            [ 
                'users', [
                'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 
                'user_email'  => 'noreply@noreply.no',
                'user_hash'   =>  '1542850d66d8007d620e4050b5715dc83f4a921d', 
                ], 1
            ],

            // 1: correct data, not full dataset (only UNIQUE KEY fields)
            [ 
                'users', [
                'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 
                'user_email'  => 'noreply@noreply.no',
                ], 1
            ],

            // FALSE: without required field (UNIQUE KEY user_token)
            [ 
                'users', [
                'user_status' => 'user_status',
                'user_email'  => 'noreply@noreply.no',
                'user_hash'   =>  '1542850d66d8007d620e4050b5715dc83f4a921d', 
                ], false 
            ],

            // FALSE: without table
            [ 
                '', [
                'user_status' => 'user_status',
                'user_email'  => 'noreply@noreply.no',
                'user_hash'   =>  '1542850d66d8007d620e4050b5715dc83f4a921d', 
                ], false 
            ],

            // FALSE: with incorrect table name (_users)
            [ 
                '_users', [
                'user_status' => 'user_status',
                'user_email'  => 'noreply@noreply.no',
                'user_hash'   =>  '1542850d66d8007d620e4050b5715dc83f4a921d', 
                ], false 
            ],

            // FALSE: without dataset
            [ 'users', [], false ],

            // FALSE: with incorrect field name (user_name)
            [ 
                'users', [
                'user_name'  => 'John Doe',
                'user_token' => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 
                'user_email' => 'noreply@noreply.no',
                'user_hash'  =>  '1542850d66d8007d620e4050b5715dc83f4a921d', 
                ], false 
            ],

            // FALSE: with field length bigger than maximum (user_status)
            [ 
                'users', [
                'user_status' => 'user_status_user_stat',
                'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 
                'user_email'  => 'noreply@noreply.no',
                'user_hash'   =>  '1542850d66d8007d620e4050b5715dc83f4a921d', 
                ], false
            ],

        ];

    }

    public function testInsertTwice() {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );

        // insert one UNIQUE KEY attribute twice
        $result = $this->call( $this->echidna, 'insert', [ 'users', [ 'user_token' => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'user_email' => 'noreply@noreply.no' ]] );
        $this->assertEquals( 1, $result );

        $result = $this->call( $this->echidna, 'insert', [ 'users', [ 'user_token' => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'user_email' => 'noreply@noreply.no' ]] );
        $this->assertFalse( $result );
    }

    /**
     * @dataProvider addUpdate
     */
    public function testUpdate( $table, $args, $data, $expected ) {

        // PREPARE: truncate table before testing and insert test dataset
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );

        $result = $this->call( $this->echidna, 'update', [ $table, $args, $data ] );
        $this->assertEquals( $expected, $result );
    }

    public function addUpdate() {

        return [ 

            // 1: update all dataset by id
            [
                'users', [
                    ['id', '=', 1], 
                ], [
                    'date'        => date('Y-m-d H:i:s'),
                    'user_status' => 'user_status',
                    'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', 
                    'user_email'  => 'no@no.no',
                    'user_hash'   =>  '1542850d66d8007d620e4050b5715dc83f4a921f', 
                ], 1
            ],

            // 1: update part of dataset by some fields
            [
                'users', [
                    ['id', '=', 1], 
                    ['user_status', '=', 'pending'], 
                ], [
                    'date'        => date('Y-m-d H:i:s'),
                    'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', 
                    'user_email'  => 'no@no.no',
                    'user_hash'   =>  '1542850d66d8007d620e4050b5715dc83f4a921f', 
                ], 1
            ],

            // 1: update part of dataset by some fields
            [
                'users', [
                    ['id', '=', 1], 
                    ['user_status', '<>', 'trash'], 
                ], [
                    'date'        => date('Y-m-d H:i:s'),
                    'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', 
                    'user_email'  => 'no@no.no',
                    'user_hash'   =>  '1542850d66d8007d620e4050b5715dc83f4a921f', 
                ], 1
            ],
            
            // 0: update field to his old value
            [
                'users', 
                [ ['id', '=', 1] ], 
                [ 'user_status' => 'pending' ], 
                0
            ],
            
            // 0: update not existing row
            [
                'users', 
                [ ['id', '=', 2] ], 
                [ 'user_status' => 'trash' ], 
                0
            ],
            
            // 0: empty table name
            [
                '', 
                [ ['id', '=', 1] ], 
                [ 'user_status' => 'trash' ], 
                0
            ],

            // 0: incorrect table name
            [
                '_users', 
                [ ['id', '=', 1] ], 
                [ 'user_status' => 'trash' ], 
                0
            ],

            // 0: empty dataset
            [
                'users', 
                [ ['id', '=', 1] ], 
                [], 
                0
            ],

            // 0: incorrect field in dataset
            [
                'users',
                [ ['id', '=', 1], ], 
                [ 'user_name' => 'Jogn Doe'], 
                0
            ],

            // 0: field longer than maximum length
            [
                'users', 
                [ ['id', '=', 1] ], 
                [ 'user_status' => 'user_ststus_user_stst' ], 
                0
            ],

        ];

    }

    /**
     * @dataProvider addIsExists
     */
    public function testIsExists( $table, $data, $expected ) {

        // PREPARE: truncate table before testing and insert test dataset
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );

        // TEST
        $result = $this->call( $this->echidna, 'is_exists', [ $table, $data ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsExists() {
        return [

            // TRUE: entry exists (also empty dataset)
            [ 'users', [], true ],
            [ 'users', [['id', '=', 1]], true ],
            [ 'users', [['id', '=', 1], ['date', '=', '2000-01-01 00:00:00']], true ],
            [ 'users', [['user_email', '=', 'noreply@noreply.no'], ['user_hash', '=', '1542850d66d8007d620e4050b5715dc83f4a921d'], ['user_status', '<>', 'trash']], true ],
            [ 'users', [['id', '=', 1], ['date', '>', '1990-01-01 00:00:00']], true ],

            // FALSE: entry not found
            [ 'users', [['id', '=', 2]], false ],

            // FALSE: empty table name
            [ '', [['id', '=', 1], ['date', '=', '2000-01-01 00:00:00']], false ],

            // FALSE: incorrect table name
            [ '_users', [['id', '=', 1], ['date', '=', '2000-01-01 00:00:00']], false ],

            // FALSE: incorrect field in dataset
            [ '_users', [['id', '=', 1], ['user_name', '=', 'John Doe']], false ],

        ];
    }

    /**
     * get_time
     */
    public function testGetTime() {

        // is a string
        $result = $this->call( $this->echidna, 'get_time' );
        $this->assertIsString( $result );

        // is not empty date
        $result = $this->call( $this->echidna, 'get_time' );
        $this->assertNotEquals( '0000-00-00 00:00:00', $result );

        // is dateteime-format
        $result = $this->call( $this->echidna, 'get_time' );
        $this->assertMatchesRegularExpression( "/^\d{4}-((0[0-9])|(1[0-2]))-(([0-2][0-9])|(3[0-1])) (([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/", $result );
    }

    /**
     * @dataProvider addSelect
     */
    public function testSelect( $table, $args, $limit, $offset, $expected ) {

        // PREPARE: truncate table before testing and insert test dataset
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (2, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', 'noreply1@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );

        $tmp = $this->call( $this->echidna, 'select', [ $table, $args, $limit, $offset ] );
        $result = is_array( $tmp ) ? count( $tmp ) : 0;
        $this->assertEquals( $expected, $result );
    }

    public function addSelect() {

        return [ 

            // CORRECT: select 1 row
            [
                'users',
                [ ['id', '=', 1], ], 
                1, 0,
                1
            ],

            // CORRECT: select 1 row
            [
                'users',
                [ ['user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200'], ], 
                1, 0,
                1
            ],

            // CORRECT: select 1 row
            [
                'users',
                [ ['id', '=', 1], ['user_status', '=', 'pending'], ], 
                1, 0,
                1
            ],

            // CORRECT: select 1 row
            [
                'users',
                [ ['id', '=', 1], ['user_status', '<>', 'trash'], ], 
                1, 0,
                1
            ],

            // CORRECT: select 1 row
            [
                'users',
                [ ['user_email', '=', 'noreply@noreply.no'], ['user_hash', '=' ,'1542850d66d8007d620e4050b5715dc83f4a921d'], ['user_status', '<>', 'trash']], 
                1, 0,
                1
            ],

            // CORRECT: select 2 rows
            [
                'users',
                [ ['user_status', '<>', 'trash']], 
                2, 0,
                2
            ],

            // CORRECT: 0 rows
            [
                'users',
                [ ['user_status', '=', 'approved']], 
                1, 0,
                0
            ],

            // INCORRECT: empty table
            [
                '',
                [ ['user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200'], ], 
                1, 0,
                0
            ],

            // INCORRECT: incorrect table name
            [
                '_users',
                [ ['user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200'], ], 
                1, 0,
                0
            ],

            // INCORRECT: incorrect field name
            [
                'users',
                [ ['user_name', '=', 'John Doe'], ], 
                1, 0,
                0
            ],

            // INCORRECT: empty args
            [
                'users',
                [], 
                1, 0,
                0
            ],

        ];
    }

    /**
     * @dataProvider addDelete
     */
    public function testDelete( $table, $args, $expected ) {

        // PREPARE: truncate table before testing and insert test dataset
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );

        $result = $this->call( $this->echidna, 'delete', [ $table, $args ] );
        $this->assertEquals( $expected, $result );

    }

    public function addDelete() {

        return [ 


            // CORRECT: delete 1 row
            [
                'users',
                [ ['id', '=', 1], ], 
                True
            ],

            // CORRECT: delete 1 row
            [
                'users',
                [ ['user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200'], ], 
                True
            ],

            // CORRECT: delete 1 row
            [
                'users',
                [ ['id', '=', 1], ['user_status', '=', 'pending'], ], 
                True
            ],

            // CORRECT: delete 1 row
            [
                'users',
                [ ['id', '=', 1], ['user_status', '<>', 'trash'], ], 
                True
            ],

            // CORRECT: delete 1 row
            [
                'users',
                [ ['user_email', '=', 'noreply@noreply.no'], ['user_hash', '=' ,'1542850d66d8007d620e4050b5715dc83f4a921d'], ['user_status', '<>', 'trash']], 
                True
            ],

            // INCORRECT: empty table
            [
                '',
                [ ['user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200'], ], 
                False
            ],

            // INCORRECT: incorrect table name
            [
                '_users',
                [ ['user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200'], ], 
                False
            ],

            // INCORRECT: incorrect field name
            [
                'users',
                [ ['user_name', '=', 'John Doe'], ], 
                False
            ],

            // INCORRECT: empty args
            [
                'users',
                [], 
                False
            ],

        ];

    }

}
