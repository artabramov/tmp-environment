<?php
namespace artabramov\Echidna;

class Sequence
{
    protected $repository;
    protected $mapper;
    public $data = [];

    public function __construct( $repository, $mapper ) {
        $this->error = '';
        $this->repository = $repository;
        $this->mapper = $mapper;
    }

    /**
     * @return array
     */
    public function select( $entity, array $args, array $extras ) : array {

        $class = new \ReflectionClass( $entity );
        $doc = $class->getDocComment();
        preg_match_all( '#@entity\((.*?)\)\n#s', $doc, $tmp );
        preg_match_all( '/\s*([^=]+)=(\S+)\s*/', !empty( $tmp[1][0]) ? $tmp[1][0] : '', $tmp );
        $params = array_combine ( $tmp[1], $tmp[2] );

        $rows = $this->repository->select( ['id'], $params['table'], $args, $extras );

        foreach( $rows as $row ) {
            $instance = clone $entity;
            $this->mapper->select( $instance, [['id', '=', $row->id]] );
            array_push( $this->data, $instance );
        }

        return $this->data;
    }
}
