<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Echidna.php';
require_once __DIR__.'/../src/Echidna/User.php';

class UserTest extends TestCase
{
    private $pdo;
    private $user;

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
        $this->user = new \artabramov\Echidna\Echidna\User( $this->pdo );
    }

    protected function tearDown() : void {
        $this->db = null;
        $this->user = null;
    }

    /**
     * create_token
     */
    public function testCreateToken() {

        // is a string
        $result = $this->call( $this->user, 'create_token' );
        $this->assertIsString( $result );

        // is 80-signs length
        $result = strlen( $this->call( $this->user, 'create_token' ) ) == 80 ? true : false;
        $this->assertTrue( $result );

        // is HEX-signs only
        $result = $this->call( $this->user, 'create_token' );
        $this->assertMatchesRegularExpression( '/[a-f0-9]{80}/', $result );
    }

    /**
     * create_pass
     */
    public function testCreatePass() {

        // numeric, 4 signs (also default args)
        $result = $this->call( $this->user, 'create_pass', [ 4, '0123456789' ] );
        $this->assertMatchesRegularExpression( '/[0-9]{4}/', $result );

        // letters, 8 signs
        $result = $this->call( $this->user, 'create_pass', [ 8, 'abcdefghijklmnopqrstuvwxyz0123456789' ] );
        $this->assertMatchesRegularExpression( '/[a-z0-9]{8}/', $result );
    }

    /**
     * @dataProvider addRegister
     */
    public function testRegister( $user_email, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );

        $result = $this->call( $this->user, 'register', [ $user_email ] );
        $this->assertEquals( $expected, $result );
    }

    public function addRegister() {
        return [

            // TRUE: various user_email
            [ 'noreply@noreply.no', true ],
            [ 'noreply.1@noreply.1.no', true ],
            [ 'noreply.noreply@noreply.noreply.no', true ],
            [ 'noreply-noreply.noreply@noreply-noreply.noreply.no', true ],
            [ 'noreply_noreply.noreply@noreply_noreply.noreply.no', true ],

            // FALSE: incorrect user_email
            [ '', false ],
            [ ' ', false ],
            [ 'noreply', false ],
            [ 'noreply.no', false ],
            [ 'noreply@', false ],
            [ '@noreply', false ],
            [ '@noreply.no', false ],
            [ 'noreply@noreply', false ],
            [ 'noreply@noreply.nono', false ],

        ];
    }

    public function testRegisterTwice() {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );

        // register one user_email twice
        $result = $this->call( $this->user, 'register', [ 'noreply@noreply.no' ] );
        $this->assertTrue( $result );

        $result = $this->call( $this->user, 'register', [ 'noreply@noreply.no' ] );
        $this->assertFalse( $result );
    }

    /**
     * @dataProvider addRestore
     */
    public function testRestore( $user_email, $expected ) {

        // PREPARE: truncate table before testing and insert test dataset
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (2, '2000-01-01 00:00:00', 'trash', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', 'no@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );

        $result = $this->call( $this->user, 'restore', [ $user_email ] );
        $this->assertEquals( $expected, $result );
    }

    public function addRestore() {
        return [

            // TRUE: correct user_email
            [ 'noreply@noreply.no', true ],

            // FALSE: user_email is empty
            [ '', false ],

            // FALSE: user_email is incorrect
            [ 'noreply-noreply.no', false ],

            // FALSE: user_email not exists
            [ '_noreply@noreply.no', false ],

            // FALSE: user_status is trash
            [ 'no@no.no', false ],

        ];
    }

    /**
     * @dataProvider addSignin
     */
    public function testSignin( $user_email, $user_pass, $expected ) {

        // PREPARE: truncate table before testing and insert test dataset
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '7c4a8d09ca3762af61e59520943dc26494f8941b');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (2, '2000-01-01 00:00:00', 'trash', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', 'no@noreply.no', '7c4a8d09ca3762af61e59520943dc26494f8941b');" );

        $result = $this->call( $this->user, 'signin', [ $user_email, $user_pass ] );
        $this->assertEquals( $expected, $result );
    }

    public function addSignin() {
        return [

            // TRUE: correct user_email and correct user_pass
            [ 'noreply@noreply.no', '123456', true ],

            // FALSE: empty user_email
            [ '', '123456', false ],

            // FALSE: incorrect user_email
            [ 'noreply-noreply.no', '123456', false ],
            
            // FALSE: user_email not exists
            [ '_noreply@noreply.no', '123456', false ],

            // FALSE: user_pass is empty
            [ 'noreply@noreply.no', '', false ],

            // FALSE: incorrect user_pass
            [ 'noreply@noreply.no', '12345', false ],

            // FALSE: user_status is trash
            [ 'no@noreply.no', '123456', false ],

        ];
    }

    /**
     * @dataProvider addSignout
     */
    public function testSignout( $user_id, $expected ) {

        // truncate table users and insert datasets
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'approved', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', '1.noreply.approved@noreply.no', '7c4a8d09ca3762af61e59520943dc26494f8941b');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (2, '2000-01-01 00:00:00', 'pending',  'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f202', '2.noreply.approved@noreply.no', '7c4a8d09ca3762af61e59520943dc26494f8941b');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (3, '2000-01-01 00:00:00', 'trash',    'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f203', '3.noreply.approved@noreply.no', '7c4a8d09ca3762af61e59520943dc26494f8941b');" );

        // do test
        $result = $this->call( $this->user, 'signout', [ $user_id ] );
        $this->assertEquals( $expected, $result );

    }

    public function addSignout() {
        return [

            // TRUE: approved user
            [ 1, true ],

            // FALSE: pending user
            [ 2, false ],

            // FALSE: trashed user
            [ 3, false ],

            // FALSE: user_id is null
            [ 0, false ],

            // FALSE: user_id not exists
            [ 4, false ],

        ];
    }

    /**
     * @dataProvider addAuth
     */
    public function testAuth( $user_token, $expected ) {

        // truncate table users and insert datasets
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'approved', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', '1.noreply.approved@noreply.no', '7c4a8d09ca3762af61e59520943dc26494f8941b');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (2, '2000-01-01 00:00:00', 'pending',  'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f202', '2.noreply.approved@noreply.no', '7c4a8d09ca3762af61e59520943dc26494f8941b');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (3, '2000-01-01 00:00:00', 'trash',    'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f203', '3.noreply.approved@noreply.no', '7c4a8d09ca3762af61e59520943dc26494f8941b');" );

        // do test
        $result = $this->call( $this->user, 'auth', [ $user_token ] );
        $this->assertEquals( $expected, $result );

    }

    public function addAuth() {
        return [

            // TRUE: approved user
            [ 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', true ],

            // FALSE: pending user
            [ 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f202', false ],

            // FALSE: trashed user
            [ 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f203', false ],

            // FALSE: user_token is null
            [ 0, false ],

            // FALSE: user_token not exists
            [ 'cccce1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f203', false ],

        ];
    }

    /**
     * @dataProvider addGet
     */
    public function testGet( $user_id, $expected ) {

        // truncate table users and insert datasets
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'approved', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', '1.noreply.approved@noreply.no', '7c4a8d09ca3762af61e59520943dc26494f8941b');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (2, '2000-01-01 00:00:00', 'pending',  'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f202', '2.noreply.approved@noreply.no', '7c4a8d09ca3762af61e59520943dc26494f8941b');" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (3, '2000-01-01 00:00:00', 'trash',    'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f203', '3.noreply.approved@noreply.no', '7c4a8d09ca3762af61e59520943dc26494f8941b');" );

        // do test
        $result = $this->call( $this->user, 'get', [ $user_id ] );
        $this->assertEquals( $expected, $result );

    }

    public function addGet() {
        return [

            // TRUE: approved user
            [ 1, true ],

            // FALSE: pending user
            [ 2, true ],

            // FALSE: trashed user
            [ 3, true ],

            // FALSE: user_id is null
            [ 0, false ],

            // FALSE: user_id not exists
            [ 4, false ],

        ];
    }

}
