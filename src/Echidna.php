<?php
namespace artabramov\Echidna;

class Echidna
{
    protected $pdo;
    protected $e;

    // __construct
    public function __construct( \PDO $pdo ) {
        $this->pdo = $pdo;
    }

    // __get
    public function __get( string $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
    }

    // __isset
    public function __isset( string $key ) {
        $value = property_exists( $this, $key ) ? $this->$key : null;
        $value = is_string( $value ) ? trim( $value ) : $value;
        return !empty( $value );
    }

    // is empty
    protected function is_empty( int|string $value ) : bool {
        $value = is_string( $value ) ? trim( $value ) : $value;
        return empty( $value );
    }

    // is id (0-9 {1,20})
    protected function is_id( int|string $value ) : bool {
        return is_int( $value ) and $value >= 0;
    }

    // is key (a-z0-9_- {1,20})
    protected function is_key( int|string $value ) : bool {
        return is_string( $value ) and preg_match("/^[a-z0-9_-]{1,20}$/", $value );
    }

    // is value {1,255}
    protected function is_value( int|string $value ) : bool {
        return is_string( $value ) and mb_strlen( $value, 'UTF-8' ) <= 255;
    }

    // is datetime +
    protected function is_datetime( int|string $value ) : bool {
        if( !is_string( $value ) or !preg_match("/^\d{4}-((0[0-9])|(1[0-2]))-(([0-2][0-9])|(3[0-1])) (([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/", $value )) {
            return false;
        }
        return checkdate( substr( $value, 5, 2 ), substr( $value, 8, 2 ), substr( $value, 0, 4 ));
    }

    // is token (a-f0-9 {80})
    protected function is_token( int|string $value ) : bool {
        return is_string( $value ) and preg_match("/^[a-f0-9]{80}$/", $value );
    }

    // is hash (a-f0-9 {20})
    protected function is_hash( int|string $value ) : bool {
        return is_string( $value ) and preg_match("/^[a-f0-9]{40}$/", $value );
    }

    // is email +/-
    protected function is_email( int|string $value ) : bool {
        return is_string( $value ) and preg_match("/^[a-z0-9._-]{2,80}@(([a-z0-9_-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $value );
    }

    // is exists
    protected function is_exists( string $table, array $args ) : bool {

        try {
            $where = '';
            foreach( $args as $arg ) {
                $where .= empty( $where ) ? 'WHERE ' : ' AND ';
                $where .= $arg[0] . $arg[1] . ':' . $arg[0];
            }
        
            $stmt = $this->pdo->prepare( 'SELECT id FROM ' . $table . ' ' .$where . ' LIMIT 1' );
            foreach( $args as $arg ) {

                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], \PDO::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], \PDO::PARAM_STR );
                }
            }

            $stmt->execute();
            $rows_count = $stmt->rowCount();

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return !empty( $rows_count );
    }

    // is insert
    protected function insert( string $table, array $data ) : int|bool {

        try {
            $fields = '';
            $values = '';
            foreach( $data as $key=>$value ) {
                $fields .= empty( $fields ) ? $key : ', ' . $key;
                $values .= empty( $values ) ? ':' . $key : ', ' . ':' . $key;
            }

            $stmt = $this->pdo->prepare( 'INSERT INTO ' . $table . ' ( ' . $fields . ' ) VALUES ( ' . $values . ' )' );

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

    // update
    protected function update( string $table, array $args, array $data ) : int|bool {

        if( empty( $table ) or empty( $args ) or empty( $data )) {
            return 0;
        }

        try {
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

    // is select
    protected function select( string $table, array $args, int $limit = 1, int $offset = 0 ) : array|bool {
  
        if( empty( $table ) or empty( $args )) {
            return 0;
        }

        try {
            $where = '';
            foreach( $args as $arg ) {
                $where .= empty( $where ) ? 'WHERE ' : ' AND ';
                $where .= $arg[0] . $arg[1] . ':' . $arg[0];
            }

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

    // is delete
    protected function delete( string $table, array $args ) : int|bool {

        if( empty( $table ) or empty( $args )) {
            return 0;
        }

        try {
            $where = '';
            foreach( $args as $arg ) {
                $where .= empty( $where ) ? 'WHERE ' : ' AND ';
                $where .= $arg[0] . $arg[1] . ':' . $arg[0];
            }

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

    // get time
    public function get_time() : string {

        try {
            $result = $this->pdo->query( 'SELECT NOW() as time' )->fetch();

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $result['time'] : '0000-00-00 00:00:00';
    }

}
