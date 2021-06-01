<?php
namespace artabramov\Echidna;

class Mapper
{
    protected $error;
    protected $repository;

    public function __construct( $repository ) {
        $this->error = '';
        $this->repository = $repository;
    }

    public function __isset( $key ) {
        if( property_exists( $this, $key )) {
            return !empty( $this->$key );
        }
        return false;
    }

    public function __get( $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
    }

    /**
     * Make entity params array from doc string.
     * Entity doc format: @entity(table=users entity=user)
     * @return string
     */
    private function get_entity_params( \ReflectionClass $entity_class ) : array {
        $doc = $entity_class->getDocComment();
        return $this->parse_params( $doc, 'entity' );
    }

    /**
     * Make property params array from doc string.
     * Param doc format: @column(nullable=true unique=true regex=/^[a-z]{1,20}$/)
     * @return string
     */
    private function get_property_params( $entity, string $column ) : array {
        $class = new \ReflectionClass( $entity );
        $property = $class->getProperty( $column );
        $doc = $property->getDocComment();
        return $this->parse_params( $doc, 'column' );
    }

    /**
     * Make params array from string.
     * @return array
     */
    private function parse_params( string $doc, string $key ) : array {

        preg_match_all( '#@' . $key . '\((.*?)\)\n#s', $doc, $tmp );
        preg_match_all( '/\s*([^=]+)=(\S+)\s*/', !empty($tmp[1][0]) ? $tmp[1][0] : '', $tmp );
        return array_combine ( $tmp[1], $tmp[2] );
    }

    /**
     * Check data for correctness.
     * @return void
     */
    private function parse_data( $entity, array $data ) {

        $entity_class = new \ReflectionClass( $entity );

        foreach( $data as $key => $value ) {
            $property = $entity_class->getProperty( $key );
            $property->setAccessible( true );
            $property_params = $this->get_property_params( $entity, $key );

            if( $property_params[ 'nullable' ] != 'true' and empty( $value )) {
                $this->error = $key . ' is empty';
                break;

            } elseif( !empty( $value ) and !preg_match( $property_params[ 'regex' ], $value ) ) {
                $this->error = $key . ' is incorrect';
                break;

            } elseif( !empty( $value ) and  $property_params[ 'unique' ] == 'true' and $this->exists( $entity, [[ $key, '=', $value ]] ) ) {
                $this->error = $key . ' is occupied';
                break;
            }
        }
    }

    /**
     * Select entity from repository.
     * @return bool
     */
    public function select( $entity, array $data ) : bool {
        $this->error = '';
        $class = new \ReflectionClass( $entity );
        $params = $this->get_entity_params( $class );

        $query = $this->repository->select( ['*'], $params['table'], $data, ['LIMIT 1', 'OFFSET 0'] );
        $this->repository->execute( $query );
        $rows = $this->repository->rows();

        if( !empty( $rows )) {
            foreach( $rows[0] as $key=>$value ) {

                $property = $class->getProperty( $key );
                $property->setAccessible( true );
                $property->setValue( $entity, $rows[0]->$key );
            }

        } else {
            $this->error = $params['entity'] . ' not found';
        }

        return empty( $this->error );
    }

    /**
     * Insert entity in repository.
     * @return bool
     */
    public function insert( $entity, array $data ) : bool {

        $this->error = '';
        $entity_class = new \ReflectionClass( $entity );
        $entity_params = $this->get_entity_params( $entity_class );
        $this->parse_data( $entity, $data );
        
        if( empty( $this->error )) {
            $query = $this->repository->insert( $entity_params['table'], $data );

            if( $this->repository->execute( $query )) {
                $id = $this->repository->id();

                if( !empty( $id )) {
                    $data['id'] = $id;

                    foreach( $data as $key => $value ) {
                        $property = $entity_class->getProperty( $key );
                        $property->setAccessible( true );
                        $property->setValue( $entity, $value );
                    }

                } else {
                    $this->error = $entity_params['entity'] . ' insert error';
                }
            }
        }

        return empty( $this->error );
    }

    /**
     * Update entity in repository.
     * @return bool
     */
    public function update( $entity, array $data ) : bool {

        $this->error = '';
        $entity_class = new \ReflectionClass( $entity );
        $entity_params = $this->get_entity_params( $entity_class );
        $this->parse_data( $entity, $data );

        if( empty( $this->error )) {
            $query = $this->repository->update( $entity_params['table'], [['id', '=', $entity->id]], $data );

            if( $this->repository->execute( $query )) {

                foreach( $data as $key => $value ) {
                    $property = $entity_class->getProperty( $key );
                    $property->setAccessible( true );
                    $property->setValue( $entity, $value );
                }

            } else {
                $this->error = $entity_params['entity'] . ' update error';
            }
        }

        return empty( $this->error );
    }

    /**
     * Delete entiry from repository.
     * @return bool
     */
    public function delete( $entity ) : bool {
        $this->error = '';

        $class = new \ReflectionClass( $entity );
        $params = $this->get_entity_params( $class );
        $query = $this->repository->delete( $params['table'], [['id', '=', $entity->id]] );

        if( $this->repository->execute( $query )) {
            $properties = $class->getProperties();

            foreach( $properties as $property ) {
                $property->setAccessible( true );
                $property->setValue( $entity, null );
            }

        } else {
            $this->error = $params['entity'] . ' delete error';
        }

        return empty( $this->error );
    }

    /**
     * Check is entity exists in repository.
     * @return bool
     */
    public function exists( $entity, array $args ) : bool {

        $class = new \ReflectionClass( $entity );
        $params = $this->get_entity_params( $class );

        $query = $this->repository->select( ['id'], $params['table'], $args, ['LIMIT 1', 'OFFSET 0'] );
        $this->repository->execute( $query );
        $rows = $this->repository->rows();

        return !empty( $rows[0]->id );
    }

}
