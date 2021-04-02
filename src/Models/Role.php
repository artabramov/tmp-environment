<?php
namespace artabramov\Echidna\Models;
use \artabramov\Echidna\Utilities\Filter;

/**
 * @implements Sequenceable
 */
class Role extends \artabramov\Echidna\Models\Echidna implements \artabramov\Echidna\Interfaces\Sequenceable
{
    private $error;
    private $id;
    private $date;
    private $hub_id;
    private $user_id;
    private $user_role;

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
        $this->hub_id = null;
        $this->user_id = null;
        $this->user_role = null;
    }


    /**
     * @param int $user_id
     * @param int $hub_id
     * @param string $user_role
     * @return bool
     */
    public function set( int $user_id, int $hub_id, string $user_role ) : bool {

        if( Filter::is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !Filter::is_int( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( Filter::is_empty( $hub_id )) {
            $this->error = 'hub_id is empty';

        } elseif( !Filter::is_int( $hub_id )) {
            $this->error = 'hub_id is incorrect';

        } elseif( Filter::is_empty( $user_role )) {
            $this->error = 'user_role is empty';

        } elseif( !Filter::is_key( $user_role, 20 )) {
            $this->error = 'user_role is incorrect';

        } elseif( $this->count( 'user_roles', [['user_id', '=', $user_id], ['hub_id', '=', $hub_id]] ) > 0 ) {
            $this->error = 'role is occupied';

        } else {
            $this->clear();
            $this->hub_id = $hub_id;
            $this->user_id = $user_id;
            $this->user_role = $user_role;

            $data = [
                'hub_id' => $this->hub_id,
                'user_id' => $this->user_id,
                'user_role' => $this->user_role
            ];

            $this->id = $this->insert( 'user_roles', $data );

            if( empty( $this->id )) {
                $this->error = 'role insert error';
            }   
        }

        return empty( $this->error );
    }


    // TODO










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
     * @param mixed $attribute_id
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
