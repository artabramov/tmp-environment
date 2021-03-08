<?php

namespace artabramov\Echidna;

class Role
{
    private $db;
    private $error;
    private $id;
    private $date;
    private $user_id;
    private $group_id;
    private $user_role;

    // construct *
    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db = $db;

        $this->error = '';
        $this->clear();
    }

    // set *
    public function __set( string $key, $value ) {}

    // get *
    public function __get( string $key ) {
        return isset( $this->$key ) ? $this->$key : null;
    }

    // clear data
    private function clear() {

        $this->id        = 0;
        $this->date      = '0000-00-00 00:00:00';
        $this->user_id   = 0;
        $this->group_id  = 0;
        $this->user_role = '';
    }

    // is error exists *
    public function is_error() {
        return !empty( $this->error );
    }

    // is data empty
    private function is_empty( string $key ): bool {
        return empty( $this->$key );
    }

    // data validation
    private function is_correct( string $key ) : bool {

        if ( $key == 'id' and is_int( $this->id ) and $this->id > 0 and ceil( log10( $this->id )) <= 20 ) {
            return true;

        } elseif ( $key == 'user_id' and is_int( $this->user_id ) and $this->user_id > 0 and ceil( log10( $this->user_id )) <= 20 ) {
            return true;

        } elseif ( $key == 'group_id' and is_int( $this->group_id ) and $this->group_id > 0 and ceil( log10( $this->group_id )) <= 20 ) {
            return true;

        } elseif ( $key == 'user_role' and in_array( $this->user_role, ['admin', 'editor', 'reader', 'invited']) ) {
            return true;
        }

        return false;
    }

    // check that the role exists
    private function is_exists( array $args ) : bool {

        $role = $this->db
            ->table('user_roles')
            ->select('id');

        foreach( $args as $where ) {
            $role = $role->where( $where[0], $where[1], $where[2] );
        }

        $role = $role->first();
        return empty( $role->id ) ? false : true;
    }

    // insert a new role
    private function insert() : bool {

        $this->id = $this->db
        ->table('user_roles')
        ->insertGetId([
            'date'      => $this->db::raw('now()'),
            'user_id'   => $this->user_id,
            'group_id'  => $this->group_id,
            'user_role' => $this->user_role
        ]);

        return empty( $this->id ) ? false : true;
    }

    // update
    private function update() : bool {

        $affected_rows = $this->db
            ->table('user_roles')
            ->where([ 
                ['user_id', '=', $this->user_id],
                ['group_id', '=', $this->group_id] ])
            ->update([ 
                'user_role' => $this->user_role ]);

        return is_int( $affected_rows ) ? true : false;
    }

    // select the role
    private function select() : bool {

        $role = $this->db
            ->table( 'user_roles' )
            ->where([[ 'user_id', '=', $this->user_id ], [ 'group_id', '=', $this->group_id ]])
            ->select( '*' )
            ->first();

        if( !empty( $role->id )) {
            $this->id        = $role->id;
            $this->date      = $role->date;
            $this->user_id   = $role->user_id;
            $this->group_id  = $role->group_id;
            $this->user_role = $role->user_role;
        }

        return empty( $role->id ) ? false : true;
    }

    // delete
    private function delete() : bool {

        $affected_rows = $this->db
            ->table('user_roles')
            ->where([ 
                ['user_id', '=', $this->user_id], 
                ['group_id', '=', $this->group_id] ])
            ->delete();

        return $affected_rows > 0 ? true : false;
    }

    // count roles of the group
    private function count( array $args ) : int {

        $role = $this->db->table('user_roles');

        foreach( $args as $where ) {
            $role = $role->where( $where[0], $where[1], $where[2] );
        }

        $role = $role->count();
        return $role;
    }

    // create role *
    public function set( int $user_id, int $group_id, string $user_role ) : bool {

        $this->error = '';
        $this->clear();

        $this->user_id   = $user_id;
        $this->group_id  = $group_id;
        $this->user_role = $user_role;

        if( $this->is_empty( 'user_id' )) {
            $this->error = 'user_id is empty';
        
        } elseif( !$this->is_correct( 'user_id' )) {
            $this->error = 'user_id is incorrect';
        
        } elseif( $this->is_empty( 'group_id' )) {
            $this->error = 'group_id is empty';
        
        } elseif( !$this->is_correct( 'group_id' )) {
            $this->error = 'group_id is incorrect';

        } elseif( $this->is_empty( 'user_role' )) {
            $this->error = 'user_role is empty';
        
        } elseif( !$this->is_correct( 'user_role' )) {
            $this->error = 'user_role is incorrect';

        } elseif( $this->is_exists( [['user_id', '=', $this->user_id], ['group_id', '=', $this->group_id]] )) {
            $this->error = 'role is exists';
        
        } elseif( !$this->insert()) {
            $this->error = 'role insert error';
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }

    // fetch the role *
    public function get( int $user_id, int $group_id, string $user_role = '' ) : bool {

        $this->error = '';
        $this->clear();

        $this->user_id   = $user_id;
        $this->group_id  = $group_id;
        $this->user_role = $user_role;

        if( $this->is_empty( 'user_id' )) {
            $this->error = 'user_id is empty';
        
        } elseif( !$this->is_correct( 'user_id' )) {
            $this->error = 'user_id is incorrect';
        
        } elseif( $this->is_empty( 'group_id' )) {
            $this->error = 'group_id is empty';
        
        } elseif( !$this->is_correct( 'group_id' )) {
            $this->error = 'group_id is incorrect';

        } elseif( empty( $this->user_role ) and !$this->is_exists( [['user_id', '=', $this->user_id], ['group_id', '=', $this->group_id]] )) {
            $this->error = 'role not found';

        } elseif( !empty( $this->user_role ) and !$this->is_exists( [['user_id', '=', $this->user_id], ['group_id', '=', $this->group_id], ['user_role', '=', $this->user_role]] )) {
            $this->error = 'role not found';

        } elseif( !$this->select() ) {
            $this->error = 'role select error';
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }
}
