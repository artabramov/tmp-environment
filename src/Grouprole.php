<?php

namespace artabramov\Echidna;

class Grouprole
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
            'group_id'   => 0,
            'group_role' => ''
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
            'group_id'   => 0,
            'group_role' => ''
        ];
    }

    private function is_exists( int $user_id, int $group_id ) : bool {
            
        $role = $this->db
        ->table('group_roles')
        ->select('id')
        ->where( 'user_id', '=', $user_id )
        ->where( 'group_id', '=', $group_id )
        ->first();

        return empty( $role->id ) ? false : true;
    }

    // insert a new role
    public function insert() : bool {

        $user_id = (int) $this->data[ 'user_id' ];
        $group_id = (int) $this->data[ 'group_id' ];
        $group_role = (string) $this->data[ 'group_role' ];

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

        } elseif( empty( $group_id )) {
            $this->error = 'group_id is empty';

        } elseif( strlen( strval( $group_id )) > 20 ) {
            $this->error = 'group_id is too long';

        } elseif( $this->is_exists( $user_id, $group_id ) ) {
            $this->error = 'user_id and group_id are exists';

        } elseif( empty( $group_role )) {
            $this->error = 'group_role is empty';

        } elseif( mb_strlen( $group_role, 'utf-8' ) > 255 ) {
            $this->error = 'group_role is is too long';

        } elseif( !preg_match("/^[a-z0-9_]/", $group_role )) {
            $this->error = 'group_role is incorrect';

        } else {

            $this->data['id'] = $this->db
            ->table('group_roles')
            ->insertGetId([
                'date'       => $this->db::raw('now()'),
                'user_id'    => $user_id,
                'group_id'   => $group_id,
                'group_role' => $group_role
            ]);

            if( empty( $this->data['id'] )) {
                $this->error = 'group_role insertion error';
            }
        }

        return empty( $this->error ) ? true : false;
    }

}
