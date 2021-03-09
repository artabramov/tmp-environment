<?php

namespace artabramov\Echidna;

class Group
{
    private $db;
    private $error;
    private $id;
    private $date;
    private $user_id;
    private $group_status;
    private $group_name;

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

        $this->id           = 0;
        $this->date         = '0000-00-00 00:00:00';
        $this->user_id      = 0;
        $this->group_status = '';
        $this->group_name   = '';
    }

    // is error exists *
    public function is_error() {
        return !empty( $this->error );
    }

    // data validation
    private function is_correct( string $key, $value ) : bool {

        if ( $key == 'id' and !empty( $value ) and is_int( $value ) ) {
            return true;

        } elseif ( $key == 'user_id' and !empty( $value ) and is_int( $value ) ) {
            return true;

        } elseif ( $key == 'group_status' and in_array( $value, ['public', 'private', 'trash']) ) {
            return true;

        } elseif ( $key == 'group_name' and !empty( $value ) and is_string( $value ) and mb_strlen( $value, 'utf-8' ) <= 255 ) {
            return true;
        }

        return false;
    }

    // is exists
    private function is_exists( array $args ) : bool {

        $group = $this->db
            ->table('groups')
            ->select('id')
            ->where( $args )
            ->first();

        return empty( $group->id ) ? false : true;
    }

    // insert group
    private function insert( array $data ) : bool {

        $data['date'] = $this->db::raw('now()');

        $group_id = $this->db
            ->table('groups')
            ->insertGetId( $data );

        if( !empty( $group_id ) ) {
            $this->id = $group_id;

            foreach( $data as $key=>$value ) {
                $this->$key = $value;
            }
        }

        return empty( $group_id ) ? false : true;
    }

    // update group
    private function update( array $where, array $update ) : bool {

        $affected_rows = $this->db
            ->table('groups')
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
        }

        return is_int( $affected_rows ) ? true : false;
    }

    // select group
    private function select( array $where ) : bool {

        $group = $this->db
            ->table( 'groups' )
            ->where( $where )
            ->select( '*' )
            ->first();

        if( !empty( $role->id )) {
            $this->id           = $group->id;
            $this->date         = $group->date;
            $this->user_id      = $group->user_id;
            $this->group_status = $group->group_status;
            $this->group_name   = $group->group_name;
        }

        return empty( $group->id ) ? false : true;
    }

    // delete
    private function delete( array $where ) : bool {

        $affected_rows = $this->db
            ->table('groups')
            ->where( $where )
            ->delete();

        return is_int( $affected_rows ) ? true : false;
    }

    // create group *
    public function create( int $user_id, string $group_status, string $group_name ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'user_id', $user_id )) {
            $this->error = 'user_id is incorrect';
        
        } elseif( !$this->is_correct( 'group_status', $group_status )) {
            $this->error = 'group_status is incorrect';
        
        } elseif( !$this->is_correct( 'group_name', $group_name )) {
            $this->error = 'group_name is incorrect';
        
        } else {

            $data = [
                'user_id'      => $user_id,
                'group_status' => $group_status,
                'group_name'   => $group_name ];

            if( !$this->insert( $data )) {
                $this->error = 'group insert error';
            }
        }

        if( $this->is_error() ) {
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // rename group *
    public function rename( int $group_id, string $group_name ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'id', $group_id )) {
            $this->error = 'group_id is incorrect';

        } elseif( !$this->is_exists( [['id', '=', $group_id], ['group_status', '<>', 'trash']] )) {
            $this->error = 'group is trash';
        
        } elseif( !$this->is_correct( 'group_name', $group_name )) {
            $this->error = 'group_name is incorrect';

        } else {
        
            $where = [['id', '=', $group_id]];
            $update = [ 'group_name' => $group_name ];
        
            if( !$this->update( $where, $update )) {
                $this->error = 'group update error';
            }
        }

        if( $this->is_error() ) { 
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // change status *
    public function restatus( int $group_id, string $group_status ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'id', $group_id )) {
            $this->error = 'group_id is incorrect';

        } elseif( !$this->is_correct( 'group_status', $group_status ) or $group_status == 'private') {
            $this->error = 'group_status is incorrect';

        } elseif( !$this->is_exists( [['id', '=', $group_id]] )) {
            $this->error = 'group not found';

        } else {
            
            $where = [['id', '=', $group_id]];
            $update = [ 'group_status' => $group_status ];
        
            if( !$this->update( $where, $update )) {
                $this->error = 'group update error';
            }
        }

        if( $this->is_error() ) { 
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // get the group *
    public function get( int $group_id ) : bool {

        $this->error = '';
        $this->clear();

        if( !$this->is_correct( 'id', $group_id )) {
            $this->error = 'group_id is incorrect';

        } elseif( !$this->is_exists( [['id', '=', $group_id]] ) ) {
            $this->error = 'group not found';

        } elseif( !$this->select( [['id', '=', $group_id]] ) ) {
            $this->error = 'group select error';
        }

        if( $this->is_error() ) {
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // delete the group *
    public function unset( int $group_id ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'group_id' )) {
            $this->error = 'group_id is incorrect';

        } elseif( !$this->is_exists( [['id', '=', $this->id], ['group_status', '=', 'trash']] )) {
            $this->error = 'group not found';

        } elseif( !$this->delete( [['id', '=', $group_id]] ) ) {
            $this->error = 'group delete error';
        }

        if( $this->is_error() ) {
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

}
