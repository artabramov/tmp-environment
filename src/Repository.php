<?php
namespace artabramov\Echidna;

class Repository
{
    protected $e;
    protected $pdo;
    protected $stmt;

    public function __construct( $pdo ) {
        $this->pdo = $pdo;
    }

    public function __get( $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
        return null;
    }

    public function __isset( $key ) {
        if( property_exists( $this, $key )) {
            return !empty( $this->$key );
        }
        return false;
    }

    /**
     * Create query string from array.
     */
    private function where( array $kwargs ) : string {

        return implode( ' AND ', array_map( 
            fn( $value ) => 
                is_array($value[2]) ? $value[0] . ' ' . $value[1] . ' (' . implode( ', ', array_map( fn() => '?', $value[2] ) ) . ')' :
                (
                    is_object($value[2]) ? $value[0] . ' ' . $value[1] . ' (' . $value[2]->text . ') ' :
                    $value[0] . ' ' . $value[1] . ' ?'
                ), 
            $kwargs ));
    }

    /**
     * Create query params from array.
     */
    private function params( array $kwargs ) : array {

        $params = [];
        foreach( $kwargs as $kwarg ) {

            if( is_array( $kwarg[2] )) {
                foreach( $kwarg[2] as $param ) {
                    $params[] = $param;
                }

            } elseif( is_object( $kwarg[2] )) {
                $params = array_merge( $params, $kwarg[2]->args );

            } else {
                $params[] = $kwarg[2];
            }
        }
        return $params;
    }

    /**
     * Create select query.
     */
    public function select( array $columns, string $table, array $kwargs, array $args = [] ) : \artabramov\Echidna\Query {

        $select = implode( ', ', $columns );
        $where = $this->where( $kwargs );
        $limits = !empty( $args ) ? ' ' . implode( ' ', $args ) : '';

        $query = new \artabramov\Echidna\Query();
        $query->text = 'SELECT ' . $select . ' FROM ' . $table . ' WHERE ' . $where . $limits;
        $query->args = $this->params( $kwargs );
        return $query;
    }

    /**
     * Create insert query.
     */
    public function insert( string $table, array $data ) : \artabramov\Echidna\Query {

        $columns = implode( ', ', array_keys( $data ));
        $keys = implode( ', ', array_fill( 0, count( $data ), '?' ));
        $values = array_values( $data );

        $query = new \artabramov\Echidna\Query();
        $query->text = 'INSERT INTO ' . $table . ' ( ' . $columns . ' ) VALUES ( ' . $keys . ' )';
        $query->args = $values;
        return $query;
    }

    /**
     * Create update query.
     */
    public function update( string $table, array $args, array $data ) : \artabramov\Echidna\Query {

        $set = implode( ', ', array_map( fn( $value ) => $value . ' = ?', array_keys( $data )));
        $where = $this->where( $args );
        $params = array_merge( array_values( $data ), $this->params( $args ));

        $query = new \artabramov\Echidna\Query();
        $query->text = 'UPDATE ' . $table . ' SET ' . $set . ' WHERE ' . $where . ' LIMIT 1';
        $query->args = $params;
        return $query;
    }

    /**
     * Create delete query.
     */
    public function delete( string $table, array $args ) : \artabramov\Echidna\Query {

        $where = $this->where( $args );
        $params = $this->params( $args );

        $query = new \artabramov\Echidna\Query();
        $query->text = 'DELETE FROM ' . $table . ' WHERE ' . $where;
        $query->args = $params;
        return $query;
    }

    /**
     * Create custom query.
     */
    public function custom( string $query_text, array $query_args ) : \artabramov\Echidna\Query {

        $query = new \artabramov\Echidna\Query();
        $query->text = $query_text;
        $query->args = $query_args;
        return $query;
    }

    /**
     * Execute the query.
     */
    public function execute( $query ) {
        $this->e = null;

        try {
            $this->stmt = $this->pdo->prepare( $query->text );
            $this->stmt->execute( $query->args );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? true : false;
    }

    /**
     * Select rows after query execute.
     */
    public function rows() {
        $this->e = null;

        try {
            $rows = $this->stmt->fetchAll( $this->pdo::FETCH_OBJ );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $rows : [];
    }

    /**
     * Get last insert id after last query execution.
     */
    public function id() {
        $this->e = null;

        try {
            $id = $this->pdo->lastInsertId();

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $id : 0;
    }

    /**
     * Get current inner time of repository.
     * @return string
     * @throws \Exception
     */
    public function _time() {

        try {
            $stmt = $this->pdo->prepare( 'SELECT NOW() AS datetime;' );
            $stmt->execute();
            $rows = $stmt->fetch( $this->pdo::FETCH_ASSOC );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $rows[ 'datetime' ] : '0000-00-00 00:00:00';
    }



}
