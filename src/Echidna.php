<?php
namespace artabramov\Echidna;

class Echidna
{
    protected $pdo;
    protected $e;
    public $error;

    public function __construct( \PDO $pdo ) {
        $this->pdo = $pdo;
    }

    public function __get( string $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
    }

    public function __set( string $key, $value ) {
        if( property_exists( $this, $key )) {
            $this->$key = $value;
        }
    }

    public function __isset( string $key ) {
        return property_exists( $this, $key );
    }

    protected function query() {} // time

    protected function is_exists() {}

    protected function count() {}

    protected function insert( string $table, array $keys ) {}

    protected function select() {}

    protected function update() {}

    protected function delete() {}

}
