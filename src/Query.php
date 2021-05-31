<?php
namespace artabramov\Echidna;

class Query
{
    protected $text;
    protected $args;

    public function __construct( string $text = '', array $args = [] ) {
        $this->text = $text;
        $this->args = $args;
    }

    public function __get( $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
        return null;
    }

    public function __set( $key, $value ) {
        if( property_exists( $this, $key )) {
            $this->$key = $value;
        }
    }

    public function __isset( $key ) {
        if( property_exists( $this, $key )) {
            return !empty( $this->$key );
        }
        return false;
    }

}
