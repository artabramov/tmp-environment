<?php

namespace artabramov\Echidna;

class User
{
    private $db;
    private $error;
    private $id;
    private $date;
    private $status;
    private $token;
    private $email;
    private $login;
    private $hash;
    private $restored;
    private $meta;


    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db       = $db;
        $this->error    = '';
        $this->id       = 0;
        $this->date     = '0000-00-00 00:00:00';
        $this->status   = '';
        $this->token    = '';
        $this->email    = '';
        $this->login    = '';
        $this->hash     = '';
        $this->restored = '0000-00-00 00:00:00';
        $this->meta     = [];
    }


    public function __set( string $key, $value ) {
        if( isset( $this->$key )) {
            $this->$key = $value;
        } else {
            $this->usermeta[ $key ] = $value;
        }
    }


    public function __get( string $key ) {
        if( isset( $this->$key )) {
            return $this->$key;
        } elseif( isset( $this->usermeta[ $key ] )) {
            return $this->usermeta[ $key ];
        }
    }


    public function has( string $key ) {

    }


    public function token_create() {
        do {
            $token = sha1( bin2hex( random_bytes( 64 )));
            if( $this->exists( [['token', '=', $token]] )) {
                $repeat = true;
            } else {
                $repeat = false;
            }
        } while( $repeat );
        return $token;
    }


    private function exists( array $args ) {
        if( empty( $args )) {
            return false;
        }
        $user = $this->db->table('users')->select('id');
        foreach( $args as $where ) {
            $user = $user->where( $where[0], $where[1], $where[2] );
        }
        $user = $user->first();
        return !empty( $user->id );
    }


    public function pass_create() {
        $user_pass = '';
        for( $i = 0; $i < 6; $i++ ) {
            $user_pass .= mt_rand( 0,9 );
        }
        return $user_pass;
    }


    public function hash_get( $value ) {
        return sha1( $value );
    }


    public function create() {

        $this->error = '';

        if( empty( $this->email )) {
            $this->error = 'User email is empty';

        } elseif( mb_strlen( $this->email, 'utf-8' ) > 255 ) {
            $this->error = 'User email is is too long';

        } elseif( !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $this->email )) {
            $this->error = 'User email is incorrect';

        } elseif( $this->exists( [['email', '=', $this->email]] )) {
            $this->error = 'User email is occupied';



            

        } else {

            $this->id = $this->db->table('users')->insertGetId([
                'date'     => $this->db::raw('now()'),
                'status'   => $this->status,
                'token'    => $this->token,
                'email'    => trim( strtolower( $this->email )),
                'hash'     => $this->hash,
                'restored' => $this->restored
            ]);

            if( empty( $this->id ) ) {
                $this->error = 'User insertion error';
            }
        }

        //return empty( $this->error );
    }
}