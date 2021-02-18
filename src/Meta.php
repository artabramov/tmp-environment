<?php

namespace artabramov\Echidna;

class Meta
{
    private $db;
    private $error;


    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db    = $db;
        $this->error = '';
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


    public function meta_create( string $parent_type, int $parent_id, int $user_id, string $meta_key, string $meta_value ) {

        $parent_type = trim( $parent_type );
        $meta_key    = trim( $meta_key );
        $meta_value  = trim( $meta_value );

        if( empty( $parent_type )) {
            $this->error = 'parent_type is empty';

        } elseif( !in_array( $parent_type, [ 'user', 'desk', 'post' ] )) {
            $this->error = 'parent_type is incorrect';

        } elseif( empty( $parent_id )) {
            $this->error = 'parent_id is empty';

        } elseif( $user_id == 0 ) {
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
            $this->error = 'meta_value is is too long';

        } else {

            $meta_id = $this->db->table('meta')->insertGetId([
                'date'        => $this->db::raw('now()'),
                'parent_type' => $parent_type,
                'parent_id'   => $parent_id,
                'user_id'     => $user_id,
                'meta_key'    => $meta_key,
                'meta_value'  => $meta_value
            ]);

            if( empty( $meta_id )) {
                $this->error = 'meta insertion error';
            }
        }
    }



}