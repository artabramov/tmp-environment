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


    /*
    Erase user object data.
    */
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
            $user_token = sha1( bin2hex( random_bytes( 64 )));
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


    public function create_pass() {
        $user_pass = '';
        for( $i = 0; $i < 6; $i++ ) {
            $user_pass .= mt_rand( 0,9 );
        }
        return $user_pass;
    }


    public function get_hash( $value ) {
        return sha1( $value );
    }


    public function user_create( string $user_email, string $user_hash ) {

        $user_email = trim( strtolower( $user_email ));

        $this->clear();

        if( empty( $user_email )) {
            $this->error = 'User email is empty';

        } elseif( mb_strlen( $user_email, 'utf-8' ) > 255 ) {
            $this->error = 'User email is is too long';

        } elseif( !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email )) {
            $this->error = 'User email is incorrect';

        } elseif( $this->is_exists( [['user_email', '=', $user_email]] )) {
            $this->error = 'User email is occupied';

        } elseif( empty( $user_hash )) {
            $this->error = 'User hash is empty';

        } elseif( mb_strlen( $user_hash, 'utf-8' ) != 40 ) {
            $this->error = 'User hash must be 40 characters';

        } else {

            $user_id = $this->db->table('users')->insertGetId([
                'date'        => $this->db::raw('now()'),
                'user_status' => 'pending',
                'user_token'  => $this->create_token(),
                'user_email'  => $user_email,
                'user_hash'   => $user_hash
            ]);

            if( empty( $user_id ) ) {
                $this->error = 'User insertion error';
            }
        }
    }


    public function user_auth( $user_email, $user_pass ) {

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