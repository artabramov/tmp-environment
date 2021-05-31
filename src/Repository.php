<?php
namespace artabramov\Echidna;

class Repository
{
    protected $e;
    protected $pdo;
    //protected $query;
    protected $rows;

    public function __construct( $pdo ) {
        $this->e = null;
        $this->pdo = $pdo;
        //$this->query = new \artabramov\Echidna\Query();
        $this->rows = [];
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

    // return query object
    public function select( array $columns, string $table, array $kwargs, array $args = [] ) {

        $select = implode( ', ', $columns );
        $where = $this->where( $kwargs );
        $limits = !empty( $args ) ? ' ' . implode( ' ', $args ) : '';

        $query = new \artabramov\Echidna\Query();
        $query->text = 'SELECT ' . $select . ' FROM ' . $table . ' WHERE ' . $where . $limits;
        $query->args = $this->params( $kwargs );
        return $query;
    }

    /**
     *
     */
    public function insert( string $table, array $data ) {

        $columns = implode( ', ', array_keys( $data ));
        $keys = implode( ', ', array_fill( 0, count( $data ), '?' ));
        $values = array_values( $data );

        $query = new \artabramov\Echidna\Query();
        $query->text = 'INSERT INTO ' . $table . ' ( ' . $columns . ' ) VALUES ( ' . $keys . ' )';
        $query->args = $values;
        return $query;
    }

    // return query object
    public function custom( string $query_text, array $query_args ) {

        $query = new \artabramov\Echidna\Query();
        $query->text = $query_text;
        $query->args = $query_args;
        return $query;
    }

    // only execute
    public function execute( $query ) {
        $this->e = null;
        $this->rows = [];

        try {
            $stmt = $this->pdo->prepare( $query->text );
            $stmt->execute( $query->args );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? true : false;
    }

    // execute and select rows
    public function fetch( $query ) {
        $this->e = null;
        $this->rows = [];

        try {
            $stmt = $this->pdo->prepare( $query->text );
            $stmt->execute( $query->args );
            $this->rows = $stmt->fetchAll( $this->pdo::FETCH_OBJ );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? true : false;
    }

    // get last insert id
    public function last_id() {

        try {
            $id = $this->pdo->lastInsertId();

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $id : 0;
    }





    /**
     * @return string
     */
    protected function _get_where( array $args ) : string {
        return implode( ' AND ', array_map( fn( $value ) => !is_array( $value[2] ) ? $value[0] . ' ' . $value[1] . ' ?' : $value[0] . ' ' . $value[1] . ' (' . implode( ', ', array_map( fn() => '?', $value[2] ) ) . ')', $args ));
    }

    /**
     * @return array
     */
    protected function _get_params( array $args ) : array {

        $params = [];
        foreach( $args as $arg ) {
            if( is_array( $arg[2] )) {
                foreach( $arg[2] as $param ) {
                    $params[] = $param;
                }
            } else {
                $params[] = $arg[2];
            }
        }

        return $params;
    }

    /**
     * @return integer
     * @throws \Exception
     */
    public function _insert( string $table, array $data ) : int {

        $columns = implode( ', ', array_keys( $data ));
        $values = implode( ', ', array_fill( 0, count( $data ), '?' ));

        try {
            $stmt = $this->pdo->prepare( 'INSERT INTO ' . $table . ' ( ' . $columns . ' ) VALUES ( ' . $values . ' )' );
            $stmt->execute( array_values( $data ));
            $id = $this->pdo->lastInsertId();

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $id : 0;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function _update( string $table, array $args, array $data ) : bool {

        $set = implode( ', ', array_map( fn( $value ) => $value . ' = ?', array_keys( $data )));
        $where = $this->get_where( $args );
        $params = array_merge( array_values( $data ), $this->get_params( $args ));

        try {
            $stmt = $this->pdo->prepare( 'UPDATE ' . $table . ' SET ' . $set . ' WHERE ' . $where . ' LIMIT 1' );
            $stmt->execute( $params );
            $rows = $stmt->rowCount();

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? true : false;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function _select( array $columns, string $table, array $args, array $extras = [] ) : array {

        $select = implode( ', ', $columns );
        $where = $this->get_where( $args );
        $params = $this->get_params( $args );

        try {
            $sql = 'SELECT ' . $select . ' FROM ' . $table . ' WHERE ' . $where;

            foreach( $extras as $key => $value ) {
                $sql .= ' ' . $key . ' ' . $value;
            }

            $stmt = $this->pdo->prepare( $sql );
            $stmt->execute( $params );
            $rows = $stmt->fetchAll( $this->pdo::FETCH_OBJ );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $rows : [];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function _delete( string $table, array $args ) : bool {

        $where = $this->get_where( $args );
        $params = $this->get_params( $args );

        try {
            $stmt = $this->pdo->prepare( 'DELETE FROM ' . $table . ' WHERE ' . $where );
            $stmt->execute( $params );
            $rows = $stmt->rowCount();

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? true : false;
    }

    /**
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

    public function _query( string $sql, array $params ) {

        try {
            $stmt = $this->pdo->prepare( $sql );
            $stmt->execute( $params );
            $rows = $stmt->fetchAll( $this->pdo::FETCH_OBJ );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $rows : [];
    }

}
