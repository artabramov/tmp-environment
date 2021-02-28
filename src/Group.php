<?php

namespace artabramov\Echidna;

class Group
{
    private $db;
    private $data;

    // create the object
    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db    = $db;
        $this->data  = [
            'id'           => 0,
            'date'         => '',
            'user_id'      => 0,
            'group_status' => '',
            'group_name'   => ''
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

    // check is data has a value
    public function has( string $key ) : bool {
        if( !empty( $this->data[ $key ] ) ) {
            return true;
        }
        return false;
    }

    // clear error and data
    public function clear() {
        $this->data  = [
            'id'           => 0,
            'date'         => '',
            'user_id'      => 0,
            'group_status' => '',
            'group_name'   => ''
        ];
    }

    // data validation
    public function is_correct( string $key ) : bool {

        if ( $key == 'id' and is_int( $this->data['id'] ) and $this->data['id'] > 0 and ceil( log10( $this->data['id'] )) <= 20 ) {
            return true;

        } elseif ( $key == 'user_id' and is_int( $this->data['user_id'] ) and $this->data['user_id'] > 0 and ceil( log10( $this->data['user_id'] )) <= 20 ) {
            return true;

        } elseif ( $key == 'group_status' and is_string( $this->data['group_status'] ) and mb_strlen( $this->data['group_status'], 'utf-8' ) <= 40 and preg_match("/^[a-z0-9_-]/", $this->data['group_status'] ) ) {
            return true;

        } elseif ( $key == 'group_name' and is_string( $this->data['group_name'] ) and mb_strlen( $this->data['group_name'], 'utf-8' ) <= 255 ) {
            return true;
        }

        return false;
    }


    // check that the group exists
    public function is_exists( array $args ) : bool {

        $group = $this->db
            ->table('groups')
            ->select('id');

        foreach( $args as $where ) {
            $group = $group->where( $where[0], $where[1], $where[2] );
        }

        $group = $group->first();
        return empty( $group->id ) ? false : true;
    }

    // insert a new group
    public function insert() : bool {

        $this->data['id'] = $this->db
        ->table('groups')
        ->insertGetId([
            'date'         => $this->db::raw('now()'),
            'user_id'      => $this->data['user_id'],
            'group_status' => $this->data['group_status'],
            'group_name'   => $this->data['group_name']
        ]);

        return empty( $this->data['id'] ) ? false : true;
    }

    // update
    public function update() : bool {

        $affected_rows = $this->db
            ->table('groups')
            ->where([ ['id', '=', $this->data['id'] ] ])
            ->update([ 
                'group_status' => $this->data['group_status'],
                'group_name'   => $this->data['group_name'] ]);

        return $affected_rows > 0 ? true : false;
    }


}
