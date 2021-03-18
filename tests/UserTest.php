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


}
