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

    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db = $db;

        $this->error = '';
        $this->clear();
    }

    public function __set( string $key, $value ) {
    }

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

    // is error *
    public function is_error() : bool {
        return !empty( $this->error );
    }

    // data validation
    private function is_correct( string $key, $value ) : bool {

        if ( $key == 'id' and !empty( $value ) and is_int( $value ) ) {
            return true;

        } elseif ( $key == 'user_id' and !empty( $value ) and is_int( $value ) ) {
            return true;

        } elseif ( $key == 'group_id' and !empty( $value ) and is_int( $value ) ) {
            return true;

        } elseif ( $key == 'user_role' and !empty( $value ) and in_array( $value, ['admin', 'editor', 'reader', 'invited']) ) {
            return true;
        }

        return false;
    }

    // is exists
    private function is_exists( array $args ) : bool {

        $role = $this->db
            ->table('user_roles')
            ->select('id')
            ->where( $args )
            ->first();

        return empty( $role->id ) ? false : true;
    }

    // insert a new role
    private function insert( array $data ) : bool {

        $this->clear();
        $data['date'] = $this->db::raw('now()');

        $role_id = $this->db
            ->table('user_roles')
            ->insertGetId( $data );

        if( !empty( $role_id ) ) {
            $this->id = $role_id;

            foreach( $data as $key=>$value ) {
                $this->$key = $value;
            }

            return true;
        }

        return false;
    }

    // update the role
    private function update( array $where, array $update ) : bool {

        $this->clear();
        $affected_rows = $this->db
            ->table('user_roles')
            ->where( $where )
            ->update( $update );

        if( is_int( $affected_rows ) ) {
            foreach( $where as $value ) {
                $key = $value[0];
                $this->$key = $value[2];
            }

            foreach( $update as $key=>$value ) {
                $this->$key = $value;
            }

            return true;
        }

        return false;
    }

    // select the role
    private function select( array $where ) : bool {

        $this->clear();
        $role = $this->db
            ->table( 'user_roles' )
            ->where( $where )
            ->select( '*' )
            ->first();

        if( !empty( $role->id )) {
            $this->id        = $role->id;
            $this->date      = $role->date;
            $this->user_id   = $role->user_id;
            $this->group_id  = $role->group_id;
            $this->user_role = $role->user_role;

            return true;
        }

        return false;
    }

    // delete
    private function delete( array $where ) : bool {

        $affected_rows = $this->db
            ->table('user_roles')
            ->where( $where )
            ->delete();

        if( is_int( $affected_rows ) ) {
            $this->clear();
            return true;
        }

        return false;
    }

    // count roles
    private function count( array $where ) : int {

        $roles_count = $this->db
            ->table('user_roles')
            ->where( $where )
            ->count();

        return $roles_count;
    }

    // create role *
    public function set( int $user_id, int $group_id, string $user_role ) : bool {

        $this->error = '';

        if( !$this->is_correct( 'user_id', $user_id )) {
            $this->error = 'user_id is incorrect';
        
        } elseif( !$this->is_correct( 'group_id', $group_id )) {
            $this->error = 'group_id is incorrect';
        
        } elseif( !$this->is_correct( 'user_role', $user_role )) {
            $this->error = 'user_role is incorrect';

        } else {

            if( $this->is_exists( [['user_id', '=', $user_id], ['group_id', '=', $group_id]] )) {
                if( !$this->update( [[ 'user_id', '=', $user_id ], [ 'group_id', '=', $group_id ]], [ 'user_role' => $user_role ] )) {
                    $this->error = 'role update error';
                }

            } else {
                if( !$this->insert( [ 'user_id' => $user_id, 'group_id' => $group_id, 'user_role' => $user_role ] )) {
                    $this->error = 'role insert error';
                }
            }
        }

        return $this->is_error() ? false : true;
    }

    // fetch the role *
    public function get( int $user_id, int $group_id, string $user_role = '' ) : bool {

        $this->error = '';
        
        if( !$this->is_correct( 'user_id', $user_id )) {
            $this->error = 'user_id is incorrect';
        
        } elseif( !$this->is_correct( 'group_id', $group_id )) {
            $this->error = 'group_id is incorrect';

        } elseif( empty( $user_role ) and !$this->is_exists( [['user_id', '=', $user_id], ['group_id', '=', $group_id]] )) {
            $this->error = 'role not found';

        } elseif( !empty( $user_role ) and !$this->is_exists( [['user_id', '=', $user_id], ['group_id', '=', $group_id], ['user_role', '=', $user_role]] )) {
            $this->error = 'role not found';

        } elseif( !$this->select( [['user_id', '=', $user_id], ['group_id', '=', $group_id]] ) ) {
            $this->error = 'role select error';
        }

        return $this->is_error() ? false : true;
    }

    // delete role *
    public function unset( int $user_id, int $group_id ) : bool {

        $this->error = '';

        if( !$this->is_correct( 'user_id', $user_id )) {
            $this->error = 'user_id is incorrect';
        
        } elseif( !$this->is_correct( 'group_id', $group_id )) {
            $this->error = 'group_id is incorrect';

        } elseif( !$this->is_exists( [['user_id', '=', $user_id], ['group_id', '=', $group_id]] )) {
            $this->error = 'role not found';

        } elseif( !$this->select( [['user_id', '=', $user_id], ['group_id', '=', $group_id]] ) ) {
            $this->error = 'role select error';

        } elseif( $this->user_role == 'admin' and $this->count( [['group_id', '=', $group_id], ['user_role', '=', 'admin']] ) <= 1 ) {
            $this->error = 'cannot delete last admin role';
        
        } elseif( !$this->delete( [['user_id', '=', $user_id], ['group_id', '=', $group_id]] )) {
            $this->error = 'role delete error';
        }

        return $this->is_error() ? false : true;
    }
}
