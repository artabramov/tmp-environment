<?php
namespace artabramov\Echidna;

class Time
{
    protected $error;
    protected $repository;
    protected $time;

    public function __construct( $repository ) {
        $this->error = '';
        $this->repository = $repository;
        $this->time = $this->repository->time();
    }

    public function __isset( $key ) {
        if( property_exists( $this, $key )) {
            return !empty( $this->$key );
        }
        return false;
    }

    public function __get( $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
    }

}
