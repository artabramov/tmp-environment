<?php
namespace artabramov\Echidna\Models;
use \artabramov\Echidna\Utilities\Filter;

/**
 * @implements Sequenceable
 */
class Hub extends \artabramov\Echidna\Models\Echidna implements \artabramov\Echidna\Interfaces\Sequenceable
{
    private $error;
    private $id;
    private $date;
    private $user_id;
    private $hub_status;
    private $hub_name;

    public function __get( string $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
        return false;
    }

    public function __isset( string $key ) {
        if( property_exists( $this, $key )) {
            return !empty( $this->$key );
        }
        return false;
    }

    public function clear() {
        $this->e = null;
        $this->error = null;
        $this->id = null;
        $this->date = null;
        $this->user_id = null;
        $this->hub_status = null;
        $this->hub_name = null;
    }


    /**
     * @param int $user_id
     * @param string $hub_status
     * @param string $hub_name
     * @return bool
     */
    public function set( int $user_id, string $hub_status, string $hub_name, int $min_length, int $max_length ) : bool {

        if( Filter::is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !Filter::is_int( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( Filter::is_empty( $hub_status )) {
            $this->error = 'hub_status is empty';

        } elseif( !Filter::is_key( $hub_status, 20 )) {
            $this->error = 'hub_status is incorrect';

        } elseif( Filter::is_empty( $hub_name )) {
            $this->error = 'hub_name is empty';

        } elseif( !Filter::is_string( $hub_name, $min_length, $max_length )) {
            $this->error = 'hub_name is incorrect';

        } else {
            $this->clear();
            $this->user_id = $user_id;
            $this->hub_status = $hub_status;
            $this->hub_name = $hub_name;

            $data = [
                'user_id'    => $this->user_id,
                'hub_status' => $this->hub_status,
                'hub_name'   => $this->hub_name
            ];

            $this->id = $this->insert( 'hubs', $data );

            if( empty( $this->id )) {
                $this->error = 'hub insert error';
            }   
        }

        return empty( $this->error );
    }

    /**
     * @param int $hub_id
     * @param mixed $hub_name
     * @return bool
     */
    public function put( int $hub_id, mixed $hub_name, int $min_length, int $max_length ) : bool {

        if( Filter::is_empty( $hub_id )) {
            $this->error = 'hub_id is empty';

        } elseif( !Filter::is_int( $hub_id )) {
            $this->error = 'hub_id is incorrect';

        } elseif( Filter::is_empty( $hub_name )) {
            $this->error = 'hub_name is empty';

        } elseif( !Filter::is_string( $hub_name, $min_length, $max_length )) {
            $this->error = 'hub_name is incorrect';

        } elseif( $this->count( 'hubs', [['id', '=', $hub_id], ['hub_status', '<>', 'trash']] ) == 0 ) {
            $this->error = 'hub not found';

        } else {
            $this->clear();
            $this->id = $hub_id;
            $this->hub_name = $hub_name;

            $args = [[ 'id', '=', $this->id ]];
            $data = [ 'hub_name' => $this->hub_name ];

            if( !$this->update( 'hubs', $args, $data )) {
                $this->error = 'hub update error';
            }
        }

        return empty( $this->error );
    }

    /**
     * Update status from public to trash.
     * @param int $hub_id
     * @return bool
     */
    public function trash( int $hub_id ) : bool {

        if( Filter::is_empty( $hub_id )) {
            $this->error = 'hub_id is empty';

        } elseif( !Filter::is_int( $hub_id )) {
            $this->error = 'hub_id is incorrect';

        } elseif( $this->count( 'hubs', [['id', '=', $hub_id], ['hub_status', '=', 'public']] ) == 0 ) {
            $this->error = 'hub not found';

        } else {
            $this->clear();
            $this->id = $hub_id;

            $args = [ ['id', '=', $this->id] ];
            $data = [ 'hub_status' => 'trash' ];

            if( !$this->update( 'hubs', $args, $data )) {
                $this->error = 'hub update error';
            }
        }

        return empty( $this->error );
    }

    /**
     * Update status from trash to public.
     * @param int $hub_id
     * @return bool
     */
    public function recover( int $hub_id ) : bool {

        if( Filter::is_empty( $hub_id )) {
            $this->error = 'hub_id is empty';

        } elseif( !Filter::is_int( $hub_id )) {
            $this->error = 'hub_id is incorrect';

        } elseif( $this->count( 'hubs', [['id', '=', $hub_id], ['hub_status', '=', 'trash']] ) == 0 ) {
            $this->error = 'hub not found';

        } else {
            $this->clear();
            $this->id = $hub_id;

            $args = [ ['id', '=', $this->id] ];
            $data = [ 'hub_status' => 'public' ];

            if( !$this->update( 'hubs', $args, $data )) {
                $this->error = 'hub update error';
            }
        }

        return empty( $this->error );
    }

    /**
     * Permanent remove.
     * @param int $hub_id
     * @return bool
     */
    public function remove( int $hub_id ) : bool {

        if( Filter::is_empty( $hub_id )) {
            $this->error = 'hub_id is empty';

        } elseif( !Filter::is_int( $hub_id )) {
            $this->error = 'hub_id is incorrect';

        } elseif( $this->count( 'hubs', [['id', '=', $hub_id], ['hub_status', '=', 'trash']] ) == 0 ) {
            $this->error = 'hub not found';

        } else {
            $this->clear();
            $this->id = $hub_id;

            $args = [ ['id', '=', $this->id] ];

            if( !$this->delete( 'hubs', $args, $data )) {
                $this->error = 'hub delete error';
            }
        }

        return empty( $this->error );
    }

    /**
     * This is a part of the Sequence interface. Get the element by id.
     * @param mixed $hub_id
     * @return bool
     */
    public function getone( int $hub_id ) : bool {

        if( Filter::is_empty( $hub_id )) {
            $this->error = 'hub_id is empty';

        } elseif( !Filter::is_int( $hub_id )) {
            $this->error = 'hub_id is incorrect';

        } elseif( $this->count( 'hubs', [['id', '=', $hub_id]] ) == 0 ) {
            $this->error = 'hub not found';

        } else {
            $this->clear();
            $this->id = $hub_id;

            $args = [ ['id', '=', $this->id] ];

            if( !$this->select( 'hubs', $args )) {
                $this->error = 'hub select error';
            }
        }

        return empty( $this->error );
    }

}
