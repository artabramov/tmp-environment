<?php

namespace artabramov\Echidna;

class Group
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
            'group_name' => ''
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

    // check is data has a value
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
            'group_name' => ''
        ];
    }

    // insert a new group
    public function insert() : bool {

        $user_id = (int) $this->data[ 'user_id' ];
        $group_name = (string) $this->data[ 'group_name' ];

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

        } elseif( empty( $group_name )) {
            $this->error = 'group_name is empty';

        } elseif( mb_strlen( $group_name, 'utf-8' ) > 255 ) {
            $this->error = 'group_name is is too long';

        } else {

            $this->data['id'] = $this->db
            ->table('groups')
            ->insertGetId([
                'date'       => $this->db::raw('now()'),
                'user_id'    => $user_id,
                'group_name' => $group_name
            ]);

            if( empty( $this->data['id'] )) {
                $this->error = 'group insertion error';
            }
        }

        return empty( $this->error ) ? true : false;
    }

}
