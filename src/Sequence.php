<?php
namespace artabramov\Echidna;

class Sequence
{
    protected $error;
    protected $repository;
    protected $entity;
    protected $rows;

    public function __construct( $repository, $entity ) {
        $this->error = '';
        $this->repository = $repository;
        $this->entity = $entity;
        $this->rows = [];
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

    public function select( array $columns, string $table, array $kwargs, array $args = [] ) : \artabramov\Echidna\Query {
        return $this->repository->select( $columns, $table, $kwargs, $args );
    }

    // return result rows
    public function execute( $query ) {

        $this->error = '';
        $this->rows = [];

        $this->repository->execute( $query );
        $rows = $this->repository->rows();

        foreach( $rows as $row ) {

            $instance = clone $this->entity;
            $reflection = new \ReflectionClass( $instance );
            $properties = $reflection->getProperties();

            foreach( $properties as $property ) {
                $property_name = $property->name;

                if( property_exists( $row, $property_name ) ) {
                    $property->setAccessible( true );
                    $property->setValue( $instance, $row->$property_name );
                }
            }
            array_push( $this->rows, $instance );
        }
    }

}
