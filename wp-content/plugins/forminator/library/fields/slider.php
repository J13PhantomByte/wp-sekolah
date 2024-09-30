<?php
/**
 * Slider field.
 *
 * @package Forminator
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Slider
 */
class Forminator_Slider extends Forminator_Field {
	/**
	 * Module ID
	 *
	 * @var int
	 */
	private static int $module_id;

	/**
	 * Field constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->name = esc_html__( 'Slider', 'forminator' );
		$this->slug = 'slider';
		$this->type = 'slider';
		$this->icon = 'sui-icon-settings-slider-control';

		$this->category = 'standard';
		$this->position = 27;

		add_filter( 'forminator_is_subfield_enabled', array( __CLASS__, 'is_subfield_enabled' ), 10, 3 );
	}

	/**
	 * Checks whether the subfield is enabled
	 *
	 * @param bool   $is_enabled Is subfield enabled.
	 * @param string $subfield_name Subfield name.
	 * @param object $obj Forminator_Field object.
	 * @return bool
	 */
	public static function is_subfield_enabled( $is_enabled, $subfield_name, $obj ) {
		if ( ! empty( $obj->raw['type'] ) && 'slider' === $obj->raw['type'] ) {
			$is_enabled = ! empty( $obj->raw['slider_type'] ) && 'range' === $obj->raw['slider_type'];
		}

		return $is_enabled;
	}

	/**
	 * Field defaults
	 *
	 * @return array
	 */
	public function defaults(): array {
		return array(
			'calculations' => 'true',
			'slider_type'  => 'single',
			'field_label'  => esc_html__( 'Slider', 'forminator' ),
		);
	}

	/**
	 * Field front-end markup
	 *
	 * @param array                  $field Field.
	 * @param Forminator_CForm_Front $views_obj Forminator_CForm_Front object.
	 * @param array|null             $draft_value Draft value(s).
	 *
	 * @return mixed
	 */
	public function markup( $field, $views_obj, $draft_value = array() ) {
		self::$module_id = $views_obj->get_module_id();

		$name        = self::get_property( 'element_id', $field );
		$required    = self::get_property( 'required', $field );
		$label       = self::get_property( 'field_label', $field );
		$description = self::get_property( 'description', $field );
		$id          = self::get_field_id( $name );
		$slider      = self::create_slider( $field, $draft_value );
		$full_slider = self::add_slider_value( $slider, $field );

		$html  = '<div class="forminator-field">';
		$html .= self::get_field_label( $label, $id, $required );
		$html .= $full_slider;
		$html .= self::get_description( $description, $name );
		$html .= '</div>';

		/**
		 * Filter slider markup
		 *
		 * @param string $html HTML markup.
		 * @param array  $field Field settings.
		 * @param int    $module_id Module ID.
		 */
		return apply_filters( 'forminator_field_slider_markup', $html, $field, self::$module_id );
	}

	/**
	 * Add slider value HTML to slider
	 *
	 * @param string $slider Basic slider HTML.
	 * @param array  $field Field settings.
	 * @return string
	 */
	private static function add_slider_value( string $slider, array $field ): string {
		$name        = self::get_property( 'element_id', $field );
		$value_block = self::get_value_block( $field );
		$width       = self::get_property( 'slider_width', $field, 'full' );
		$limit_block = self::get_limit_block( $field );

		$html = '<div class="forminator-slider forminator-slider-' . esc_attr( $width ) . '">' .
			'<input type="text" id="' . esc_attr( self::get_field_id( $name ) ) . '" class="forminator-hidden-input" style="display:none;"/>' .
			$slider . $limit_block . $value_block .
		'</div>';

		return $html;
	}

	/**
	 * Get slider value template
	 *
	 * @param array $field Field settings.
	 */
	private static function get_value_template( array $field ): string {
		$prefix         = self::get_property( 'prefix', $field );
		$suffix         = self::get_property( 'suffix', $field );
		$value_template = $prefix . '{slider-value}' . $suffix;

		/**
		 * Filter slider value template
		 *
		 * @param string $value_template Value template.
		 * @param array  $field Field settings.
		 * @param int    $module_id Module ID.
		 */
		return apply_filters( 'forminator_slider_value_template', $value_template, $field, self::$module_id );
	}

	/**
	 * Get value block HTML
	 *
	 * @param array $field Field settings.
	 * @return string
	 */
	private static function get_value_block( array $field ): string {
		$value_template = self::get_value_template( $field );
		$position       = self::get_property( 'value_position', $field );
		$position       = 'top' !== $position ? 'bottom' : 'top';
		$name           = self::get_property( 'element_id', $field );
		$is_range       = self::is_range_slider( $field );
		$attrs          = ' type="hidden" value=""';

		$hidden_behavior = self::get_property( 'hidden_behavior', $field );
		if ( 'zero' === $hidden_behavior ) {
			$attrs .= ' data-hidden-behavior="' . esc_attr( $hidden_behavior ) . '"';
		}

		$html  = '<div class="forminator-slider-amount forminator-slider-amount-' . esc_attr( $position ) . '"'
			. ' data-value-template="' . esc_attr( $value_template ) . '">';
		$html .= '<span class="forminator-slider-value-min"></span>';
		$html .= '<input' . $attrs .
			' id="' . esc_attr( self::get_field_id( $name . '-input' . ( $is_range ? '-min' : '' ) ) ) . '"' .
			' name="' . esc_attr( $name ) . ( $is_range ? '-min' : '' ) . '"' .
			' class="forminator-slider-hidden-min" />';
		if ( $is_range ) {
			$html .= '<span class="forminator-slider-separator">-</span>';
			$html .= '<span class="forminator-slider-value-max"></span>';
			$html .= '<input' . $attrs .
				' id="' . esc_attr( self::get_field_id( $name . '-input-max' ) ) . '"' .
				' name="' . esc_attr( $name ) . '-max"' .
				' class="forminator-slider-hidden-max" />';
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get limit block HTML
	 *
	 * @param array $field Field settings.
	 * @return string
	 */
	private static function get_limit_block( array $field ): string {
		$min  = self::get_min_limit( $field );
		$max  = self::get_max_limit( $field );
		$show = self::get_property( 'slider_limits', $field );

		if ( 'hide' === $show ) {
			return '';
		}

		$html  = '<div class="forminator-slider-limit">';
		$html .= '<span class="forminator-slider-limit-min">' . esc_html( $min ) . '</span>';
		$html .= '<span class="forminator-slider-limit-max">' . esc_html( $max ) . '</span>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Create slider HTML
	 *
	 * @param array      $field Field settings.
	 * @param array|null $draft_value Draft value(s).
	 * @return string
	 */
	private static function create_slider( array $field, $draft_value ): string {
		$name     = self::get_property( 'element_id', $field );
		$required = self::get_property( 'required', $field );
		$min      = self::get_min_limit( $field );
		$max      = self::get_max_limit( $field );
		$step     = self::get_property( 'slider_step', $field, 1 );
		$value    = self::get_property( 'slider_default', $field, $min );
		$is_range = self::is_range_slider( $field );

		if ( isset( $draft_value['value'] ) && is_numeric( $draft_value['value'] ) ) {
			$value = $draft_value['value'];
		}

		$attr = array(
			'class'         => 'forminator-slide',
			'aria-required' => $required ? 'true' : 'false',
			'data-is-range' => $is_range,
			'data-min'      => $min,
			'data-max'      => $max,
			'data-step'     => $step,
		);

		if ( self::get_property( 'description', $field ) ) {
			$attr['aria-describedby'] = $name . '-description';
		}

		if ( $is_range ) {
			$value_2 = self::get_property( 'slider_default_2', $field, $max );

			if ( isset( $draft_value['value']['min'] ) && is_numeric( $draft_value['value']['min'] ) ) {
				$value = $draft_value['value']['min'];
			}
			if ( isset( $draft_value['value']['max'] ) && is_numeric( $draft_value['value']['max'] ) ) {
				$value_2 = $draft_value['value']['max'];
			}

			$attr['data-value-max'] = self::get_post_data( $name . '-max', $value_2 );
		}

		// Override value by the posted value.
		$attr['data-value'] = self::get_post_data( $name, $value );

		$markup = self::implode_attr( $attr );
		$slider = sprintf( '<div %s></div>', $markup );

		/**
		 * Filter slider HTML
		 *
		 * @param string $slider HTML markup.
		 * @param array  $field Field settings.
		 * @param array  $attr Slider attributes.
		 * @param int    $module_id Module ID.
		 */
		return apply_filters( 'forminator_field_create_slider', $slider, $field, $attr, self::$module_id );
	}

	/**
	 * Is range slider?
	 *
	 * @param array $field Field settings.
	 * @return bool
	 */
	private static function is_range_slider( array $field ): bool {
		$slider_type = self::get_property( 'slider_type', $field );
		return 'range' === $slider_type;
	}

	/**
	 * Get slider max limit
	 *
	 * @param array $field Field settings.
	 * @return int
	 */
	private static function get_max_limit( array $field ): int {
		$slider_max = self::get_property( 'slider_max', $field );
		if ( ! is_numeric( $slider_max ) ) {
			$slider_max = 10;
		}
		return intval( $slider_max );
	}

	/**
	 * Get slider min limit
	 *
	 * @param array $field Field settings.
	 * @return int
	 */
	private static function get_min_limit( array $field ): int {
		return self::get_property( 'slider_min', $field, 1, 'num' );
	}

	/**
	 * Field back-end validation
	 *
	 * @param array        $field Field settings.
	 * @param array|string $data Data to validate.
	 */
	public function validate( $field, $data ) {
		$id = self::get_property( 'element_id', $field );

		$is_empty = ! isset( $data ) || '' === $data;
		$sub_id   = $id;
		if ( self::is_range_slider( $field ) ) {
			$sub_id .= '-max';
			if ( ! isset( $data['min'] ) || '' === $data['min'] ) {
				$is_empty = true;
			} elseif ( ! isset( $data['max'] ) || '' === $data['max'] ) {
				$is_empty = true;
			}
		}

		if ( ! $is_empty ) {
			$max = self::get_max_limit( $field );
			$min = self::get_min_limit( $field );
			if ( self::is_range_slider( $field ) && ( (
					$min > $data['min'] || $max < $data['min'] || $min > $data['max'] || $max < $data['max'] || $data['min'] > $data['max']
				) || ( ! is_array( $data ) && ( ( $min > $data ) || ( $max < $data ) ) ) ) ) {
				$validation_message = /* translators: 1: Minimum value, 2: Maximum value */ sprintf( esc_html__( 'The slider should be less than %1$d and greater than %2$d.', 'forminator' ), $min, $max );

				$this->validation_message[ $sub_id ] = sprintf(
					apply_filters(
						'forminator_field_slider_max_min_validation_message',
						$validation_message,
						$id,
						$field,
						$data
					),
					$max,
					$min
				);
			}
		}

		if ( $this->is_required( $field ) && $is_empty ) {
			$require_message             = self::get_property( 'required_message', $field );
			$required_validation_message = ! empty( $require_message ) ? $require_message : esc_html__( 'This field is required.', 'forminator' );

			$this->validation_message[ $sub_id ] = apply_filters(
				'forminator_field_slider_required_field_validation_message',
				$required_validation_message,
				$id,
				$field,
				$data,
				$this
			);
		}
	}

	/**
	 * Internal calculable value
	 *
	 * @param array|string $submitted_field The field value.
	 * @param array        $field_settings The field settings.
	 *
	 * @return float
	 */
	private static function calculable_value( $submitted_field, $field_settings ) {
		$enabled = self::get_property( 'calculations', $field_settings, false, 'bool' );
		if ( ! $enabled ) {
			return self::FIELD_NOT_CALCULABLE;
		}

		return intval( $submitted_field );
	}

	/**
	 * Get calculable
	 *
	 * @param array $submitted_field_data Original field value.
	 * @param array $field_settings Field settings.
	 * @return int|string
	 */
	public static function get_calculable_value( $submitted_field_data, $field_settings ) {
		$calculable_value = self::calculable_value( $submitted_field_data, $field_settings );

		/**
		 * Filter formula being used on calculable value on slider field
		 *
		 * @param int   $calculable_value Calculable value,
		 * @param array $submitted_field_data Original field value.
		 * @param array $field_settings Field settings.
		 *
		 * @return string|int
		 */
		return apply_filters( 'forminator_field_slider_calculable_value', $calculable_value, $submitted_field_data, $field_settings );
	}
}
