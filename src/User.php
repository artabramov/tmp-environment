<?php

namespace artabramov\Echidna;

class User
{
    private $db;
    private $data;

    // create the object
    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {

        $this->db    = $db;
        $this->data  = [
            'id'          => 0,
            'date'        => '',
            'user_status' => '',
            'user_token'  => '',
            'user_email'  => '',
            'user_pass'   => '',
            'user_hash'   => '',
            'hash_date'   => ''
        ];
    }

    // set the data
    public function __set( string $key, $value ) {
        if( array_key_exists( $key, $this->data ) ) {
            $this->data[ $key ] = $value;
        }
    }

    // get the data
    public function __get( string $key ) {
        if( array_key_exists( $key, $this->data ) ) {
            return $this->data[ $key ];
        }
        return null;
    }

    // check data is not empty
    public function has( string $key ) : bool {
        if( !empty( $this->data[ $key ] ) ) {
            return true;
        }
        return false;
    }

    // clear data and error
    public function clear() : bool {
        $this->error = '';
        $this->data  = [
            'id'          => 0,
            'date'        => '',
            'user_status' => '',
            'user_token'  => '',
            'user_email'  => '',
            'user_pass'   => '',
            'user_hash'   => '',
            'hash_date'   => ''
        ];
        return true;
    }

    // data validation
    public function is_correct( string $key ) : bool {

        if ( $key == 'id' and is_int( $this->data['id'] ) and $this->data['id'] > 0 and ceil( log10( $this->data['id'] )) <= 20 ) {
            return true;

        } elseif ( $key == 'user_status' and in_array( $this->data['user_status'], [ 'pending', 'approved', 'trash' ] )) {
            return true;

        } elseif ( $key == 'user_token' and is_string( $this->data['user_token'] ) and mb_strlen( $this->data['user_token'], 'utf-8' ) == 80 ) {
            return true;

        } elseif ( $key == 'user_email' and is_string( $this->data['user_email'] ) and mb_strlen( $this->data['user_email'], 'utf-8' ) <= 255 and preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $this->data['user_email'] ) ) {
            return true;

        } elseif ( $key == 'user_pass' and is_string( $this->data['user_pass'] ) and mb_strlen( $this->data['user_pass'], 'utf-8' ) >= 4 ) {
            return true;

        } elseif ( $key == 'user_hash' and is_string( $this->data['user_hash'] ) and mb_strlen( $this->data['user_hash'], 'utf-8' ) == 40 ) {
            return true;

        // TODO
        } elseif ( $key == 'hash_date' ) {
            return true;
        }

        return false;
    }

    // check that the user exists
    public function is_exists( array $args ) : bool {
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
    public function create_token() : bool {
        do {
            $user_token = bin2hex( random_bytes( 40 ));
            if( $this->is_exists( [['user_token', '=', $user_token]] )) {
                $repeat = true;
            } else {
                $repeat = false;
            }
        } while( $repeat );
        $this->data['user_token'] = $user_token;
        return true;
    }

    // generate random password
    public function create_pass() : bool {
        $user_pass = '';
        for( $i = 0; $i < 8; $i++ ) {
            $user_pass .= mt_rand( 0,9 );
        }
        $this->data['user_pass'] = $user_pass;
        return true;
    }

    // return a hash
    public function create_hash() : bool {
        $this->data['user_hash'] = sha1( $this->data['user_pass'] );
        return true;
    }

    // create a new user by user_email
    public function insert() : bool {
        
        $this->data['id'] = $this->db
        ->table('users')
        ->insertGetId([
            'date'        => $this->db::raw('now()'),
            'user_status' => $this->data['user_status'],
            'user_token'  => $this->data['user_token'],
            'user_email'  => $this->data['user_email'],
            'user_hash'   => $this->data['user_hash']
        ]);

        return empty( $this->data['id'] ) ? false : true;
    }

    // select user by key
    public function select( string $key ) : bool {
  
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
        }

        return empty( $user->id ) ? false : true;
    }

    // update user data
    public function update() : bool {

        $affected_rows = $this->db
        ->table('users')
        ->where([ ['id', '=', $user_id] ])
        ->update([
            'user_status' => $this->data['user_status'],
            'user_token' => $this->data['user_token'],
            'user_email' => $this->data['user_email'],
            'user_hash' => $this->data['user_hash']]);

        return $affected_rows > 0 ? true : false;
    }

}