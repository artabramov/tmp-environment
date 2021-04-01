<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Interfaces/Sequenceable.php';
require_once __DIR__.'/../src/Models/Echidna.php';
require_once __DIR__.'/../src/Models/User.php';
require_once __DIR__.'/../src/Services/Filter.php';

class UserTest extends TestCase
{
    private $pdo;
    private $user;

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

    public function getProperty($object, $property) {
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
    public function testRegister( $user_email, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".users;" );

        $result = $this->callMethod( $this->user, 'register', [ $user_email ] );
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

}