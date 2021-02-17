<?php

namespace artabramov\Echidna;

class Usermeta
{
    private $db;
    private $error;
    private $meta;


    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db    = $db;
        $this->error = '';
        $this->meta  = [];
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


    public function meta_create( int $user_id, string $meta_key, string $meta_value ) {

        $meta_key  = trim( $meta_key );
        $meta_value = trim( $meta_value );

        if( $user_id == 0 ) {
            $this->error = 'user_id is empty';

        } elseif( empty( $meta_key )) {
            $this->error = 'meta_key is empty';

        } elseif( mb_strlen( $meta_key, 'utf-8' ) > 40 ) {
            $this->error = 'meta_key is is too long';

        } elseif( !preg_match("/^[a-z0-9_]/", $meta_key )) {
            $this->error = 'meta_key is incorrect';

        } elseif( empty( $meta_value )) {
            $this->error = 'meta_value is empty';

        } elseif( mb_strlen( $meta_value, 'utf-8' ) > 255 ) {
            $this->error = 'user_value is is too long';

        } else {

            $meta_id = $this->db->table('usermeta')->insertGetId([
                'date'       => $this->db::raw('now()'),
                'user_id'    => $user_id,
                'meta_key'   => $meta_key,
                'meta_value' => $meta_value
            ]);

            if( empty( $meta_id )) {
                $this->error = 'insertion error';
            }
        }
    }



}