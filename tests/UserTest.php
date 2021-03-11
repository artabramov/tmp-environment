<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../src/User.php';

class UserTest extends TestCase
{
    private $db;
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

        $this->db = new \PDO( 'mysql:host=localhost;dbname=project', 'root', '123456' );
        $this->user = new \artabramov\Echidna\User( $this->db );
    }

    protected function tearDown() : void {
        $this->db = null;
        $this->user = null;
    }

    public function addIsEmpty() {
        return [
            [ 'id', 0,     true ],
            [ 'id', '0',   true ],
            [ 'id', '',    true ],
            [ 'id', ' ',   true ],
            [ 'id', ' 0 ', true ],
            [ 'id', 1,     false ],
            [ 'id', '1',   false ],
            [ 'id', 'a',   false ],
        ];
    }

    /**
     * @dataProvider addIsEmpty
     */
    public function testIsEmpty( $key, $value, $expected ) {

        $result = $this->call( $this->user, 'is_empty', [ $key, $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsCorrect() {
        return [
            [ 'id ', 1, false ],
            [ ' id', 1, false ],
            [ ' id ', 1, false ],
            [ '_id', 1, false ],
            [ 'ID', 1, false ],
            [ 'Id', 1, false ],
            [ 'iD', 1, false ],
            [ 'id', 0, true ],
            [ 'id', 1, true ],
            [ 'id', 2, true ],
            [ 'id', '0', false ],
            [ 'id', '1', false ],
            [ 'id', '2', false ],
            [ 'user_status', 'pending', true ],
            [ 'user_status', 'approved', true ],
            [ 'user_status', 'trash', true ],
            [ 'user_status', 'PENDING', false ],
            [ 'user_status', ' pending ', false ],
            [ 'user_status', 'anything', false ],
            [ 'user_status', '', false ],
            [ 'user_email', 'noreply@noreply.ru', true ],
            [ 'user_email', 'noreply@noreply.com', true ],
            [ 'user_email', 'noreply@noreply.com.ru', true ],
            [ 'user_email', 'noreply@noreply.biz', true ],
            [ 'user_email', 'noreply@noreply.me', true ],
            [ 'user_email', 'noreply@noreply.info', true ],
            [ 'user_email', 'me.noreply@noreply.info', true ],
            [ 'user_email', 'me.me.noreply@noreply.info', true ],
            [ 'user_email', '1noreply@noreply.info', true ],
            [ 'user_email', '_1noreply@noreply.info', true ],
            [ 'user_email', '1a@me.info', true ],
            [ 'user_email', 'noreply@noreply.gif', false ],
            [ 'user_email', 'noreply@noreply.jpg', false ],
            [ 'user_email', 'noreply@noreply.jpeg', false ],
            [ 'user_email', 'noreply@noreply.png', false ],
            [ 'user_email', 'a@noreply.com', false ],



        ];
    }

    /**
     * @dataProvider addIsCorrect
     */
    public function testIsCorrect( $key, $value, $expected ) {

        $result = $this->call( $this->user, 'is_correct', [ $key, $value ] );
        $this->assertEquals( $expected, $result );
    }


}
