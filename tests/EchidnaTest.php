<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Models/Echidna.php';

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
     * @dataProvider addSelect
     */
    public function testSelect( $fields, $table, $args, $limit, $offset, $expected ) {

        // PREPARE: truncate table before testing and insert test dataset
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (2, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', 'noreply1@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );

        $tmp = $this->call( $this->echidna, 'select', [ $fields, $table, $args, $limit, $offset ] );
        $result = is_array( $tmp ) ? count( $tmp ) : 0;
        $this->assertEquals( $expected, $result );
    }

    public function addSelect() {

        return [ 

            // CORRECT
            [ '*', 'users', [ ['id', '=', 1], ], 1, 0, 1 ],
            [ '*', 'users', [ ['user_email', '=', 'noreply@noreply.no'] ], 1, 0, 1 ],
            [ '*', 'users', [ ['id', '=', 1], ['user_status', '=', 'pending'], ], 1, 0, 1 ],
            [ '*', 'users', [ ['id', '=', 1], ['user_status', '<>', 'trash'], ], 1, 0, 1 ],
            [ '*', 'users', [ ['user_email', '=', 'noreply@noreply.no'], ['user_status', '<>', 'trash']], 1, 0, 1 ],
            [ '*', 'users', [ ['user_status', '<>', 'trash']], 2, 0, 2 ],
            [ '*', 'users', [ ['user_status', '=', 'approved']], 1, 0, 0 ],
            [ '*', 'users', [], 1, 0, 1 ],
            [ 'id', 'users', [ ['user_status', '=', 'approved']], 1, 0, 0 ],
            [ 'date', 'users', [ ['user_status', '=', 'approved']], 1, 0, 0 ],

            // INCORRECT (no results)
            [ '*', '', [ ['user_status', '=', 'approved']], 1, 0, 0 ],
            [ '*', '_users', [ ['user_status', '=', 'approved']], 1, 0, 0 ],
            [ '*', 'users', [ ['user_name', '=', 'John Doe'], ], 1, 0, 0 ],
            [ '_id', 'users', [ ['id', '=', 1], ], 1, 0, 0 ],

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

            // 
            [
                'users',
                [], 
                True
            ],

        ];

    }

    /**
     * @dataProvider addCount
     */
    public function testCount( $table, $data, $expected ) {

        // PREPARE: truncate table before testing and insert test dataset
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (2, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', 'noreply1@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );

        // TEST
        $result = $this->call( $this->echidna, 'count', [ $table, $data ] );
        $this->assertEquals( $expected, $result );
    }

    public function addCount() {
        return [

            [ 'users', [['id', '=', 1]], 1 ],
            [ 'users', [['id', '=', 2]], 1 ],
            [ 'users', [['id', '<>', 3]], 2 ],
            [ 'users', [['id', '=', 3]], 0 ],
            [ 'users', [['_id', '=', 1]], 0 ],

        ];
    }

}