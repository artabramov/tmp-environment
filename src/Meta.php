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
        $this->error      = '';
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

    // is data empty
    private function is_empty( string $key ): bool {

        if( $key = 'date' )) {
            return $this->date == '0000-00-00 00:00:00' or empty( $this->date );
        }
        return empty( $this->$key );
    }

    // is data correct
    private function is_correct( string $key ) : bool {

        if ( $key == 'id' and is_int( $this->id ) and $this->id > 0 and ceil( log10( $this->id )) <= 20 ) {
            return true;

        } elseif ( $key == 'user_id' and is_int( $this->user_id ) and $this->user_id > 0 and ceil( log10( $this->user_id )) <= 20 ) {
            return true;

        } elseif ( $key == 'meta_key' and is_string( $this->meta_key ) and mb_strlen( $this->meta_key, 'utf-8' ) <= 40 and preg_match("/^[a-z0-9_-]/", $this->meta_key ) ) {
            return true;

        } elseif ( $key == 'meta_value' and is_string( $this->meta_value ) and mb_strlen( $this->meta_value, 'utf-8' ) <= 255 ) {
            return true;
        }

        return false;
    }

    // is meta exists
    private function is_exists( array $args ) : bool {

        $meta = $this->db
            ->table('user_meta')
            ->select('id');

        foreach( $args as $where ) {
            $meta = $meta->where( $where[0], $where[1], $where[2] );
        }

        $meta = $meta->first();
        return empty( $meta->id ) ? false : true;
    }

    // insert meta
    private function insert() : bool {

        $this->id = $this->db
            ->table('user_meta')
            ->insertGetId([
            'date'        => $this->db::raw('now()'),
            'user_id'     => $this->user_id,
            'meta_key'    => $this->meta_key,
            'meta_value'  => $this->meta_value 
        ]);

        return empty( $this->id ) ? false : true;
    }

    // update meta
    private function update() : bool {

        $affected_rows = $this->db
            ->table('user_meta')
            ->where([ ['user_id', '=', $this->user_id], [ 'meta_key', '=', $thismeta_key] ])
            ->update([ 'meta_value'  => $this->meta_value]);

        return is_int( $affected_rows ) ? true : false;
    }

    // select meta
    private function select() : bool {

        $meta = $this->db
            ->table( 'user_meta' )
            ->where([[ 'user_id', '=', $this->user_id ], [ 'meta_key', '=', $this->meta_key ]])
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
    private function delete() : bool {

        $affected_rows = $this->db
            ->table('user_meta')
            ->where([ ['user_id', '=', $this->user_id], ['meta_key', '=', $this->meta_key] ])
            ->delete();

        // TODO: check deleting
        return $affected_rows > 0 ? true : false;
    }

    // insert/update meta *
    public function set( int $user_id, string $meta_key, string $meta_value ) : bool {}

    // select meta *
    public function get( int $user_id, string $meta_key ) : bool {}

    // delete meta *
    public function unset( int $user_id, string $meta_key ) : bool {}

}
