<?php
namespace artabramov\Echidna\Services;

class Sequence extends \artabramov\Echidna\Models\Echidna
{
    public $data = [];

    public function collect( $model, $table, $args, $limit, $offset ) {
        $rows = $this->select( 'id', $table, $args, $limit, $offset );

        foreach( $rows as $row ) {
            $instance = new $model( $this->pdo );
            $instance->get( intval( $row['id'] ));
            array_push( $this->data, $instance );
        }
    }
}
