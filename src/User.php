<?php

namespace artabramov\Echidna;

class User
{
    private $pdo;
    private $exception;
    private $error;
    private $data;

    // __construct
    public function __construct( \PDO $pdo ) {
        $this->pdo = $pdo;
        $this->clear();
    }

    // __get
    public function __get( string $key ) {

        if( in_array( $key, ['exception', 'error'] )) {
            $value = $this->$key;

        } elseif( array_key_exists( $key, $this->data )) {
            $value = $this->data[ $key ];
        }

        return !empty( $value ) ? $value : null;
    }

    // is data not empty +
    private function is_empty( int|string $value ) : bool {

        if( is_string( $value )) {
            $value = trim( $value );
        }

        return empty( $value ) ? true : false;
    }

    // is correct +
    private function is_correct( string $key, int|string $value ) : bool {

        if( !array_key_exists( $key, $this->data )) {
            return false;

        } elseif( $key == 'id' and !is_int( $value )) {
            return false;

        } elseif( $key == 'user_status' and !in_array( $value, ['pending', 'approved', 'trash']) ) {
            return false;

        } elseif( $key == 'user_token' and ( !is_string( $value ) or strlen( $value ) != 80 )) {
            return false;

        } elseif( $key == 'user_email' and ( !is_string( $value ) or !preg_match("/^[a-z0-9._-]{2,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $value ))) {
            return false;

        } elseif( $key == 'user_hash' and ( !is_string( $value ) or strlen( $value ) != 40 )) {
            return false;
        }

        return true;
    }

    // is exists +
    private function is_exists( array $args ) : bool {

        $params = '';
        foreach( $args as $arg ) {
            $params .= empty( $params ) ? ' WHERE ' : ' AND ';
            $params .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'SELECT id FROM users' . $params . ' LIMIT 1' );
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

            $stmt->execute();
            $rows_count = $stmt->rowCount();

        } catch( \PDOException $e ) {
            $this->exception = $e;
        }

        return !empty( $rows_count ) ? true : false;
    }

    // is insert +
    public function is_insert( array $data ) : bool {

        try {
            $stmt = $this->pdo->prepare( "INSERT INTO users ( user_status, user_token, user_email, user_hash ) VALUES ( :user_status, :user_token, :user_email, :user_hash )" );
            $stmt->bindParam( ':user_status', $data['user_status'], \PDO::PARAM_STR, 40 );
            $stmt->bindParam( ':user_token',  $data['user_token'],  \PDO::PARAM_STR, 80 );
            $stmt->bindParam( ':user_email',  $data['user_email'],  \PDO::PARAM_STR, 255 );
            $stmt->bindParam( ':user_hash',   $data['user_hash'],   \PDO::PARAM_STR, 40 );
            $stmt->execute();
            $user_id = $this->pdo->lastInsertId();

        } catch( \PDOException $e ) {
            $this->exception = $e;
        }

        $this->data['id'] = !empty( $user_id ) ? $user_id : 0;

        return !empty( $user_id ) ? true : false;
    }

    // is update
    private function is_update( array $where, array $update ) : bool {

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
    private function is_select( array $where ) : bool {
  
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

    // get time +
    public function get_time() : string {

        try {
            $result = $this->pdo->query( 'SELECT NOW() as time' )->fetch();

        } catch( \PDOException $e ) {
            $this->exception = $e;
        }

        return isset( $result['time'] ) ? $result['time'] : '0000-00-00 00:00:00';
    }

    // get token +
    private function get_token() : string {

        do {
            $user_token = bin2hex( random_bytes( 40 ));

            if( $this->is_exists( [['user_token', '=', $user_token]] )) {
                $repeat = true;
                
            } else {
                $repeat = false;
            }
        } while( $repeat );

        return $user_token;
    }

    // get user pass +
    private function get_pass( $length, $signs = '0123456789' ) : string {

        $user_pass = '';
        $signs_len = mb_strlen( $signs, 'utf-8' ) - 1;

        for( $i = 0; $i < $length; $i++ ) {
            $user_pass .= $signs[ random_int( 0, $signs_len ) ];
        }

        return $user_pass;
    }

    // get hash +
    private function get_hash( $user_pass ) : string {
        return sha1( $user_pass );
    }





    // check is variable not empty
    public function has( string $key ) : bool {

        if( in_array( $key, ['exception', 'error'] )) {
            $value = $this->$key;

        } elseif( array_key_exists( $key, $this->data )) {
            $value = $this->data[ $key ];

        } else {
            $value = '';
        }

        if( is_string( $value )) {
            $value = trim( $value );
        }

        return !empty( $value );
    }

    // clear all user data
    public function clear() {
        $this->exception = null;
        $this->error = '';
        $this->data = [
            'id'          => 0,
            'date'        => '0000-00-00 00:00:00',
            'user_status' => '',
            'user_token'  => '',
            'user_email'  => '',
            'user_pass'   => '',
            'user_hash'   => '',
            'hash_date'   => '0000-00-00 00:00:00'
        ];
    }

    // user register
    public function register( string $user_email ) : bool {

        $this->clear();

        if( $this->is_empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( !$this->is_correct( 'user_email', $user_email )) {
            $this->error = 'user_email is incorrect';
        
        } elseif( $this->is_exists( [['user_email', '=', $user_email]] )) {
            $this->error = 'user_email already exists';
        
        } else {

            $data = [
                'user_status' => 'pending',
                'user_token'  => 'token',
                'user_email'  => $user_email,
                'user_hash'   => '' ];

            if( !$this->is_insert( $data ) ) {
                $this->error = 'user insert error';
            }
        }

        return $this->has( 'error' ) ? false : true;
    }

    // is error exists
    public function fails() {
        return !empty( $this->error );
    }

    // is user attribute not empty
    public function filled( string $key ) : bool {
        return !empty( $this->data[$key] );
    }

    // user restore
    public function restore( string $user_email, int $pass_length = 4, int $restore_delay = 30 ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'user_email', $user_email )) {
            $this->error = 'user_email is incorrect';
        
        } elseif( !$this->select( [['user_email', '=', $user_email], ['user_status', '<>', 'trash']] )) {
            $this->error = 'user not found';

        } elseif( strtotime( $this->time() ) - strtotime( $this->hash_date ) < $restore_delay ) {
            $this->error = 'restore delay is too long';

        } else {

            $this->user_pass = $this->pass_create( $pass_length );

            $where = [['user_email', '=', $user_email]];

            $update = [
                'user_hash' => $this->hash_create( $this->user_pass ),
                'hash_date' => $this->dbh::raw('now()') ];

            if( !$this->update( $where, $update ) ) {
                $this->error = 'user update error';
            }
        }

        if( $this->is_error() ) {
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }
    
    // user signin
    public function signin( string $user_email, string $user_pass, int $pass_expires = 120 ) : bool {

        $this->error = '';
        $this->clear();

        $user_hash  = $this->hash_create( $user_pass );
        
        if( !$this->is_correct( 'user_email', $user_email )) {
            $this->error = 'user_email is incorrect';
    
        } elseif( !$this->is_correct( 'user_pass', $user_pass )) {
            $this->error = 'user_pass is incorrect';
        
        } elseif( !$this->select( [['user_email', '=', $user_email], ['user_hash', '=', $user_hash], ['user_status', '<>', 'trash']] )) {
            $this->error = 'user not found';
        
        } elseif( strtotime( $this->time() ) - strtotime( $this->hash_date ) > $pass_expires ) {
            $this->error = 'user_pass is expired';

        } else {

            $where = [['user_email', '=', $user_email]];

            $update = [
                'user_status' => 'approved',
                'user_hash'   => '',
                'hash_date'   => '0000-00-00 00:00:00'];

            if( !$this->update( $where, $update ) ) {
                $this->error = 'user update error';
            }
        }

        if( $this->is_error() ) {
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // user auth
    public function auth( string $user_token ) : bool {

        $this->error = '';
        $this->clear();
        
        $this->user_token = $user_token;
        
        if( !$this->is_correct( 'user_token', $user_token )) {
            $this->error = 'user_token is incorrect';
        
        } elseif( !$this->select( [[ 'user_token', '=', $user_token ], ['user_status', '=', 'approved']] )) {
            $this->error = 'user not found';
        }

        if( $this->is_error() ) { 
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // user change
    public function change( int $user_id, string $user_email ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'id', $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( !$this->is_correct( 'user_email', $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( !$this->is_exists( [['id', '=', $user_id], ['user_status', '=', 'approved']] )) {
            $this->error = 'user not found';
        
        } elseif( $this->is_exists( [['user_email', '=', $user_email]] )) {
            $this->error = 'user_email is occupied';

        } else {

            $where = [['id', '=', $user_id]];

            $update = [
                'user_status' => 'pending',
                'user_token'  => $this->token_create(),
                'user_pass'   => '',
                'user_hash'   => '',
                'hash_date'   => '0000-00-00 00:00:00' ];

            if( !$this->update( $where, $update )) {
                $this->error = 'user update error';
            }
        }

        if( $this->is_error() ) { 
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // user signout
    public function signout( int $user_id ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'id', $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( !$this->is_exists( [['id', '=', $user_id], ['user_status', '=', 'approved']] )) {
            $this->error = 'user_id not found';

        } else {
            $where = [['id', '=', $user_id]];

            $update = [ 'user_token' => $this->token_create() ];

            if( !$this->update( $where, $update ) ) {
                $this->error = 'user update error';
            }
        }

        if( $this->is_error() ) { 
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // get user
    public function get2( int $user_id, string $user_status ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'id', $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( !$this->select( [['id', '=', $user_id]] ) ) {
            $this->error = 'user not found';
        }

        if( $this->is_error() ) { 
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }
}
