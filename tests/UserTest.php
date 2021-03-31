<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Echidna.php';
require_once __DIR__.'/../src/Core/User.php';

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

        // is string
        $this->assertIsString( $result );
        
        // is 80-signs length
        $this->assertTrue( strlen( $result ) == 80 );

        // is HEX-signs only
        $this->assertMatchesRegularExpression( '/[a-f0-9]{80}/', $result );
    }

}