<?php

namespace artabramov\Echidna\Echidna;

/**
 * All posts should be inside one of the hubs. 
 * The Hub class is responsible for working with hubs.
 */
class Hub extends \artabramov\Echidna\Echidna
{
    /**
     * Insert a new hub.
     * @param int $user_id
     * @param string $hub status
     * @param string $hub_name
     * @return bool
     */
    public function set( int $user_id, string $hub_status, string $hub_name ) : bool {

        if( $this->is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !$this->is_id( $user_id )) {
            $this->error = 'user_id is incorrect';


        } elseif( $this->is_empty( $hub_status )) {
            $this->error = 'hub_status is empty';

        } elseif( !$this->is_key( $hub_status )) {
            $this->error = 'hub_status is incorrect';

        } elseif( $this->is_empty( $hub_name )) {
            $this->error = 'hub_name is empty';

        } elseif( !$this->is_value( $hub_name )) {
            $this->error = 'hub_name is incorrect';

        } elseif( $this->is_exists( 'hubs', [['user_id', '=', $user_id], ['hub_name', '=', $hub_name]] )) {
            $this->error = 'hub_name is occupied';

        } else {

            $data = [
                'user_id'    => $user_id,
                'hub_status' => $hub_status,
                'hub_name'   => $hub_name
            ];

            if( !$this->insert( 'hubs', $data )) {
                $this->error = 'hub insert error';
            }
        }

        return empty( $this->error );
    }

    public function put() : bool {}

    public function unset() : bool {}

    public function get() : bool {}

    public function some() : bool {}

}
