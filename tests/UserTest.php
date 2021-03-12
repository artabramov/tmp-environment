<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../src/User.php';

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
        

        $this->user = new \artabramov\Echidna\User( $this->pdo );
    }

    protected function tearDown() : void {
        $this->db = null;
        $this->user = null;
    }

    /**
     * @dataProvider addIsEmpty
     */
    public function testIsEmpty( $value, $expected ) {

        $result = $this->call( $this->user, 'is_empty', [ $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsEmpty() {

        return [
            [ '', true ],
            [ ' ', true ],
            [ '  ', true ],

            [ 0, true ],
            [ 1, false ],
            [ 2, false ],

            [ '0', true ],
            [ ' 0 ', true ],
            [ '  0  ', true ],
            [ '0 ', true ],
            [ '0  ', true ],
            [ ' 0', true ],
            [ '  0', true ],

            [ '1', false ],
            [ ' 1 ', false ],
            [ '  1  ', false ],
            [ '1 ', false ],
            [ '1  ', false ],
            [ ' 1', false ],
            [ '  1', false ],

            [ '\\', false ],
            [ '/', false ],
            [ ',', false ],
            [ '\'', false ],
            [ '_', false ],
            [ '-', false ],
            [ ';', false ],
            [ 'null', false ],
            [ 'exit', false ],
        ];
    }

    /**
     * @dataProvider addIsCorrect
     */
    public function testIsCorrect( $key, $value, $expected ) {

        $result = $this->call( $this->user, 'is_correct', [ $key, $value ] );
        $this->assertEquals( $expected, $result );
    }

    public function addIsCorrect() {

        return [
            [ 'id', 0, true ],
            [ 'id', 1, true ],
            [ 'id', 2, true ],
            [ 'id', '0', false ],
            [ 'id', '1', false ],
            [ 'id', '2', false ],
            [ 'id', '1.0', false ],
            [ 'id', '1.1', false ],
            [ 'id', '1.2', false ],

            [ 'user_status', 'pending', true ],
            [ 'user_status', 'approved', true ],
            [ 'user_status', 'trash', true ],
            [ 'user_status', 'PENDING', false ],
            [ 'user_status', ' pending ', false ],
            [ 'user_status', 'anything', false ],
            [ 'user_status', '', false ],

            [ 'user_token', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b0', true ],
            [ 'user_token', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b', false ],
            [ 'user_token', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b01', false ],

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

            [ 'user_hash', '0b5715dc83f4a921d36ce9ce47d0d13c5d85f2b0', true ],
            [ 'user_hash', '0b5715dc83f4a921d36ce9ce47d0d13c5d85f2b', false ],
            [ 'user_hash', '0b5715dc83f4a921d36ce9ce47d0d13c5d85f2b01', false ],
        ];
    }

    /**
     * @dataProvider addIsInsert
     */
    public function testIsInsert( $data, $expected ) {

        $result = $this->call( $this->user, 'is_insert', [ $data ] );
        $this->assertEquals( $expected, $result );
    }
    
    public function addIsInsert() {

        return [
            [ [ 'user_status' => 'pending', 
                'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 
                'user_email'  => 'noreply.0@noreply.no', 
                'user_hash'   => '' ], 
                true ],

            [ [ 'user_status' => 'pending', 
                'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200', 
                'user_email'  => 'noreply.0@noreply.no', 
                'user_hash'   => '' ], 
                false ],

                [ [ 'user_status' => 'approved', 
                'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', 
                'user_email'  => 'noreply.1@noreply.no', 
                'user_hash'   => '' ], 
                true ],

                [ [ 'user_status' => 'pending', 
                'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201', 
                'user_email'  => 'noreply.z@noreply.no', 
                'user_hash'   => '' ], 
                false ],

            [ [ 'user_status' => 'pending', 
                'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f20z', 
                'user_email'  => 'noreply.1@noreply.no', 
                'user_hash'   => '' ], 
                false ],

            [ [ 'user_status' => 'trash', 
                'user_token'  => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f202', 
                'user_email'  => 'noreply.2@noreply.no', 
                'user_hash'   => '' ], 
                true ],

        ];



    }

    /**
     * @dataProvider addIsExists
     */
    public function testIsExists( $data, $expected ) {

        $result = $this->call( $this->user, 'is_exists', [ $data ] );
        $this->assertEquals( $expected, $result );
    }
    
    public function addIsExists() {

        return [ 
            [ [['id', '=', 0]], false ],
            [ [['id', '=', 2]], false ],
            [ [['id', '=', 4]], false ],
            [ [['id', '=', 1]], true ],
            [ [['id', '=', 3]], true ],
            [ [['id', '=', 6]], true ],

            [ [['id', '=', 1], ['user_status', '=', 'pending'] ], true ],
            [ [['id', '=', 1], ['user_status', '=', 'approved'] ], false ],
            [ [['id', '=', 1], ['user_status', '=', 'trash'] ], false ],
            [ [['id', '=', 1], ['user_status', '<>', 'approved'] ], true ],
            [ [['id', '=', 1], ['user_status', '<>', 'trash'] ], true ],

            [ [['user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f200']], true ],
            [ [['user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f201']], true ],
            [ [['user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f202']], true ],
            [ [['user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2000']], false ],
            [ [['user_token', '=', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f20']], false ],

            [ [['user_email', '=', 'noreply.0@noreply.no']], true ],
            [ [['user_email', '=', 'noreply.1@noreply.no']], true ],
            [ [['user_email', '=', 'noreply.2@noreply.no']], true ],
            [ [['user_email', '=', 'noreply.3@noreply.no']], false ],

        ];



    }


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

    public function testGetTime() {

        // is a string
        $result = $this->call( $this->user, 'get_time' );
        $this->assertIsString( $result );

        // is dateteime-format
        $result = $this->call( $this->user, 'get_time' );
        $this->assertMatchesRegularExpression( '/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/', $result );
    }




}
