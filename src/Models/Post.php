<?php
namespace artabramov\Echidna\Models;
use \artabramov\Echidna\Utilities\Filter;

/**
 * @implements Sequenceable
 */
class Post extends \artabramov\Echidna\Models\Echidna implements \artabramov\Echidna\Interfaces\Sequenceable
{
    private $error;
    private $id;
    private $date;
    private $parent_id;
    private $user_id;
    private $hub_id;
    private $post_status;
    private $post_type;
    private $post_content;

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
        $this->parent_id = null;
        $this->user_id = null;
        $this->hub_id = null;
        $this->post_status = null;
        $this->post_type = null;
        $this->post_content = null;
    }

    /**
     * @param int $parent_id
     * @param int $user_id
     * @param int $hub_id
     * @param string $post_status
     * @param string $post_type
     * @param string $post_content
     * @param int $min_length
     * @param int $max_length
     * @return bool
     */
    public function create( int $parent_id, int $user_id, int $hub_id, string $post_status, string $post_type, string $post_content, int $min_length, int $max_length ) : bool {

        $this->clear();

        if( !Filter::is_empty( $user_id ) and !Filter::is_int( $parent_id )) {
            $this->error = 'parent_id is incorrect';

        } elseif( Filter::is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !Filter::is_int( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( Filter::is_empty( $hub_id )) {
            $this->error = 'hub_id is empty';

        } elseif( !Filter::is_int( $hub_id )) {
            $this->error = 'hub_id is incorrect';

        } elseif( Filter::is_empty( $post_status )) {
            $this->error = 'post_status is empty';

        } elseif( !Filter::is_key( $post_status, 20 )) {
            $this->error = 'post_status is incorrect';

        } elseif( Filter::is_empty( $post_type )) {
            $this->error = 'post_type is empty';

        } elseif( !Filter::is_key( $post_type, 20 )) {
            $this->error = 'post_type is incorrect';

        } elseif( Filter::is_empty( $post_content )) {
            $this->error = 'post_content is empty';

        } elseif( !Filter::is_string( $post_content, $min_length, $max_length )) {
            $this->error = 'post_content is incorrect';

        } else {
            $this->parent_id = $parent_id;
            $this->user_id = $user_id;
            $this->hub_id = $hub_id;
            $this->post_status = $post_status;
            $this->post_type = $post_type;
            $this->post_content = $post_content;

            $data = [
                'parent_id'    => $this->parent_id,
                'user_id'      => $this->user_id,
                'hub_id'       => $this->hub_id,
                'post_status'  => $this->post_status,
                'post_type'    => $this->post_type,
                'post_content' => $this->post_content,
            ];

            $this->id = $this->insert( 'posts', $data );

            if( empty( $this->id )) {
                $this->clear();
                $this->error = 'post insert error';
            }   
        }

        return empty( $this->error );
    }


    /**
     * 
     */
    public function getone( int $post_id ) {}

}
