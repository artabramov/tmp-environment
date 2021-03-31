<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Echidna.php';
require_once __DIR__.'/../src/Core/User.php';
require_once __DIR__.'/../src/Utils/Validator.php';

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
        $this->echidna = new \artabramov\Echidna\Echidna( $this->pdo );
        $this->user = new \artabramov\Echidna\Core\User( $this->pdo );
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
     * register
     */
    public function testRegister() {
        
        $result = $this->callMethod( $this->user, 'register', ['noreply@noreply.no'] );
        $this->assertEquals( True, $result );

        $result = $this->callMethod( $this->user, 'register', ['noreply@noreply.n'] );
        $this->assertEquals( False, $result );
    }

}