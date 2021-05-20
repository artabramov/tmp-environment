<?php
namespace artabramov\Echidna;

class Sequence
{
    protected $repository;
    public $data = [];

    public function __construct( $repository ) {
        $this->error = '';
        $this->repository = $repository;
    }

    /**
     * @return array
     */
    public function select( string $entity_class, array $args, array $extras ) : array {

        $entity = new \artabramov\Echidna\Sequence( $repository );

        $class = new \ReflectionClass( $entity_class );
        $doc = $entity_class->getDocComment();
        preg_match_all( '#@' . $key . '\((.*?)\)\n#s', $doc, $tmp );
        preg_match_all( '/\s*([^=]+)=(\S+)\s*/', !empty($tmp[1][0]) ? $tmp[1][0] : '', $tmp );
        $entity_params = array_combine ( $tmp[1], $tmp[2] );

        $this->data = $this->repository->select( $entity_params['table'], $table, $args, $extras );

        /*
        foreach( $rows as $row ) {
            $user = new \App\Entities\User;
            $instance = new $model( $this->pdo );
            $instance->getone( intval( $row['id'] ));
            array_push( $this->data, $instance );
        }
        */

        return $this->data;
    }
}
