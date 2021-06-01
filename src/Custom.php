<?php
namespace artabramov\Echidna;

class Time
{
    protected $error;
    protected $repository;

    public function __construct( $repository ) {
        $this->error = '';
        $this->repository = $repository;
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

    public function time() {
        $query = $this->repository->custom( "SELECT NOW() as time", [] );
        $this->repository->execute( $query );
        return $this->repository->rows[0]->time;
    }

}
