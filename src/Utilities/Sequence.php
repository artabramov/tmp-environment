<?php
namespace artabramov\Echidna\Utilities;

class Sequence extends \artabramov\Echidna\Models\Echidna
{
    public $data = [];

    public function getall( $model, $table, $args, $limit, $offset ) {
        $rows = $this->select( 'id', $table, $args, $limit, $offset );

        foreach( $rows as $row ) {
            $instance = new $model( $this->pdo );
            $instance->getone( intval( $row['id'] ));
            array_push( $this->data, $instance );
        }
    }
}
