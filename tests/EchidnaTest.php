<?php

use PHPUnit\Framework\TestCase;

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

        $pdo_host    = 'localhost';
        $pdo_user    = 'root';
        $pdo_pass    = '123456';
        $pdo_dbase   = 'project';
        $pdo_charset = 'utf8';

        $dsn = 'mysql:host=' . $pdo_host . ';dbname=' . $pdo_dbase . ';charset=' . $pdo_charset;
        $args = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new PDO( $dsn, $pdo_user, $pdo_pass, $args );
        $this->echidna = new \artabramov\Echidna\Echidna( $this->pdo );
    }

    protected function tearDown() : void {
        $this->db = null;
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
            [ -1, false ],
            [ 0, true ],
            [ 1, false ],

            [ '', true ],
            [ ' ', true ],
            [ '0', true ],
            [ ' 0', true ],
            [ '0 ', true ],
            [ ' 0 ', true ],
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
            [ '', false ],
            [ '0', false ],
            [ '1', false ],
            [ '-1', false ],

            [ 0, true ],
            [ 1, true ],
            [ -1, true ],

            [ 999999999999999999, true ],
            [ -999999999999999999, true ],
            [ 10000000000000000000, false ],
            [ -10000000000000000000, false ],
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
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],

            [ 'a', true ],
            [ 'aa', true ],
            [ 'aa', true ],
            [ 'a1', true ],
            [ 'a_', true ],
            [ 'a-', true ],
            [ 'a.', false ],
            [ 'a,', false ],
            [ 'a ', false ],

            [ 'abcdefghijklmnopqrst', true ],
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
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],

            [ '', true ],
            [ '1', true ],
            [ '-1', true ],
            [ 'abcdefghijklmnopqrstuvwxyz0123456789 abcdefghijklmnopqrstuvwxyz0123456789 abcdefghijklmnopqrstuvwxyz0123456789 abcdefghijklmnopqrstuvwxyz0123456789 abcdefghijklmnopqrstuvwxyz0123456789 abcdefghijklmnopqrstuvwxyz0123456789 abcdefghijklmnopqrstuvwxyz0123456', true ],
            [ 'abcdefghijklmnopqrstuvwxyz0123456789 abcdefghijklmnopqrstuvwxyz0123456789 abcdefghijklmnopqrstuvwxyz0123456789 abcdefghijklmnopqrstuvwxyz0123456789 abcdefghijklmnopqrstuvwxyz0123456789 abcdefghijklmnopqrstuvwxyz0123456789 abcdefghijklmnopqrstuvwxyz01234567', false ],
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
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],
            [ date('U'), false ],

            [ '0001-01-01 01:01:01', true ],
            [ '2099-12-12 23:59:59', true ],

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
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],

            [ 'da39a3ee5e6b4b0d3255bfef95601890afd80709da39a3ee5e6b4b0d3255bfef95601890afd80719', true ],
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
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],

            [ 'da39a3ee5e6b4b0d3255bfef95601890afd80709', true ],
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
            [ 0, false ],
            [ 1, false ],
            [ -1, false ],

            [ 'noreply@noreply.no', true ],
            [ 'noreply.1@noreply.1.no', true ],
            [ 'noreply.noreply@noreply.noreply.no', true ],
            [ 'noreply-noreply.noreply@noreply-noreply.noreply.no', true ],
            [ 'noreply_noreply.noreply@noreply_noreply.noreply.no', true ],

            [ '@noreply.no', false ],
            [ 'noreply@noreply', false ],
            [ 'noreply@noreply.nono', false ],

        ];
    }

    /**
     * @dataProvider addIsInsert
     */
    public function testIsInsert( $table, $data, $expected ) {

        $result = $this->call( $this->echidna, 'is_insert', [ $table, $data ] );
        $this->assertEquals( $expected, $result );

        $id = $this->pdo->lastInsertId();
        if( $id ) {
            $stmt = $this->pdo->query( "DELETE FROM " . $table . " WHERE id=" . $id );
        }
    }

    public function addIsInsert() {

        return [ 

            // ok
            [
                'users',
                [
                    'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 
                    'user_email'  => 'noreply@noreply.no', 
                ], 
                true
            ],

            // ok
            [
                'users',
                [
                    'user_status' => 'pending', 
                    'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 
                    'user_email'  => 'noreply@noreply.no', 
                ], 
                true
            ],

            // ok
            [
                'users',
                [
                    'user_status' => 'pending', 
                    'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 
                    'user_email'  => 'noreply@noreply.no', 
                    'user_hash'   => 'cf83e1357eefb8bdf1542850d66d8007d620e405'
                ], 
                true
            ],

            // without required field
            [
                'users',
                [
                    'user_status' => 'pending', 
                    'user_email'  => 'noreply@noreply.no', 
                    'user_hash'   => 'cf83e1357eefb8bdf1542850d66d8007d620e405'
                ], 
                false
            ],


            // user_token longer than field max length
            [
                'users',
                [
                    'user_status' => 'pending', 
                    'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2001', 
                    'user_email'  => 'noreply@noreply.no', 
                    'user_hash'   => 'cf83e1357eefb8bdf1542850d66d8007d620e405'
                ], 
                false
            ],


        ];
        



    }

    /**
     * @dataProvider addIsExists
     */
    public function testIsExists( $table, $data, $expected ) {

        // insert test data
        $stmt = $this->pdo->query( "INSERT INTO " . $table . " ( user_status, user_token, user_email, user_hash ) VALUES ( 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', 'cf83e1357eefb8bdf1542850d66d8007d620e405' )" );

        // test case
        $result = $this->call( $this->echidna, 'is_exists', [ $table, $data ] );
        $this->assertEquals( $expected, $result );

        // delete test data
        $stmt = $this->pdo->query( "DELETE FROM " . $table . " WHERE user_token='cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200'" );

    }

    public function addIsExists() {

        return [ 

            // ok
            [
                'users',
                [
                    [ 'user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200' ], 
                ], 
                true
            ],

            // ok
            [
                'users',
                [
                    [ 'user_email', '=', 'noreply@noreply.no' ], 
                ], 
                true
            ],

            // ok
            [
                'users',
                [
                    [ 'user_hash', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e405' ], 
                ], 
                true
            ],

            // ok
            [
                'users',
                [
                    [ 'user_email', '=', 'noreply@noreply.no' ], 
                    [ 'user_hash', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e405' ], 
                ], 
                true
            ],

            // ok
            [
                'users',
                [
                    [ 'user_status', '=', 'pending' ], 
                    [ 'user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200' ], 
                ], 
                true
            ],

            // ok
            [
                'users',
                [
                    [ 'user_status', '<>', 'trash' ], 
                    [ 'user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200' ], 
                ], 
                true
            ],

            // false
            [
                'users',
                [
                    [ 'user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201' ], 
                ], 
                false
            ],

            //
            [
                'users',
                [
                    [ 'user_token', '<>', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201' ], 
                ], 
                true
            ],


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
     * @dataProvider addIsUpdate
     */
    public function testIsUpdate( $table, $args, $data, $expected ) {

        // insert test data
        $stmt = $this->pdo->query( "INSERT INTO " . $table . " ( user_status, user_token, user_email, user_hash ) VALUES ( 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', 'cf83e1357eefb8bdf1542850d66d8007d620e405' )" );

        $result = $this->call( $this->echidna, 'is_update', [ $table, $args, $data ] );
        $this->assertEquals( $expected, $result );

        // delete test data
        $stmt = $this->pdo->query( "DELETE FROM " . $table . " WHERE user_token='cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200'" );
    }

    public function addIsUpdate() {

        return [ 

            // ok
            [
                'users',
                [
                    ['user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200'], 
                ], 
                [
                    'user_status' => 'trash', 
                ], 
                true
            ],






        ];
        



    }

}
