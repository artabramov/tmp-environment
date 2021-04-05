<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Interfaces/Sequenceable.php';
require_once __DIR__.'/../src/Models/Echidna.php';
require_once __DIR__.'/../src/Models/User.php';
require_once __DIR__.'/../src/Utilities/Filter.php';

class UserTest extends TestCase
{
    private $pdo;
    private $user;

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
        $this->user = new \artabramov\Echidna\Models\User( $this->pdo );
    }

    protected function tearDown() : void {
        $this->pdo = null;
        $this->echidna = null;
        $this->user = null;
    }

    /**
     * set_token
     */
    public function testSetToken() {
        
        $this->callMethod( $this->user, 'set_token' );
        $result = $this->getProperty($this->user, 'user_token');

        // is correct
        $this->assertMatchesRegularExpression( '/[a-f0-9]{80}/', $result );
    }

    /**
     * set_pass
     */
    public function testSetPass() {
        
        $this->callMethod( $this->user, 'set_pass', ['abcddefghijklmnopqrstuvwxyz0123456789', 6] );
        $result = $this->getProperty($this->user, 'user_pass');

        // is correct
        $this->assertMatchesRegularExpression( '/[a-z0-9]{6}/', $result );
    }

    /**
     * set_hash
     */
    public function testSetHash() {
        
        $this->callMethod( $this->user, 'set_hash' );
        $result = $this->getProperty($this->user, 'user_hash');

        // is correct
        $this->assertMatchesRegularExpression( '/[a-f0-9]{40}/', $result );
    }

    /**
     * @dataProvider addRegister
     */
    public function testRegister( $email, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );

        $result = $this->callMethod( $this->user, 'register', [ $email ] );
        $user_id = $this->getProperty( $this->user, 'id' );
        $user_status = $this->getProperty( $this->user, 'user_status' );
        $user_token = $this->getProperty( $this->user, 'user_token' );
        $user_email = $this->getProperty( $this->user, 'user_email' );
        $user_hash = $this->getProperty( $this->user, 'user_hash' );
        $error = $this->getProperty( $this->user, 'error' );

        if( $result == true ) {
            $this->assertEquals( $user_id, 1 );
            $this->assertEquals( $user_status, 'pending' );
            $this->assertMatchesRegularExpression( '/[a-f0-9]{80}/', $user_token );
            $this->assertEquals( $user_email, $email );
            $this->assertEquals( $user_hash, '' );
            $this->assertEmpty( $error );
            $this->assertTrue( $result );

        } else {
            $this->assertEquals( $user_id, null );
            $this->assertEquals( $user_status, null );
            $this->assertEquals( $user_token, null );
            $this->assertEquals( $user_email, null );
            $this->assertEquals( $user_hash, null );
            $this->assertNotEmpty( $error );
            $this->assertFalse( $result );
        }
    }

    public function addRegister() {
        return [

            // correct cases
            [ 'noreply@noreply.no', true ],
            [ 'noreply.1@noreply.1.no', true ],
            [ 'noreply.noreply@noreply.noreply.no', true ],
            [ 'noreply-noreply.noreply@noreply-noreply.noreply.no', true ],
            [ 'noreply_noreply.noreply@noreply_noreply.noreply.no', true ],

            // incorrect cases
            [ '', false ],
            [ ' ', false ],
            [ '0', false ],
            [ '0 ', false ],
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

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );

        $result = $this->callMethod( $this->user, 'register', [ 'noreply@noreply.no' ] );
        $this->assertTrue( $result );

        $result = $this->callMethod( $this->user, 'register', [ 'noreply@noreply.no' ] );
        $this->assertFalse( $result );
    }

    /**
     * @dataProvider addRestore
     */
    public function testRestore( $user_email, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );

        $result = $this->callMethod( $this->user, 'restore', [ $user_email, 6, '0123456789' ] );
        $user_pass = $this->getProperty( $this->user, 'user_pass' );
        $user_hash = $this->getProperty( $this->user, 'user_hash' );
        $error = $this->getProperty( $this->user, 'error' );

        if( $result ) {
            $this->assertMatchesRegularExpression( '/[0-9]{6}/', $user_pass );
            $this->assertMatchesRegularExpression( '/[a-f0-9]{40}/', $user_hash );
            $this->assertEmpty( $error );
            $this->assertTrue( $result );

        } else {
            $this->assertEquals( $user_pass, null );
            $this->assertEquals( $user_hash, null );
            $this->assertNotEmpty( $error );
            $this->assertFalse( $result );
        }
    }

    public function addRestore() {
        return [

            // correct case
            [ 'noreply@noreply.no', true ],

            // incorrect cases (email correct, but not exists)
            [ 'noreply.1@noreply.1.no', true ],
            [ 'noreply.noreply@noreply.noreply.no', true ],
            [ 'noreply-noreply.noreply@noreply-noreply.noreply.no', true ],
            [ 'noreply_noreply.noreply@noreply_noreply.noreply.no', true ],

            // incorrect cases
            [ '', false ],
            [ ' ', false ],
            [ '0', false ],
            [ '0 ', false ],
            [ 'noreply', false ],
            [ 'noreply.no', false ],
            [ 'noreply@', false ],
            [ '@noreply', false ],
            [ '@noreply.no', false ],
            [ 'noreply@noreply', false ],
            [ 'noreply@noreply.nono', false ],

        ];
    }

    /**
     * @dataProvider addSignin
     */
    public function testSignin( $email, $pass, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );

        $result = $this->callMethod( $this->user, 'signin', [ $email, $pass ] );

        $id = $this->getProperty( $this->user, 'id' );
        $date = $this->getProperty( $this->user, 'date' );
        $user_email = $this->getProperty( $this->user, 'user_email' );
        $user_pass = $this->getProperty( $this->user, 'user_pass' );
        $user_hash = $this->getProperty( $this->user, 'user_hash' );
        $error = $this->getProperty( $this->user, 'error' );

        if( $result ) {
            $this->assertEquals( $id, 1 );
            $this->assertEquals( $date, '2000-01-01 00:00:00' );
            $this->assertEquals( $user_email, $email );
            $this->assertEquals( $user_pass, '123456' );
            $this->assertEquals( $user_hash, '' );
            $this->assertEmpty( $error );
            $this->assertTrue( $result );

        } else {
            $this->assertEquals( $id, null );
            $this->assertEquals( $date, null );
            $this->assertEquals( $user_email, null );
            $this->assertEquals( $user_pass, null );
            $this->assertEquals( $user_hash, null );
            $this->assertNotEmpty( $error );
            $this->assertFalse( $result );
        }
    }

    public function addSignin() {
        return [

            // correct case
            [ 'noreply@noreply.no', '123456', true ],

            // incorrect case
            [ '_noreply@noreply.no', '123456', false ],
        ];
    }

    /**
     * @dataProvider addSignout
     */
    public function testSignout( $user_id, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );

        $result = $this->callMethod( $this->user, 'signout', [ $user_id ] );
        $error = $this->getProperty( $this->user, 'error' );

        if( $result ) {
            $this->assertEmpty( $error );
            $this->assertTrue( $result );

        } else {
            $this->assertNotEmpty( $error );
            $this->assertFalse( $result );
        }
    }

    public function addSignout() {
        return [

            // correct case
            [ 1, true ],

            // incorrect cases
            [ 0, false ],
            [ -1, false ],
            [ 2, false ],
            [ 'a', false ],
        ];
    }

    /**
     * @dataProvider addAuth
     */
    public function testAuth( $token, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );

        $result = $this->callMethod( $this->user, 'auth', [ $token ] );

        $id = $this->getProperty( $this->user, 'id' );
        $date = $this->getProperty( $this->user, 'date' );
        $user_status = $this->getProperty( $this->user, 'user_status' );
        $user_token = $this->getProperty( $this->user, 'user_token' );
        $user_email = $this->getProperty( $this->user, 'user_email' );
        $user_hash = $this->getProperty( $this->user, 'user_hash' );
        $error = $this->getProperty( $this->user, 'error' );

        if( $result ) {
            $this->assertEquals( $id, 1 );
            $this->assertEquals( $date, '2000-01-01 00:00:00' );
            $this->assertEquals( $user_status, 'pending' );
            $this->assertEquals( $user_token, 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200' );
            $this->assertEquals( $user_email, 'noreply@noreply.no' );
            $this->assertEquals( $user_hash, '1542850d66d8007d620e4050b5715dc83f4a921d' );
            $this->assertEmpty( $error );
            $this->assertTrue( $result );

        } else {
            $this->assertEquals( $id, null );
            $this->assertEquals( $date, null );
            $this->assertEquals( $user_status, null );
            $this->assertEquals( $user_token, null );
            $this->assertEquals( $user_email, null );
            $this->assertEquals( $user_hash, null );
            $this->assertNotEmpty( $error );
            $this->assertFalse( $result );
        }
    }

    public function addAuth() {
        return [

            // correct case
            [ 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', true ],

            // incorrect case
            [ 1, false ],
            [ '', false ],
            [ 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', false ],
        ];
    }

    /**
     * @dataProvider addGetone
     */
    public function testGetone( $user_id, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );
        $stmt = $this->pdo->query( "INSERT INTO " . PDO_DBASE . ".users (id, date, user_status, user_token, user_email, user_hash) VALUES (1, '2000-01-01 00:00:00', 'pending', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 'noreply@noreply.no', '1542850d66d8007d620e4050b5715dc83f4a921d');" );

        $result = $this->callMethod( $this->user, 'getone', [ $user_id ] );

        $id = $this->getProperty( $this->user, 'id' );
        $date = $this->getProperty( $this->user, 'date' );
        $user_status = $this->getProperty( $this->user, 'user_status' );
        $user_token = $this->getProperty( $this->user, 'user_token' );
        $user_email = $this->getProperty( $this->user, 'user_email' );
        $user_hash = $this->getProperty( $this->user, 'user_hash' );
        $error = $this->getProperty( $this->user, 'error' );

        if( $result ) {
            $this->assertEquals( $id, 1 );
            $this->assertEquals( $date, '2000-01-01 00:00:00' );
            $this->assertEquals( $user_status, 'pending' );
            $this->assertEquals( $user_token, 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200' );
            $this->assertEquals( $user_email, 'noreply@noreply.no' );
            $this->assertEquals( $user_hash, '1542850d66d8007d620e4050b5715dc83f4a921d' );
            $this->assertEmpty( $error );
            $this->assertTrue( $result );

        } else {
            $this->assertEquals( $id, null );
            $this->assertEquals( $date, null );
            $this->assertEquals( $user_status, null );
            $this->assertEquals( $user_token, null );
            $this->assertEquals( $user_email, null );
            $this->assertEquals( $user_hash, null );
            $this->assertNotEmpty( $error );
            $this->assertFalse( $result );
        }
    }

    public function addGetone() {
        return [

            // correct case
            [ 1, true ],

            // incorrect case
            [ 0, false ],
            [ 2, false ],
            [ '', false ],
            [ 'a', false ],
        ];
    }

}
