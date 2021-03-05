<?php

namespace artabramov\Echidna;

class User
{
    private $db;
    private $error;
    private $id;
    private $data;
    private $user_status;
    private $user_token;
    private $user_email;
    private $user_pass;
    private $user_hash;
    private $hash_date;

    // create the object *
    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {

        $this->db          = $db;
        $this->error       = '';
        $this->id          = 0;
        $this->data        = '0000-00-00 00:00:00';
        $this->user_status = '';
        $this->user_token  = '';
        $this->user_email  = '';
        $this->user_pass   = '';
        $this->user_hash   = '';
        $this->hash_date   = '0000-00-00 00:00:00';
    }

    // set the data *
    public function __set( string $key, $value ) {}

    // get the data *
    public function __get( string $key ) {
        return isset( $this->$key ) ? $this->key : null;
    }

    // check error exists *
    public function is_error() {
        return empty( $this->error );
    }

    // check data exists
    private function is_empty( string $key ): bool {

        if( in_array( $key, [ 'date', 'hash_date' ] )) {
            return $this->data[$key] == '0000-00-00 00:00:00';
        }
        return empty( $this->data[ $key ] );
    }

    // data validation
    private function is_correct( string $key ) : bool {

        if ( $key == 'id' and is_int( $this->data['id'] ) and $this->data['id'] > 0 and ceil( log10( $this->data['id'] )) <= 20 ) {
            return true;

        } elseif ( $key == 'user_status' and is_string( $this->data['user_status'] ) and mb_strlen( $this->data['user_status'], 'utf-8' ) <= 40 and preg_match("/^[a-z0-9_-]/", $this->data['user_status'] ) ) {
            return true;

        } elseif ( $key == 'user_token' and is_string( $this->data['user_token'] ) and mb_strlen( $this->data['user_token'], 'utf-8' ) == 80 ) {
            return true;

        } elseif ( $key == 'user_email' and is_string( $this->data['user_email'] ) and mb_strlen( $this->data['user_email'], 'utf-8' ) <= 255 and preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $this->data['user_email'] ) ) {
            return true;

        } elseif ( $key == 'user_pass' and is_string( $this->data['user_pass'] ) and !empty( $this->data['user_pass'] ) ) {
            return true;

        } elseif ( $key == 'user_hash' and is_string( $this->data['user_hash'] ) and mb_strlen( $this->data['user_hash'], 'utf-8' ) == 40 ) {
            return true;

        } elseif ( $key == 'hash_date' and is_string( $this->data['user_hash'] ) and preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $this->data['hash_date'] ) ) {
            return true;
        }

        return false;
    }

    // check that the user exists
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

    // generate unique random token
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

    // generate random password
    private function pass_create() : string {
        $user_pass = '';
        for( $i = 0; $i < 8; $i++ ) {
            $user_pass .= mt_rand( 0,9 );
        }
        return $user_pass;
    }

    // return a hash
    private function hash_create( $user_pass ) : string {
        return sha1( $this->data['user_pass'] );
    }

    // insert user
    private function user_insert() : bool {
        
        $this->data['id'] = $this->db
        ->table('users')
        ->insertGetId([
            'date'        => $this->db::raw('now()'),
            'user_status' => $this->data['user_status'],
            'user_token'  => $this->data['user_token'],
            'user_email'  => $this->data['user_email'],
            'user_hash'   => $this->data['user_hash'],
            'hash_date'   => $this->data['hash_date']
        ]);

        return empty( $this->data['id'] ) ? false : true;
    }

    // select user
    private function user_select( string $key ) : bool {
  
        $user = $this->db
            ->table('users')
            ->select(['*'])
            ->where([ [ $key, '=', $this->data[ $key ] ] ])
            ->first();

        if( !empty( $user->id )) {
            $this->data['id'] = $user->id;
            $this->data['date'] = $user->date;
            $this->data['user_status'] = $user->user_status;
            $this->data['user_token'] = $user->user_token;
            $this->data['user_email'] = $user->user_email;
            $this->data['user_hash'] = $user->user_hash;
            $this->data['hash_date'] = $user->hash_date;
        }

        return empty( $user->id ) ? false : true;
    }

    // update user
    private function user_update( array $keys ) : bool {

        $data = [];
        foreach( $keys as $key ) {
            $data[ $key ] = $this->data[ $key ];
        }        
        
        $affected_rows = $this->db
            ->table('users')
            ->where([[ 'id', '=', $this->data['id'] ]])
            ->update( $data );

        return is_int( $affected_rows ) ? true : false;
    }

    // register *
    public function register( string $user_email ) : bool {

        if( $user->is_empty( 'user_email' )) {
            $this->error = 'user_email is empty';
        
        } elseif( !$user->is_correct( 'user_email' )) {
            $this->error = 'user_email is incorrect';
        
        } elseif( $user->is_exists( [['user_email', '=', $user->user_email]] )) {
            $this->error = 'user_email is exists';
        
        } else {
            $this->user_status = 'pending';
            $this->user_token  = $this->token_create();
            $this->user_email  = $user_email;
            $this->user_pass   = $this->pass_create();
            $this->user_hash   = $this->hash_create( $this->user_pass );
            $this->hash_date   = '0000-00-00 00:00:00';

            if( !$user->insert() ) {
                $this->error = 'user insert error';
            }
        }
    }
    
}