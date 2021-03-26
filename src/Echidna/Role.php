<?php

namespace artabramov\Echidna\Echidna;

/**
 * All users have roles according to which 
 * they can add posts to various hubs.
 */
class Role extends \artabramov\Echidna\Echidna
{

    /**
     * Set the user role.
     * @param int $user_id
     * @param int $hub_id
     * @param string $user_role
     * @return bool
     */
    public function set( int $user_id, int $hub_id, string $user_role ) : bool {
        
        if( $this->is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !$this->is_id( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( $this->is_empty( $hub_id )) {
            $this->error = 'hub_id is empty';

        } elseif( !$this->is_id( $hub_id )) {
            $this->error = 'hub_id is incorrect';

        } elseif( $this->is_empty( $user_role )) {
            $this->error = 'user_role is empty';

        } elseif( !$this->is_key( $user_role )) {
            $this->error = 'user_role is incorrect';

        } elseif( $this->is_exists( 'user_roles', [['user_id', '=', $user_id], ['hub_id', '=', $hub_id]] )) {
            $this->error = 'role is exists';

        } else {

            $data = [
                'hub_id'    => $hub_id,
                'user_id'   => $user_id,
                'user_role' => $user_role
            ];

            if( !$this->insert( 'user_roles', $data )) {
                $this->error = 'role insert error';
            }
        }

        return empty( $this->error );
    }

    /**
     * Change the user role.
     * @param int $user_id
     * @param int $hub_id
     * @param string $user_role
     * @return bool
     */
    public function reset( int $user_id, int $hub_id, string $user_role ) : bool {}

    /**
     * Select role of the user in the hub.
     * @param int $user_id
     * @param int $hub_id
     * @return bool
     */
    public function get_one( int $user_id, int $hub_id ) : bool {}

    /**
     * Delete the role.
     * @param int $user_id
     * @param int $hub_id
     * @return bool
     */
    public function remove( int $user_id, int $hub_id ) : bool {}

}
