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
    private $user_hash;


    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db          = $db;
        $this->error       = '';
        $this->id          = 0;
        $this->date        = '0000-00-00 00:00:00';
        $this->user_status = '';
        $this->user_token  = '';
        $this->user_email  = '';
        $this->user_hash   = '';
    }


    public function __set( string $key, $value ) {
        if( isset( $this->$key )) {
            $this->$key = $value;
        }
    }


    public function __get( string $key ) {
        if( isset( $this->$key )) {
            return $this->$key;
        } else {
            return null;
        }
    }


    public function has( string $key ) {
        if( !empty( $this->$key )) {
            return true;
        } else {
            return false;
        }
    }


    private function clear() {
        $this->error       = '';
        $this->id          = 0;
        $this->date        = '0000-00-00 00:00:00';
        $this->user_status = '';
        $this->user_token  = '';
        $this->user_email  = '';
        $this->user_hash   = '';
    }


    public function create_token() {
        do {
            $user_token = bin2hex( random_bytes( 64 ));
            if( $this->is_exists( [['user_token', '=', $user_token]] )) {
                $repeat = true;
            } else {
                $repeat = false;
            }
        } while( $repeat );
        return $user_token;
    }


    private function is_exists( array $args ) {
        $user = $this->db->table('users')->select('id');
        foreach( $args as $where ) {
            $user = $user->where( $where[0], $where[1], $where[2] );
        }
        $user = $user->first();
        return !empty( $user->id );
    }


    public function create_pass( int $pass_length ) {
        $user_pass = '';
        for( $i = 0; $i < $pass_length; $i++ ) {
            $user_pass .= mt_rand( 0,9 );
        }
        return $user_pass;
    }

    public function user_create( string $user_email, string $user_hash ) {

        $this->clear();

        if( empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( mb_strlen( $user_email, 'utf-8' ) > 255 ) {
            $this->error = 'user_email is too long';

        } elseif( !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( $this->is_exists( [['user_email', '=', $user_email]] )) {
            $this->error = 'user_email is occupied';

        } elseif( empty( $user_hash )) {
            $this->error = 'user_hash is empty';

        } elseif( mb_strlen( $user_hash, 'utf-8' ) < 40 ) {
            $this->error = 'user_hash is too short';

        } elseif( mb_strlen( $user_hash, 'utf-8' ) > 40 ) {
            $this->error = 'user_hash is too long';

        } else {

            $user_id = $this->db->table('users')->insertGetId([
                'date'        => $this->db::raw('now()'),
                'user_status' => 'pending',
                'user_token'  => $this->create_token(),
                'user_email'  => $user_email,
                'user_hash'   => $user_hash
            ]);

            if( empty( $user_id ) ) {
                $this->error = 'user creation error';
                
            }
        }
    }

    public function user_select( string $key, int|string $value ) {
  
        $this->clear();

        if( empty( $key )) {
            $this->error = 'key is empty';

        } elseif( !in_array( $key, ['id', 'user_token', 'user_email'] )) {
            $this->error = 'key is incorrect';

        } elseif( empty( $value )) {
            $this->error = 'value is empty';
            
        } elseif( $key == 'id' and !is_int( $value )) {
            $this->error = 'value for user_id is incorrect';

        } elseif( $key == 'id' and mb_strlen( strval( $value ), 'utf-8' ) > 20 ) {
            $this->error = 'value for user_id is too long';

        } elseif( $key == 'user_token' and mb_strlen( $value, 'utf-8' ) < 128 ) {
            $this->error = 'value for user_token is too short';

        } elseif( $key == 'user_token' and mb_strlen( $value, 'utf-8' ) > 128 ) {
            $this->error = 'value for user_token is too long';

        } elseif( $key == 'user_token' and !$this->is_exists( [['user_token', '=', $value]] )) {
            $this->error = 'value for user_token not found';

        } elseif( $key == 'user_email' and mb_strlen( $value, 'utf-8' ) > 255 ) {
            $this->error = 'value fot user_email is too long';

        } elseif( $key == 'user_email' and !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $value )) {
            $this->error = 'value for user_email is incorrect';

        } else {

            $user = $this->db
            ->table( 'users' )
            ->where([[ $key, '=', $value ]])
            ->select( '*' )
            ->first();

            if( isset( $user->id )) {
                $this->id = $user->id;
                $this->date = $user->date;
                $this->user_status = $user->user_status;
                $this->user_token = $user->user_token;
                $this->user_email = $user->user_email;
                $this->user_hash = $user->user_hash;

            } else {
                $this->error = 'user not found';
            }
        }
    }


    public function user_auth( string $user_email, string $user_pass ) {

        $this->clear();

        $user_email = trim( strtolower( $user_email ));
        $user_pass  = trim( $user_pass );
        $user_hash  = $this->get_hash( $user_pass );

        if( empty( $user_email )) {
            $this->error = 'User email is empty';

        } elseif( mb_strlen( $user_email, 'utf-8' ) > 255 ) {
            $this->error = 'User email is is too long';

        } elseif( !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email )) {
            $this->error = 'User email is incorrect';

        } elseif( !$this->is_exists( [['user_email', '=', $user_email], ['user_status', '<>', 'trash']] )) {
            $this->error = "User email does not exist or has been deleted";

        } elseif( empty( $user_pass )) {
            $this->error = 'User password is empty';

        } elseif( !$this->is_exists( [['user_email', '=', $user_email], ['user_hash', '=', $user_hash]] )) {
            $this->error = "User password is incorrect";

        } else {

            $this->db
            ->table('users')
            ->where([ ['user_email', '=', $user_email], ['user_hash', '=', $user_hash] ])
            ->update([ 'user_status' => 'approved', 'user_hash' => '' ]);

            $user = $this->db
            ->table('users')
            ->select('*')
            ->where([ ['user_email', '=', $user_email] ])
            ->first();

            $this->id          = $user->id;
            $this->date        = $user->date;
            $this->user_status = $user->user_status;
            $this->user_token  = $user->user_token;
            $this->user_email  = $user->user_email;
            $this->user_hash   = $user->user_hash;

        }
    }


    public function user_exit( string $user_token ) {

        $user_token = trim( $user_token );

        $this->clear();

        if( empty( $user_token )) {
            $this->error = 'User token is empty';

        } elseif( mb_strlen( $user_token, 'utf-8' ) != 40 ) {
            $this->error = 'User token must be 40 characters';

        } elseif( !$this->is_exists( [['user_token', '=', $user_token], ['user_status', '<>', 'trash']] )) {
            $this->error = "User token is incorrect or user has been deleted";

        } else {

            $this->db
            ->table('users')
            ->where([ ['user_token', '=', $user_token] ])
            ->update([ 'user_token' => $this->create_token() ]);
        }
    }

}