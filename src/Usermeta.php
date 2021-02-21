<?php

namespace artabramov\Echidna;

class Usermeta
{
    private $db;
    private $error;
    private $data;

    // create the object
    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db    = $db;
        $this->error = '';
        $this->data  = [
            'id'         => 0,
            'date'       => '',
            'user_id'    => 0,
            'meta_key'   => '',
            'meta_value' => ''
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
        if( $key == 'error' ) {
            return $this->error;
        } elseif( array_key_exists( $key, $this->data ) ) {
            return $this->data[ $key ];
        }
        return null;
    }

    // check is key has a value
    public function has( string $key ) : bool {
        if( $key == 'error' and !empty( $this->error ) ) {
            return true;
        } elseif( !empty( $this->data[ $key ] ) ) {
            return true;
        }
        return false;
    }

    // clear error and data
    public function clear() {
        $this->error = '';
        $this->data  = [
            'id'         => 0,
            'date'       => '',
            'user_id'    => 0,
            'meta_key'   => '',
            'meta_value' => ''
        ];
    }

    // insert a new meta (user_id, meta_key, meta_value)
    public function insert() : bool {

        $user_id = $this->data[ 'user_id' ];
        $meta_key = $this->data[ 'meta_key' ];
        $meta_value = $this->data[ 'meta_value' ];

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !is_int( $user_id ) ) {
            $this->error = 'user_id is not an integer';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

        } elseif( empty( $meta_key )) {
            $this->error = 'meta_key is empty';

        } elseif( !is_string( $meta_key ) ) {
            $this->error = 'meta_key is not a string';

        } elseif( mb_strlen( $meta_key, 'utf-8' ) > 40 ) {
            $this->error = 'meta_key is is too long';

        } elseif( !preg_match("/^[a-z0-9_]/", $meta_key )) {
            $this->error = 'meta_key is incorrect';

        } elseif( empty( $meta_value )) {
            $this->error = 'meta_value is empty';

        } elseif( !is_string( $meta_value ) ) {
            $this->error = 'meta_value is not a string';

        } elseif( mb_strlen( $meta_value, 'utf-8' ) > 255 ) {
            $this->error = 'meta_value is is too long';

        } else {

            $this->data['id'] = $this->db
            ->table('user_meta')
            ->insertGetId([
                'date'        => $this->db::raw('now()'),
                'user_id'     => $user_id,
                'meta_key'    => $meta_key,
                'meta_value'  => $meta_value
            ]);

            if( empty( $this->data['id'] )) {
                $this->error = 'meta insertion error';
            }
        }

        return empty( $this->error ) ? true : false;
    }

    // TODO

    private function is_exists( int $user_id, string $meta_key ) : bool {
            
        $usermeta = $this->db
        ->table('user_meta')
        ->select('id')
        ->where( 'user_id', '=', $user_id )
        ->where( 'meta_key', '=', $meta_key )
        ->first();

    return empty( $usermeta->id ) ? false : true;
    }

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

    // update (or insert) the usermeta
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

            if( $this->is_exists( $user_id, $meta_key ) ) {
                $this->db
                ->table('user_meta')
                ->where([ ['user_id', '=', $user_id], [ 'meta_key', '=', $meta_key ] ])
                ->update([ 'meta_value'  => $meta_value ]);

            } else {
                $this->db
                ->table('user_meta')
                ->insert([
                    'date'        => $this->db::raw('now()'),
                    'user_id'     => $user_id,
                    'meta_key'    => $meta_key,
                    'meta_value'  => $meta_value
                ]);
            }
        }

        return empty( $this->error ) ? true : false;
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