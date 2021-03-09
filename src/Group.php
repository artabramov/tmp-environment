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

        } elseif ( $key == 'group_status' and in_array( $this->group_status, ['public', 'private', 'trash']) ) {
            return true;

        } elseif ( $key == 'group_name' and is_string( $this->group_name ) and mb_strlen( $this->group_name, 'utf-8' ) <= 255 ) {
            return true;
        }

        return false;
    }

    // is group exists
    private function is_exists( array $args ) : bool {

        $group = $this->db
            ->table('groups')
            ->select('id');

        foreach( $args as $where ) {
            $group = $group->where( $where[0], $where[1], $where[2] );
        }

        $group = $group->first();
        return empty( $group->id ) ? false : true;
    }

    // insert group
    private function insert() : bool {

        $this->id = $this->db
            ->table('groups')
            ->insertGetId([
            'date'         => $this->db::raw('now()'),
            'user_id'      => $this->user_id,
            'group_status' => $this->group_status,
            'group_name'   => $this->group_name
        ]);

        return empty( $this->id ) ? false : true;
    }

    // select group
    private function select() : bool {

        $group = $this->db
            ->table( 'groups' )
            ->where([[ 'id', '=', $this->id ]])
            ->select( '*' )
            ->first();

        if( !empty( $meta->id )) {
            $this->id           = $group->id;
            $this->date         = $group->date;
            $this->user_id      = $group->user_id;
            $this->group_status = $group->group_status;
            $this->group_name   = $group->group_name;
        }

        return empty( $group->id ) ? false : true;
    }

    // update group
    private function update( array $keys ) : bool {

        $data = [];
        foreach( $keys as $key ) {
            $data[ $key ] = $this->$key;
        }        
        
        $affected_rows = $this->db
            ->table('groups')
            ->where([[ 'id', '=', $this->id ]])
            ->update( $data );

        return is_int( $affected_rows ) ? true : false;
    }

    // create group *
    public function create( int $user_id, string $group_status, string $group_name ) : bool {

        $this->error = '';
        $this->clear();

        $this->user_id      = $user_id;
        $this->group_status = $group_status;
        $this->group_name   = $group_name;

        if( $this->is_empty( 'user_id' )) {
            $this->error = 'user_id is empty';
        
        } elseif( !$this->is_correct( 'user_id' )) {
            $this->error = 'user_id is incorrect';
        
        } elseif( $this->is_empty( 'group_status' )) {
            $this->error = 'group_status is empty';
        
        } elseif( !$this->is_correct( 'group_status' )) {
            $this->error = 'group_status is incorrect';

        } elseif( $this->is_empty( 'group_name' )) {
            $this->error = 'group_name is empty';
        
        } elseif( !$this->is_correct( 'group_name' )) {
            $this->error = 'group_name is incorrect';
        
        } elseif( !$this->insert()) {
            $this->error = 'group insert error';
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }

    // rename group *
    public function rename( int $group_id, string $group_name ) : bool {

        $this->error = '';
        $this->clear();
        
        $this->id         = $group_id;
        $this->group_name = $group_name;

        if( $this->is_empty( 'id' )) {
            $this->error = 'group_id is empty';
        
        } elseif( !$this->is_correct( 'id' )) {
            $this->error = 'group_id is incorrect';

        } elseif( !$this->is_exists( [['id', '=', $this->id], ['group_status', '<>', 'trash']] )) {
            $this->error = 'group is trash';

        } elseif( $this->is_empty( 'group_name' )) {
            $this->error = 'group_name is empty';
        
        } elseif( !$this->is_correct( 'group_name' )) {
            $this->error = 'group_name is incorrect';

        } elseif( !$this->update( [ 'group_name' ] )) {
            $this->error = 'group name update error';
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }

    // change status *
    public function restatus( int $group_id, string $group_status ) : bool {

        $this->error = '';
        $this->clear();
        
        $this->id           = $group_id;
        $this->group_status = $group_status;

        if( $this->is_empty( 'id' )) {
            $this->error = 'group_id is empty';
        
        } elseif( !$this->is_correct( 'id' )) {
            $this->error = 'group_id is incorrect';

        } elseif( !$this->is_exists( [['id', '=', $this->id], ['group_status', '<>', 'private']] )) {
            $this->error = 'group is not available';

        } elseif( $this->is_empty( 'group_status' )) {
            $this->error = 'group_status is empty';
        
        } elseif( !in_array( $this->group_status, ['public', 'trash'] )) {
            $this->error = 'group_status is incorrect';

        } elseif( !$this->update( [ 'group_status' ] )) {
            $this->error = 'group status update error';
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }

    // get the group *
    public function get( int $group_id, string $group_status = '' ) : bool {

        $this->error = '';
        $this->clear();

        $this->id = $group_id;
        $this->group_status = $group_status;

        if( $this->is_empty( 'id' )) {
            $this->error = 'group_id is empty';
       
        } elseif( !$this->is_correct( 'id' )) {
            $this->error = 'group_id is incorrect';

        } elseif( empty( $this->group_status ) and !$this->is_exists( [['id', '=', $this->id]] ) ) {
            $this->error = 'group not found';

        } elseif( !empty( $this->group_status ) and !$this->is_exists( [['id', '=', $this->id], ['group_status', '=', $this->group_status]] )) {
            $this->error = 'group not found';

        } elseif( !$this->select() ) {
            $this->error = 'group select error';
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }

    // delete the group *
    public function delete( int $group_id ) : bool {

        $this->error = '';
        $this->clear();
        
        $this->id = $group_id;

        if( $this->is_empty( 'id' )) {
            $this->error = 'group_id is empty';
        
        } elseif( !$this->is_correct( 'id' )) {
            $this->error = 'group_id is incorrect';

        } elseif( !$this->is_exists( [['id', '=', $this->id], ['group_status', '=', 'trash']] )) {
            $this->error = 'group is not found';

        } else {
            // TODO
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
 
    }

}
