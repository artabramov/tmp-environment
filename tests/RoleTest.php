<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/../src/Echidna.php';
require_once __DIR__ . '/../src/Echidna/Role.php';

class RoleTest extends TestCase
{
    private $pdo;
    private $role;

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
        $this->role = new \artabramov\Echidna\Echidna\Role( $this->pdo );
    }

    protected function tearDown() : void {
        $this->pdo = null;
        $this->role = null;
    }

    /**
     * @dataProvider addSet
     */
    public function testSet( $user_id, $hub_id, $user_role, $expected ) {

        // truncate table before testing
        $stmt = $this->pdo->query( "TRUNCATE TABLE " . PDO_DBASE . ".user_roles;" );

        // test
        $result = $this->call( $this->role, 'set', [ $user_id, $hub_id, $user_role ] );
        $this->assertEquals( $expected, $result );

    }

    public function addSet() {
        return [

            // TRUE: various correct cases
            [ 1, 1, 'admin', true ],
            [ 9223372036854775807, 1, 'admin', true ],
            [ 1, 9223372036854775807, 'admin', true ],
            [ 1, 1, 'a', true ],
            [ 1, 1, 'admin_admin_admin_ad', true ],

            // FALSE: incorrect user_id (int only)
            [ 0, 1, 'admin', false ],
            [ -1, 1, 'admin', false ],

            // FALSE: incorrect hub_id (int only)
            [ 1, 0, 'admin', false ],
            [ 1, -1, 'admin', false ],

            // FALSE: various incorrect user_role (string only)
            [ 1, 1, '', false ],
            [ 1, 1, ' ', false ],
            [ 1, 1, '0', false ],
            [ 1, 1, '0 ', false ],
            [ 1, 1, 'admin_admin_admin_adm', false ],

        ];
    }

}
