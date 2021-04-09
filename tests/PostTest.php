<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__.'/../src/Interfaces/Sequenceable.php';
require_once __DIR__.'/../src/Models/Echidna.php';
require_once __DIR__.'/../src/Models/Post.php';
require_once __DIR__.'/../src/Utilities/Filter.php';

class PostTest extends TestCase
{
    private $pdo;
    private $post;

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
        $this->post = new \artabramov\Echidna\Models\Post( $this->pdo );
    }

    protected function tearDown() : void {
        $this->pdo = null;
        $this->post = null;
    }

    /**
     * @dataProvider addCreate
     */
    public function testCreate( $parent_id, $user_id, $hub_id, $post_status, $post_type, $post_content, $expected ) {

        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".posts;" );

        $result = $this->callMethod( $this->post, 'create', [ $parent_id, $user_id, $hub_id, $post_status, $post_type, $post_content, 2, 255 ] );
        $post = [];
        
        $post['id']           = $this->getProperty( $this->post, 'id' );
        $post['parent_id']    = $this->getProperty( $this->post, 'parent_id' );
        $post['user_id']      = $this->getProperty( $this->post, 'user_id' );
        $post['hub_id']       = $this->getProperty( $this->post, 'hub_id' );
        $post['post_status']  = $this->getProperty( $this->post, 'post_status' );
        $post['post_type']    = $this->getProperty( $this->post, 'post_type' );
        $post['post_content'] = $this->getProperty( $this->post, 'post_content' );
        $post['error']        = $this->getProperty( $this->post, 'error' );

        $this->assertEquals( $result, $expected );
        if( $result ) {
            $this->assertEquals( $post['id'],           1 );
            $this->assertEquals( $post['parent_id'],    $parent_id );
            $this->assertEquals( $post['user_id'],      $user_id );
            $this->assertEquals( $post['hub_id'],       $hub_id );
            $this->assertEquals( $post['post_status'],  $post_status );
            $this->assertEquals( $post['post_type'],    $post_type );
            $this->assertEquals( $post['post_content'], $post_content );
            $this->assertEmpty( $post['error'] );

        } else {
            $this->assertEquals( $post['id'],           null );
            $this->assertEquals( $post['parent_id'],    null );
            $this->assertEquals( $post['user_id'],      null );
            $this->assertEquals( $post['hub_id'],       null );
            $this->assertEquals( $post['post_status'],  null );
            $this->assertEquals( $post['post_type'],    null );
            $this->assertEquals( $post['post_content'], null );
            $this->assertNotEmpty( $post['error'] );
        }
    }

    public function addCreate() {
        return [

            // correct cases
            [ 1, 1, 1, 'todo', 'post_type', 'Lorem ipsum', true ],
            [ 0, 1, 1, 'todo', 'post_type', 'Lorem ipsum', true ],
            [ 1, 1, 1, 'todo', 'post_type', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor i', true ],

            // incorrect cases
            [ 1, 0, 1, 'todo', 'post_type', 'Lorem ipsum', false ],
            [ 1, 1, 0, 'todo', 'post_type', 'Lorem ipsum', false ],
            [ 1, 1, 1, '', 'post_type', 'Lorem ipsum', false ],
            [ 1, 1, 1, 'todo', '', 'Lorem ipsum', false ],
            [ 1, 1, 1, 'todo', 'post_type', '', false ],
            [ 1, 1, 1, 'todo', 'post_type', 'L', false ],
            [ 1, 1, 1, 'todo', 'post_type', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in', false ],

        ];
    }

}
