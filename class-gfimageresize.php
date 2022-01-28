<?php

GFForms::include_addon_framework();
 
class GFImageResize extends GFAddOn {
 
    protected $_version = GF_IMAGE_RESIZE_VERSION;
    protected $_min_gravityforms_version = '1.9';
    protected $_slug = 'imageresize';
    protected $_path = 'gravityforms-image-resize/imageresize.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms Image Resize';
    protected $_short_title = 'Image Resize';
 
    private static $_instance = null;
 
    public static function get_instance() {
        if ( self::$_instance == null ) {
            self::$_instance = new GFImageResize();
        }
        return self::$_instance;
    }
 
	// Initialize add-on
    public function init() {
		// Initialize add-on framework
        parent::init();
		// Add settings
        add_filter( 'gform_form_settings_fields', array( $this, 'image_resize_settings' ), 10, 2 );
		// Set initial setting values
		add_filter( 'gform_form_settings_initial_values', array( $this, 'setting_initial_values' ), 10, 2 );
		// Resize images after form submission
		add_action('gform_after_submission', array( $this, 'gform_resize_images' ), 10, 2);
    }

	// Add image resize settings to form settings
	function image_resize_settings( $fields, $form ) {

		$sizes = wp_get_registered_image_subsizes();
		$size_options = array();

		foreach($sizes as $label => $size) {
			$size_options[] = array(
				'label' => esc_html__( $label, 'imageresize' ),
				'value' => $label
			);
		}

		$fields['vi_image_resize'] = array(
			'title'       => 'Image Resize',
			'fields'      => array(
				array(
					'label'   => esc_html__( 'Automatically Resize Images', 'imageresize' ),
					'type'    => 'checkbox',
					'name'    => 'image_resize_enabled',
					'tooltip' => esc_html__( 'Enable automatic resize on form image upload fields', 'imageresize' ),
					'choices' => array(
						array(
							'label' => esc_html__( 'Enable Image Resize', 'imageresize' ),
							'name'  => 'enable_resize',
						),
					),
				),
				array(
					'label'   => esc_html__( 'Image Size', 'imageresize' ),
					'type'    => 'select',
					'name'    => 'image_max_size',
					'choices' => $size_options,
				),
			)
		);
		
		return $fields;

    }

	// Set default max size to large
	function setting_initial_values($initial_values, $form) {
		$initial_values['image_max_size'] = 'large';
    	return $initial_values;
	}

	function gform_resize_images($entry, $form) {

		// Check if image resize is enabled
		if($form['enable_resize']) {

			// Loop through form fields
			foreach($form['fields'] as $field) {

				// Get form file upload fields
				if ($field['type'] == 'fileupload') {

					// Add support for multiple file uploads
					$files = str_replace(array('"', '[', ']'), '', stripslashes($entry[$field['id']]) );
					$files = explode(',', $files);

					// Loop through uploaded files
					foreach($files as $url) {

						// Parse URL and get image
						$parsed_url = parse_url($url);
						$path = $_SERVER['DOCUMENT_ROOT'] . $parsed_url['path'];
						$image = wp_get_image_editor($path);

						if ( ! is_wp_error( $image ) ) {

							// Get image size from form settings
							$form_image_size = $form['image_max_size'];
							$wp_sizes = wp_get_registered_image_subsizes();
							$width = $wp_sizes[$form_image_size]['width'];
							$length = $wp_sizes[$form_image_size]['length'];

							// Resize and save image
							$result = $image->resize( $width, $length, false );
							$result = $image->save($path);

						}
					}
				}
			}
		}
	}
 
}
