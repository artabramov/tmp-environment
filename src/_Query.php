<?php
namespace artabramov\Echidna;

class Query
{
    protected $query;
    protected $params;

    public function __construct() {
        $this->query = '';
        $this->params = [];
    }

    public function __get( $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
        return null;
    }

    public function __set( $key ) {
        if( property_exists( $this, $key )) {
            $this->$key = $value;
        }
    }

}
