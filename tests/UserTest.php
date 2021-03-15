<?php

use PHPUnit\Framework\TestCase;

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

        // Параметры подключения к базе данных
        $pdo_host    = 'localhost';
        $pdo_user    = 'root';
        $pdo_pass    = '123456';
        $pdo_dbase   = 'project';
        $pdo_charset = 'utf8';

        // Подключаемся к базе данных
        $dsn = 'mysql:host=' . $pdo_host . ';dbname=' . $pdo_dbase . ';charset=' . $pdo_charset;
        $args = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // создаем объект подключения
        $this->pdo = new PDO( $dsn, $pdo_user, $pdo_pass, $args );

        $this->user = new \artabramov\Echidna\Echidna\User( $this->pdo );
    }

    protected function tearDown() : void {
        $this->db = null;
        $this->user = null;
    }

    /**
     * get_token
     */
    public function testGetToken() {

        // is a string
        $result = $this->call( $this->user, 'get_token' );
        $this->assertIsString( $result );

        // is 80-signs length
        $result = strlen( $this->call( $this->user, 'get_token' ) ) == 80 ? true : false;
        $this->assertTrue( $result );

        // is HEX-signs only
        $result = $this->call( $this->user, 'get_token' );
        $this->assertMatchesRegularExpression( '/[a-f0-9]{80}/', $result );
    }

    /**
     * @dataProvider addRegister
     */
    public function testRegister( $user_email, $expected ) {

        $result = $this->call( $this->user, 'register', [ $user_email ] );
        $this->assertEquals( $expected, $result );

        // check is exists
        if( $result ) {
            $exists_result = $this->call( $this->user, 'register', [ $user_email ] );
            $this->assertFalse( $exists_result );
        }

        // delete test data
        if( $result ) {
            $stmt = $this->pdo->query( "DELETE FROM users WHERE user_email='" . $user_email . "'" );
        }
    }

    public function addRegister() {
        return [

            [ 'noreply@noreply.no', true ],
            [ 'noreply.1@noreply.1.no', true ],
            [ 'noreply.noreply@noreply.noreply.no', true ],
            [ 'noreply-noreply.noreply@noreply-noreply.noreply.no', true ],
            [ 'noreply_noreply.noreply@noreply_noreply.noreply.no', true ],

            [ '', false ],
            [ ' ', false ],
            [ '0', false ],
            [ ' 0', false ],
            [ '0 ', false ],
            [ 'noreply.no', false ],
            [ 'noreply@', false ],
            [ '@noreply', false ],
            [ '@noreply.no', false ],
            [ 'noreply@noreply', false ],
            [ 'noreply@noreply.nono', false ],

        ];
    }



    /*
    public function testGetPass() {

        // can be empty
        $result = str_replace( ' ', '',  $this->call( $this->user, 'get_pass', [20, ' '] ));
        $this->assertEmpty( $result );

        // is 20-signs length
        $result = strlen( $this->call( $this->user, 'get_pass', [20] )) == 20 ? true : false;
        $this->assertTrue( $result );

        // can be only numbers
        $result = $this->call( $this->user, 'get_pass', [20, '0123456789'] );
        $this->assertIsNumeric( $result );

        // can be only letters
        $result = $this->call( $this->user, 'get_pass', [20, 'abcdefghijklmnopqrstuvwxyz'] );
        $this->assertIsString( $result );

        // can be any signs
        $result = $this->call( $this->user, 'get_pass', [20, '0123456789abcdefghijklmnopqrstuvwxyz'] );
        $this->assertMatchesRegularExpression( '/[a-z0-9]{20}/', $result );
    }

    public function testGetHash() {

        $result = $this->call( $this->user, 'get_hash', [ '' ] );
        $this->assertEquals( sha1(''), $result );

        $result = $this->call( $this->user, 'get_hash', [ '1' ] );
        $this->assertEquals( sha1('1'), $result );

        $result = $this->call( $this->user, 'get_hash', [ '2' ] );
        $this->assertEquals( sha1('2'), $result );
    }
    */

}
