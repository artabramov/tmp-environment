<?php

require_once __DIR__.'/../src/User.php';

class UserTests extends PHPUnit_Framework_TestCase
{
    private $db;
    private $user;

    protected function setUp() {

        $this->db = new \Illuminate\Database\Capsule\Manager;
        $this->db->addConnection([
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'project',
            'username'  => 'root',
            'password'  => '123456',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
            'engine'    => null
        ]);
        $this->db->setAsGlobal();
        $this->db->bootEloquent();


        $this->user = new User( $this->db );
    }

    protected function tearDown() {
        $this->db = null;
        $this->user = null;
    }

    public function testIsEmpty() {

        $result = $this->user->is_empty( 'id', 1 );
        $this->assertEquals(false, $result);
    }

}
