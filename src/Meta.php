<?php

namespace artabramov\Echidna;

class Meta
{
    private $db;
    private $error;
    private $id;
    private $date;
    private $user_id;
    private $meta_key;
    private $meta_value;

    // construct *
    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db = $db;

        $this->error = '';
        $this->clear();
    }

    // set *
    public function __set( string $key, $value ) {
    }

    // get *
    public function __get( string $key ) {
        return isset( $this->$key ) ? $this->$key : null;
    }

    // clear data
    private function clear() {

        $this->id         = 0;
        $this->date       = '0000-00-00 00:00:00';
        $this->user_id    = 0;
        $this->meta_key   = '';
        $this->meta_value = '';
    }

    // is error exists *
    public function is_error() {
        return !empty( $this->error );
    }

    // is data correct
    private function is_correct( string $key, $value ) : bool {

        if ( $key == 'id' and !empty( $value ) and is_int( $value ) ) {
            return true;

        } elseif( $key == 'user_id' and !empty( $value ) and is_int( $value ) ) {
            return true;

        } elseif ( $key == 'meta_key' and !empty( $value ) and is_string( $value ) and mb_strlen( $value, 'utf-8' ) <= 40 and preg_match("/^[a-z0-9_-]/", $value ) ) {
            return true;

        } elseif ( $key == 'meta_value' and is_string( $value ) and mb_strlen( $value, 'utf-8' ) <= 255 ) {
            return true;
        }

        return false;
    }

    // is exists
    private function is_exists( array $args ) : bool {

        $meta = $this->db
            ->table('user_meta')
            ->select('id')
            ->where( $args )
            ->first();

        return empty( $meta->id ) ? false : true;
    }

    // insert meta
    private function insert( array $data ) : bool {

        $data['date'] = $this->db::raw('now()');

        $meta_id = $this->db
            ->table('user_meta')
            ->insertGetId( $data );

        if( !empty( $meta_id ) ) {
            $this->id = $meta_id;

            foreach( $data as $key=>$value ) {
                $this->$key = $value;
            }
        }

        return empty( $meta_id ) ? false : true;
    }

    // update meta
    private function update( array $where, array $update ) : bool {

        $affected_rows = $this->db
            ->table('user_meta')
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

    // select meta
    private function select( array $where ) : bool {

        $meta = $this->db
            ->table( 'user_meta' )
            ->where( $where )
            ->select( '*' )
            ->first();

        if( !empty( $meta->id )) {
            $this->id         = $meta->id;
            $this->date       = $meta->date;
            $this->user_id    = $meta->user_id;
            $this->meta_key   = $meta->meta_key;
            $this->meta_value = $meta->meta_value;
        }

        return empty( $meta->id ) ? false : true;
    }

    // delete meta
    private function delete( array $where ) : bool {

        $affected_rows = $this->db
            ->table('user_meta')
            ->where( $where )
            ->delete();

        return is_int( $affected_rows ) ? true : false;
    }

    // insert/update meta *
    public function set( int $user_id, string $meta_key, string $meta_value ) : bool {
        
        $this->error = '';
        $this->clear();

        if( !$this->is_correct( 'user_id', $user_id )) {
            $this->error = 'user_id is incorrect';
        
        } elseif( !$this->is_correct( 'meta_key', $meta_key )) {
            $this->error = 'meta_key is incorrect';

        } elseif( !$this->is_correct( 'meta_value', $meta_value )) {
            $this->error = 'meta_value is incorrect';
        
        } else {

            if( $this->is_exists( [['user_id', '=', $user_id], ['meta_key', '=', $meta_key]] )) {

                $where = [
                    [ 'user_id', '=', $user_id ], 
                    [ 'meta_key', '=', $meta_key ]];

                $data = [ 'meta_value' => $meta_value ];

                if( !$this->update( $where, $data )) {
                    $this->error = 'meta update error';
                }

            } else {

                $data = [
                    'user_id' => $user_id, 
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value];

                if( !$this->insert( $data )) {
                    $this->error = 'meta insert error';
                }
            }
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }

    // select meta *
    public function get( int $user_id, string $meta_key ) : bool {

        $this->error = '';
        $this->clear();

        if( !$this->is_correct( 'user_id', $user_id )) {
            $this->error = 'user_id is incorrect';
        
        } elseif( !$this->is_correct( 'meta_key', $meta_key )) {
            $this->error = 'meta_key is incorrect';
        
        } elseif( !$this->is_exists( [['user_id', '=', $user_id], ['meta_key', '=', $meta_key]] )) {
            $this->error = 'meta not found';

        } elseif( !$this->select( [['user_id', '=', $user_id], ['meta_key', '=', $meta_key]] )) {
            $this->error = 'meta select error';
        }

        if( $this->is_error() ) {
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // delete meta *
    public function unset( int $user_id, string $meta_key ) : bool {

        $this->error = '';
        $this->clear();

        if( !$this->is_correct( 'user_id', $user_id )) {
            $this->error = 'user_id is incorrect';
        
        } elseif( !$this->is_correct( 'meta_key', $meta_key )) {
            $this->error = 'meta_key is incorrect';

        } elseif( !$this->is_exists( [['user_id', '=', $user_id], ['meta_key', '=', $meta_key]] )) {
            $this->error = 'meta not found';

        } elseif( !$this->delete( [['user_id', '=', $user_id], ['meta_key', '=', $meta_key]] )) {
            $this->error = 'meta delete error';
        }

        if( $this->is_error() ) {
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

}
