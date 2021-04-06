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
    public function create( int $hub_id, int $user_id, string $user_role ) : bool {

        $this->clear();

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
                $this->clear();
                $this->error = 'role insert error';
            }   
        }

        return empty( $this->error );
    }

    /**
     * @param int $user_id
     * @param int $hub_id
     * @param string $user_role
     * @return bool
     */
    public function rerole( int $hub_id, int $user_id, string $user_role ) : bool {

        $this->clear();

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

        } elseif( $this->count( 'user_roles', [['user_id', '=', $user_id], ['hub_id', '=', $hub_id]] ) == 0 ) {
            $this->error = 'role not found';

        } else {
            $this->user_id = $user_id;
            $this->hub_id = $hub_id;
            $this->user_role = $user_role;

            $args = [[ 'user_id', '=', $this->user_id ], [ 'hub_id', '=', $this->hub_id ]];
            $data = [ 'user_role' => $this->user_role ];

            if( !$this->update( 'user_roles', $args, $data )) {
                $this->clear();
                $this->error = 'role update error';
            }
        }

        return empty( $this->error );
    }
    
    /**
     * @param int $user_id
     * @param int $hub_id
     * @return bool
     */
    public function remove( int $hub_id, int $user_id ) : bool {

        $this->clear();

        if( Filter::is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !Filter::is_int( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( Filter::is_empty( $hub_id )) {
            $this->error = 'hub_id is empty';

        } elseif( !Filter::is_int( $hub_id )) {
            $this->error = 'hub_id is incorrect';

        } elseif( $this->count( 'user_roles', [['user_id', '=', $user_id], ['hub_id', '=', $hub_id]] ) == 0 ) {
            $this->error = 'role not found';

        } else {
            $this->user_id = $user_id;
            $this->hub_id = $hub_id;

            $args = [[ 'user_id', '=', $this->user_id ], [ 'hub_id', '=', $this->hub_id ]];

            if( !$this->delete( 'user_roles', $args )) {
                $this->clear();
                $this->error = 'role delete error';
            }
        }

        return empty( $this->error );
    }

    /**
     * @param int $user_id
     * @param int $hub_id
     * @return bool
     */
    public function fetch( int $hub_id, int $user_id ) : bool {

        $this->clear();

        if( Filter::is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !Filter::is_int( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( Filter::is_empty( $hub_id )) {
            $this->error = 'hub_id is empty';

        } elseif( !Filter::is_int( $hub_id )) {
            $this->error = 'hub_id is incorrect';

        } elseif( $this->count( 'user_roles', [['user_id', '=', $user_id], ['hub_id', '=', $hub_id]] ) == 0 ) {
            $this->error = 'role not found';

        } else {
            $this->user_id = $user_id;
            $this->hub_id = $hub_id;

            $args = [[ 'user_id', '=', $this->user_id ], [ 'hub_id', '=', $this->hub_id ]];
            $rows = $this->select( '*', 'user_roles', $args, 1, 0 );

            if( empty( $rows[0] )) {
                $this->clear();
                $this->error = 'role select error';

            } else {
                $this->id        = $rows[0]['id'];
                $this->date      = $rows[0]['date'];
                $this->hub_id    = $rows[0]['hub_id'];
                $this->user_id   = $rows[0]['user_id'];
                $this->user_role = $rows[0]['user_role'];
            }
        }

        return empty( $this->error );
    }

    /**
     * This is a part of the Sequence interface. Get the element by id.
     * @param mixed $role_id
     * @return bool
     */
    public function getone( int $role_id ) : bool {

        $this->clear();

        if( Filter::is_empty( $role_id )) {
            $this->error = 'role_id is empty';

        } elseif( !Filter::is_int( $role_id )) {
            $this->error = 'role_id is incorrect';

        } elseif( $this->count( 'user_roles', [['id', '=', $role_id]] ) == 0 ) {
            $this->error = 'role not found';

        } else {
            $this->id = $role_id;

            $args = [[ 'id', '=', $this->id ]];
            $rows = $this->select( '*', 'user_roles', $args, 1, 0 );

            if( empty( $rows[0] )) {
                $this->clear();
                $this->error = 'role select error';

            } else {
                $this->id        = $rows[0]['id'];
                $this->date      = $rows[0]['date'];
                $this->hub_id    = $rows[0]['hub_id'];
                $this->user_id   = $rows[0]['user_id'];
                $this->user_role = $rows[0]['user_role'];
            }
        }

        return empty( $this->error );
    }

    
}
