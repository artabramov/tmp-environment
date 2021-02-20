<?php

namespace artabramov\Echidna;

class Usermeta
{
    private $db;
    private $error;
    private $data;


    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db    = $db;
        $this->error = '';
        $this->data  = [];
    }

    public function __set( string $key, $value ) {
        if( isset( $this->data[ $key ] )) {
            $this->data[ $key ] = $value;
        }
    }


    public function __get( string $key ) {
        if( isset( $this->data[ $key ] )) {
            return $this->data[ $key ];

        } else {
            return null;
        }
    }

    public function has( string $key ) : bool {
        if( isset( $this->data[ $key ] )) {
            return true;
        } else {
            return false;
        }
    }

    public function error() : string {
        return $this->error;
    }


    private function clear() {
        $this->error = '';
        $this->meta = [];
    }


    public function insert( int $user_id, string $meta_key, string $meta_value ) : bool {

        $this->clear();

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

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

            $meta_id = $this->db
            ->table('user_meta')
            ->insertGetId([
                'date'        => $this->db::raw('now()'),
                'user_id'     => $user_id,
                'meta_key'    => $meta_key,
                'meta_value'  => $meta_value
            ]);

            if( empty( $meta_id )) {
                $this->error = 'meta insertion error';
            }
        }

        return empty( $meta_id ) ? false : true;
    }

    /**
     * Select all metadata of the user.
     */
    public function select( int $user_id ) : bool {

        $this->clear();

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

        } else {

            $metas = $this->db
            ->table( 'user_meta' )
            ->where([[ 'user_id', '=', $user_id ]])
            ->select( '*' )
            ->get();

            if( !empty( $metas )) {
                foreach( $metas as $meta ) {
                    $this->data[ $meta->meta_key ] = $meta->meta_value;
                }
            }
        }

        return true;
    }


    public function update( int $user_id, string $meta_key, string $meta_value ) : bool {

        $this->clear();

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

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

            $this->db
            ->table('user_meta')
            ->where([ ['user_id', '=', $user_id], [ 'meta_key', '=', $meta_key ] ])
            ->updateOrCreate([ 'meta_value'  => $meta_value ]);

            return true;
        }
        return false;
    }


    public function delete( int $user_id, string $meta_key ) : bool {

        $this->clear();

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

        } elseif( empty( $meta_key )) {
            $this->error = 'meta_key is empty';

        } elseif( mb_strlen( $meta_key, 'utf-8' ) > 40 ) {
            $this->error = 'meta_key is is too long';

        } elseif( !preg_match("/^[a-z0-9_]/", $meta_key )) {
            $this->error = 'meta_key is incorrect';

        } else {

            $this->db
            ->table('user_meta')
            ->where([ ['user_id', '=', $user_id], [ 'meta_key', '=', $meta_key ] ])
            ->delete();
        }

        return empty( $this->error ) ? true : false;
    }



}