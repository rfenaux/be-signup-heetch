<?php

class FrmAPIFieldsController extends WP_REST_Controller {

	protected $rest_base = 'fields';
	protected $parent_base = 'forms';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		$posts_args = $this->get_item_args();

		register_rest_route( FrmAPIAppController::$v2_base, '/' . $this->parent_base . '/(?P<parent_id>[\w-]+)/' . $this->rest_base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'            => $posts_args,
			),
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_items' ),
				'permission_callback' => array( $this, 'edit_item_permissions_check' ),
				'args'            => $posts_args,
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( FrmAPIAppController::$v2_base, '/' . $this->parent_base . '/(?P<parent_id>[\w-]+)/' . $this->rest_base . '/(?P<id>[\w-]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'            => $posts_args,
			),
			array(
				'methods'         => WP_REST_Server::DELETABLE,
				'callback'        => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'            => $posts_args,
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	protected function get_item_args() {
		$posts_args = array(
		);

		return $posts_args;
	}

	public function get_items( $request ) {
		$prepared_args = $this->prepare_items_query( $request );

		if ( is_numeric( $request['parent_id'] ) ) {
			$form_id = $request['parent_id'];
		} else {
			$form_id = FrmForm::getIdByKey( $request['parent_id'] );
		}
		$fields = FrmField::get_all_for_form( $form_id, '', 'include' );
		
		$data = array();
		foreach ( $fields as $obj ) {
			$status = $this->prepare_item_for_response( $obj, $request );
			$data[ $obj->field_key ] = $this->prepare_response_for_collection( $status );
		}
		return $data;
	}

	public function create_items( $request ) {
		$form_id = $request['parent_id'];
		
		foreach ( $request['fields'] as $field ) {
			$f = apply_filters( 'frm_before_field_created', FrmFieldsHelper::setup_new_vars( $field['type'], $form_id ) );
			foreach ( $field as $opt => $val ) {
				$f[ $opt ] = $val;
			}
		    
			$f['form_id'] = $form_id;
		    
			FrmField::create( $f );
		}

		$response = $this->get_items( array(
			'parent_id' => $form_id,
			'context'   => 'edit',
		) );
		$response = rest_ensure_response( $response );

		return $response;
	}

	public function get_item( $request ) {
		$obj = FrmField::getOne( $request['id'] );
		$data = $this->prepare_item_for_response( $obj, $request );
		$response = rest_ensure_response( $data );
		return $response;
	}

	public function delete_item( $request ) {
		$id = sanitize_text_field( $request['id'] );
		$form_id = sanitize_text_field( $request['parent_id'] );
		$field = FrmField::getOne( $id );

		$get_request = new WP_REST_Request( 'GET', rest_url( '/' . FrmAPIAppController::$v2_base . '/' . $this->parent_base . '/' . $form_id . '/' . $this->rest_base . '/' . $id ) );
		$get_request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $field, $get_request );

		$results = FrmField::destroy( $id );

		if ( ! $results ) {
			return new WP_Error( 'rest_field_invalid_id', __( 'Invalid field ID.' ), array( 'status' => 404 ) );
		}
	    
		return $response;
	}

	protected function prepare_items_query( $request ) {
		$form_id = $request['parent_id'];
		if ( ! is_numeric( $form_id ) ) {
			$form_id = FrmForm::getIdByKey( $form_id );
		}

		$prepared_args = array(
			'form_id' => $form_id,
		);

		if ( ! empty( $request['search'] ) ) {
			$prepared_args[] = array(
				'name like' => $request['search'],
				'description like' => $request['search'],
				'or' => 1,
			);
		}

		return $prepared_args;
	}

	public function prepare_item_for_response( $field, $request ) {

		// Base fields for every post
		$data = array(
			'id'            => $field->id,
			'field_key'     => $field->field_key,
			'name'          => $field->name,
			'description'   => $field->description,
			'type'          => $field->type,
			'default_value' => $field->default_value,
			'options'       => $field->options,
			'field_order'   => $field->field_order,
			'required'      => $field->required,
			'field_options' => $field->field_options,
			'form_id'       => $field->form_id,
			'created_at'    => $field->created_at,
		);

		$schema = $this->get_item_schema();

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data = $this->filter_response_by_context( $data, $context );

		$data = $this->add_additional_fields_to_object( $data, $request );

		// Wrap the data in a response object
		$data = rest_ensure_response( $data );

		return apply_filters( 'rest_prepare_frm_' . $this->rest_base, $data, $field, $request );
	}

	public function get_item_schema() {

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->rest_base,
			'type'       => 'object',
			'properties' => array(
				'id'              => array(
					'description' => 'Unique identifier for the object.',
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'field_key'       => array(
					'description' => 'An alphanumeric identifier for the object unique to its type.',
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
				'name'            => array(
					'description' => 'The title of this object.',
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'description'     => array(
					'description' => 'The description of this object.',
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'type'            => array(
					'description' => 'The field type of this object.',
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'default_value'   => array(
					'description' => 'The default value for this field.',
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'options'         => array(
					'description' => 'An array of options for the object.',
					'type'        => 'array',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'field_options'   => array(
					'description' => 'An array of options to show in the field.',
					'type'        => 'array',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'field_order'     => array(
					'description' => 'The order of the fields.',
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'required'        => array(
					'description' => 'If this object is a required field.',
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'form_id'         => array(
					'description' => 'The id of the form this entry belongs to.',
					'type'        => 'integer',
					'context'     => array( 'edit' ),
				),
				'created_at'      => array(
					'description' => 'The date the object was created.',
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
			),
		);

		return $schema;
	}

	public function get_item_permissions_check( $request ) {

		if ( 'edit' === $request['context'] && ! current_user_can( 'frm_edit_forms' ) && ! current_user_can( 'administrator' ) ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit forms' ), array( 'status' => 403 ) );
		}

		return true;
	}

	public function edit_item_permissions_check( $request ) {

		if ( ! current_user_can( 'frm_edit_forms' ) && ! current_user_can( 'administrator' ) ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to create or edit forms' ), array( 'status' => 403 ) );
		}

		return true;
	}

	public function delete_item_permissions_check( $request ) {

		if ( ! current_user_can('frm_delete_forms') && ! current_user_can('administrator') ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed delete forms' ), array( 'status' => 403 ) );
		}

		return true;
	}
}
