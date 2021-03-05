<?php

namespace artabramov\Echidna;

class User
{
    private $db;
    private $error;
    private $id;
    private $date;
    private $user_status;
    private $user_token;
    private $user_email;
    private $user_pass;
    private $user_hash;
    private $hash_date;

    // construct *
    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db = $db;
        $this->clear();
    }

    // set *
    public function __set( string $key, $value ) {}

    // get *
    public function __get( string $key ) {
        return isset( $this->$key ) ? $this->$key : null;
    }

    // clear data
    private function clear() {
        $this->error       = '';
        $this->id          = 0;
        $this->date        = '0000-00-00 00:00:00';
        $this->user_status = '';
        $this->user_token  = '';
        $this->user_email  = '';
        $this->user_pass   = '';
        $this->user_hash   = '';
        $this->hash_date   = '0000-00-00 00:00:00';
    }

    // is error exists *
    public function is_error() {
        return !empty( $this->error );
    }

    // is data empty
    private function is_empty( string $key ): bool {

        if( in_array( $key, [ 'date', 'hash_date' ] )) {
            return $this->$key == '0000-00-00 00:00:00' or empty( $this->$key );
        }
        return empty( $this->$key );
    }

    // is data correct
    private function is_correct( string $key ) : bool {

        if ( $key == 'id' and is_int( $this->id ) and $this->id > 0 and ceil( log10( $this->id )) <= 20 ) {
            return true;

        } elseif ( $key == 'user_status' and is_string( $this->user_status ) and mb_strlen( $this->user_status, 'utf-8' ) <= 40 and preg_match("/^[a-z0-9_-]/", $this->user_status ) ) {
            return true;

        } elseif ( $key == 'user_token' and is_string( $this->user_token ) and mb_strlen( $this->user_token, 'utf-8' ) == 80 ) {
            return true;

        } elseif ( $key == 'user_email' and is_string( $this->user_email ) and mb_strlen( $this->user_email, 'utf-8' ) <= 255 and preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $this->user_email ) ) {
            return true;

        } elseif ( $key == 'user_pass' and is_string( $this->user_pass ) and !empty( $this->user_pass ) ) {
            return true;

        } elseif ( $key == 'user_hash' and is_string( $this->user_hash ) and mb_strlen( $this->user_hash, 'utf-8' ) == 40 ) {
            return true;

        } elseif ( $key == 'hash_date' and is_string( $this->user_hash ) and preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $this->hash_date ) ) {
            return true;
        }

        return false;
    }

    // is user exists
    private function is_exists( array $args ) : bool {
        $user = $this->db
        ->table('users')
        ->select('id');
        foreach( $args as $where ) {
            $user = $user->where( $where[0], $where[1], $where[2] );
        }
        $user = $user->first();
        return empty( $user->id ) ? false : true;
    }

    // generate token
    private function token_create() : string {
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

    // generate password
    private function pass_create() : string {
        $user_pass = '';
        for( $i = 0; $i < 8; $i++ ) {
            $user_pass .= mt_rand( 0,9 );
        }
        return $user_pass;
    }

    // get hash
    private function hash_create( $user_pass ) : string {
        return sha1( $this->user_pass );
    }

    // insert user
    private function insert() : bool {
        
        $this->id = $this->db
        ->table('users')
        ->insertGetId([
            'date'        => $this->db::raw('now()'),
            'user_status' => $this->user_status,
            'user_token'  => $this->user_token,
            'user_email'  => $this->user_email,
            'user_hash'   => $this->user_hash,
            'hash_date'   => $this->hash_date
        ]);

        return empty( $this->id ) ? false : true;
    }

    // select user
    private function select( string $key ) : bool {
  
        $user = $this->db
            ->table('users')
            ->select(['*'])
            ->where([ [ $key, '=', $this->$key ] ])
            ->first();

        if( !empty( $user->id )) {
            $this->id          = $user->id;
            $this->date        = $user->date;
            $this->user_status = $user->user_status;
            $this->user_token  = $user->user_token;
            $this->user_email  = $user->user_email;
            $this->user_hash   = $user->user_hash;
            $this->hash_date   = $user->hash_date;
        }

        return empty( $user->id ) ? false : true;
    }

    // user update
    private function update( array $keys ) : bool {

        $data = [];
        foreach( $keys as $key ) {
            $data[ $key ] = $this->$key;
        }        
        
        $affected_rows = $this->db
            ->table('users')
            ->where([[ 'id', '=', $this->id ]])
            ->update( $data );

        return is_int( $affected_rows ) ? true : false;
    }

    // user register *
    public function register( string $user_email ) : bool {

        if( $this->is_empty( 'user_email' )) {
            $this->error = 'user_email is empty';
        
        } elseif( !$this->is_correct( 'user_email' )) {
            $this->error = 'user_email is incorrect';
        
        } elseif( $this->is_exists( [['user_email', '=', $user->user_email]] )) {
            $this->error = 'user_email is exists';
        
        } else {
            $this->user_status = 'pending';
            $this->user_token  = $this->token_create();
            $this->user_email  = $user_email;
            $this->user_pass   = $this->pass_create();
            $this->user_hash   = $this->hash_create( $this->user_pass );
            $this->hash_date   = '0000-00-00 00:00:00';

            if( !$this->insert() ) {
                $this->clear();
                $this->error = 'user insert error';
            }
        }

        return empty( $this->error ) ? true : false;
    }

    // user restore *
    public function restore( string $user_email ) : bool {

        if( $this->is_empty( 'user_email' )) {
            $this->error = 'user_email is empty';
        
        } elseif( !$this->is_correct( 'user_email' )) {
            $this->error = 'user_email is incorrect';
        
        } elseif( !$this->is_exists( [['user_email', '=', $user->user_email], ['user_status', '<>', 'trash']] )) {
	        $this->error = 'user_email is unavailable';

        } else {
	        $this->user_email = $user_email;
        
            if( !$this->select( 'user_email' )) {
                $this->clear();
                $this->error = 'user restore error';
	        }
        }
    }
    
}
