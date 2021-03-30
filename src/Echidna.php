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
        if( !property_exists( $this, $key )) {
            return false;
        }
        return !empty( is_string( $this->$key ) ? trim( $this->$key ) : $this->$key );
    }

    /**
     * Check is attribute numeric up to 20 signs.
     * @param string $key
     * @return bool
     */
    protected function is_id( string $key ) : bool {
        return is_int( $this->key ) and $this->key >= 0;
    }


}
