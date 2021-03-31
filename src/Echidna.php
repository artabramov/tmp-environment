<?php
namespace artabramov\Echidna;

class Echidna
{
    protected $pdo;
    protected $e;
    public $error;

    public function __construct( \PDO $pdo ) {
        $this->pdo = $pdo;
    }

    public function __get( string $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
    }

    public function __set( string $key, $value ) {
        if( property_exists( $this, $key )) {
            $this->$key = $value;
        }
    }

    public function __isset( string $key ) {
        return property_exists( $this, $key );
    }

    /**
     * Insert a new entry in the table.
     * @param string $table
     * @param array $data
     * @return int|bool
     * @throws \PDOException
     */
    protected function insert( string $table, array $data ) : int|bool {

        $keys = '';
        $values = '';
        foreach( $data as $key=>$value ) {
            $keys .= empty( $keys ) ? $key : ', ' . $key;
            $values .= empty( $values ) ? ':' . $key : ', ' . ':' . $key;
        }

        try {
            $stmt = $this->pdo->prepare( 'INSERT INTO ' . $table . ' ( ' . $keys . ' ) VALUES ( ' . $values . ' )' );
            foreach( $data as $key=>$value ) {
                $stmt->bindParam( ':' . $key, $data[ $key ], \PDO::PARAM_STR );
            }

            $stmt->execute();
            $id = $this->pdo->lastInsertId();

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $id : false;
    }

    /**
     * Update an entry.
     * @param string $table
     * @param array $args
     * @param array $data
     * @return int|bool
     * @throws \PDOException
     */
    protected function update( string $table, array $args, array $data ) : int|bool {

        if( empty( $table ) or empty( $args ) or empty( $data )) {
            return 0;
        }

        $set = '';
        foreach( $data as $key=>$value ) {
            $set .= empty( $set ) ? 'SET ' : ', ';
            $set .= $key . '=:' . $key;
        }

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'UPDATE ' . $table . ' ' . $set . ' ' . $where . ' LIMIT 1' );

            foreach( $args as $arg ) {
                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':id', $arg[2], \PDO::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], \PDO::PARAM_STR );
                }
            }

            foreach( $data as $key=>&$value ) {
                $stmt->bindParam( ':' . $key, $value, \PDO::PARAM_STR );
            }

            $stmt->execute();
            $rows = $stmt->rowCount();

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $rows : false;
    }

    /**
     * Select an entry.
     * @param string $table
     * @param array $args
     * @param int $limit
     * @param int $offset
     * @return array|bool
     * @throws \PDOException
     */
    protected function select( string $table, array $args, int $limit = 1, int $offset = 0 ) : array|bool {
  
        if( empty( $table ) or empty( $args )) {
            return 0;
        }

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'SELECT * FROM ' . $table . ' ' . $where . ' LIMIT :limit OFFSET :offset' );

            foreach( $args as $arg ) {
                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':id', $arg[2], \PDO::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], \PDO::PARAM_STR );
                }
            }

            $stmt->bindValue( ':limit', $limit, \PDO::PARAM_INT );
            $stmt->bindValue( ':offset', $offset, \PDO::PARAM_INT );

            $stmt->execute();
            $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $rows : false;
    }

    /**
     * Select an entry.
     * @param string $table
     * @param array $args
     * @param int $limit
     * @param int $offset
     * @return int|bool
     * @throws \PDOException
     */
    protected function delete( string $table, array $args ) : int|bool {

        if( empty( $table ) or empty( $args )) {
            return 0;
        }

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'DELETE FROM ' . $table . ' ' . $where . ' LIMIT 1' );

            foreach( $args as $arg ) {
                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':id', $arg[2], \PDO::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], \PDO::PARAM_STR );
                }
            }

            $stmt->execute();
            $rows = $stmt->rowCount();

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $rows : false;
    }

    protected function count( string $table, array $args ) : int {

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'SELECT COUNT(id) FROM ' . $table . ' ' .$where );
            foreach( $args as $arg ) {

                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], \PDO::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], \PDO::PARAM_STR );
                }
            }

            $stmt->execute();
            $rows = $stmt->fetch( \PDO::FETCH_ASSOC );
            return $rows['COUNT(id)'];

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return 0;
    }

    // TODO: custom query
    protected function query() {}

}
