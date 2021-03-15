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

    // __set
    public function __set( string $key, int|string $value ) {
        if( property_exists( $this, $key )) {
            $this->$key = $value;
        }
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

    // __unset
    public function __unset( string $key ) {
        if( property_exists( $this, $key )) {
            $this->$key = '';
        }
    }

    // is empty +
    protected function is_empty( int|string $value ) : bool {
        $value = is_string( $value ) ? trim( $value ) : $value;
        return empty( $value );
    }

    // is id (0-9 {1,20}) +
    protected function is_id( int|string $value ) : bool {
        return is_int( $value ) and ceil( log10( abs( $value ) + 1 )) <= 20;
    }

    // is key (a-z0-9_- {1,20}) +
    protected function is_key( int|string $value ) : bool {
        return is_string( $value ) and preg_match("/^[a-z0-9_-]{1,20}$/", $value );
    }

    // is value {1,255} +
    protected function is_value( int|string $value ) : bool {
        return is_string( $value ) and mb_strlen( $value, 'UTF-8' ) <= 255;
    }

    // is datetime +
    public function is_datetime( int|string $value ) : bool {
        if( !is_string( $value ) or !preg_match("/^\d{4}-((0[0-9])|(1[0-2]))-(([0-2][0-9])|(3[0-1])) (([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/", $value )) {
            return false;
        }
        return checkdate( substr( $value, 5, 2 ), substr( $value, 8, 2 ), substr( $value, 0, 4 ));
    }

    // is token (a-f0-9 {80}) +
    public function is_token( int|string $value ) : bool {
        return is_string( $value ) and preg_match("/^[a-f0-9]{80}$/", $value );
    }

    // is hash (a-f0-9 {20}) +
    public function is_hash( int|string $value ) : bool {
        return is_string( $value ) and preg_match("/^[a-f0-9]{40}$/", $value );
    }

    // is email +/-
    public function is_email( int|string $value ) : bool {
        return is_string( $value ) and preg_match("/^[a-z0-9._-]{2,80}@(([a-z0-9_-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $value );
    }

    // is exists +
    public function is_exists( string $table, array $args ) : bool {

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
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
            $this->exception = $e;
        }

        return !empty( $rows_count );
    }

    // is insert +
    public function is_insert( string $table, array $data ) : bool {

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
            $this->exception = $e;
        }

        return !empty( $id );
    }

    // is update
    protected function is_update( array $args, array $data ) : bool {

        $set = '';
        foreach( $data as $key=>$value ) {
            $set .= empty( $set ) ? ' SET ' : ', ';
            $set .= $key . '=:' . $key;
        }

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? ' WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        $stmt = $this->pdo->prepare( 'UPDATE users' . $set . $where );

        foreach( $args as $arg ) {
            if( $arg[0] == 'id' ) {
                $stmt->bindParam( ':id', $arg[2], \PDO::PARAM_INT, 20 );

            } elseif( $arg[0] == 'user_status' ) {
                $stmt->bindParam( ':user_status', $arg[2], \PDO::PARAM_STR, 40 );

            } elseif( $arg[0] == 'user_token' ) {
                $stmt->bindParam( ':user_token', $arg[2], \PDO::PARAM_STR, 80 );

            } elseif( $arg[0] == 'user_email' ) {
                $stmt->bindParam( ':user_email', $arg[2], \PDO::PARAM_STR, 255 );

            } elseif( $arg[0] == 'user_hash' ) {
                $stmt->bindParam( ':user_hash', $arg[2], \PDO::PARAM_STR, 40 );
            }
        }

        foreach( $data as $key=>$value ) {
            if( $key == 'id' ) {
                $stmt->bindParam( ':id', $value, \PDO::PARAM_INT, 20 );

            } elseif( $key == 'user_status' ) {
                $stmt->bindParam( ':user_status', $value, \PDO::PARAM_STR, 40 );

            } elseif( $key == 'user_token' ) {
                $stmt->bindParam( ':user_token', $value, \PDO::PARAM_STR, 80 );

            } elseif( $key == 'user_email' ) {
                $stmt->bindParam( ':user_email', $value, \PDO::PARAM_STR, 255 );

            } elseif( $key == 'user_hash' ) {
                $stmt->bindParam( ':user_hash', $value, \PDO::PARAM_STR, 40 );
            }
        }

        $stmt->execute();

        return true;

        /*
        $this->clear();

        $affected_rows = $this->dbh
            ->table('users')
            ->where( $where )
            ->update( $update );

        if( is_int( $affected_rows ) ) {
            foreach( $where as $value ) {
                $this->data[$value[0]] = $value[2];
            }

            foreach( $update as $key=>$value ) {
                $this->data[$key] = $value;
            }

        } else {
            $this->error = 'user update error';
        }

        return empty( $this->error ) ? true : false;
        */


    }

    // is select
    protected function is_select( array $where ) : bool {
  
        $this->clear();

        $user = $this->dbh
            ->table( 'users' )
            ->where( $where )
            ->select( '*' )
            ->first();

        if( !empty( $user->id )) {
            $this->id          = $user->id;
            $this->date        = $user->date;
            $this->user_status = $user->user_status;
            $this->user_token  = $user->user_token;
            $this->user_email  = $user->user_email;
            $this->user_hash   = $user->user_hash;
            $this->hash_date   = $user->hash_date;

        } else {
            $this->error = 'user select error';
        }

        return empty( $this->error ) ? true : false;
    }

    // get time
    protected function get_time() : string {

        try {
            $result = $this->pdo->query( 'SELECT NOW() as time' )->fetch();

        } catch( \PDOException $e ) {
            $this->exception = $e;
        }

        return isset( $result['time'] ) ? $result['time'] : '0000-00-00 00:00:00';
    }



}
