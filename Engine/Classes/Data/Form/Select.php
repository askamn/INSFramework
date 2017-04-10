<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Form
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 - SVN_YYYY Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.5.0
 * @version     SVN_VERSION_NUMBER
 */

namespace INS\Data\Form;

class Select extends FormAbstract
{
	/** 
     * Default options of this field
     *
     * @var 	array
     */
	protected $defaultOptions = [
		'options'			   => [],    // Options (<option> HTML Attr) [ 'key1' => 'val1', 'key2' => 'val2' ]
		'multiple'			   => FALSE, // HTML multiple Attr
		'autofocus'			   => FALSE, // HTML 5 Autofocus Attr
		'disabled'			   => FALSE, // HTML disabled Attr
		'default'			   => NULL,  // The default option
		'useEmptyVal'		   => TRUE,  // Makes the first option an empty one 
        'icon'                 => NULL,  // The icon
        'description'          => NULL,  // Description for this field
	];

	/** 
     * Attributes
     *
     * @var 	string
     */
	protected $attributes = '';
	
 	/** 
     * Valid
     *
     * @var     string
     */
    public $valid = TRUE;

    /** 
     * Builds a field
     *
     * @param       string      Name of the field
     * @param       mixed       Default value/s for the field
     * @param       boolean     Required field
     * @param       array       Array of options  
     * @param       string      HTML id attribute of the field
     * @param       boolean     Should validation JS be attached to this field?   
     * @return      void
     */
    public function __construct( $name, $value = NULL, $required = FALSE, $options = [], $customValidationFunc = NULL, $fieldID = NULL, $addValidationJS = FALSE )
    {
        parent::__construct( $name, $value, $required, $options, $customValidationFunc, $fieldID, $addValidationJS );

        if( $value === NULL AND $this->options['useEmptyVal'] === TRUE )
        {
            $value = '_INS_NULL_VAL_';
        }
        elseif( $value === NULL AND $this->options['useEmptyVal'] === FALSE AND is_array( $this->options['options'] ) )
        {
            /* Make the first option the default one */
            reset( $this->options['options'] );
            $value = key( $this->options['options'] );
        }
        elseif( $value !== NULL AND $this->options['useEmptyVal'] === TRUE )
        {
            $_options['_INS_NULL_VAL_'] = '--';
            $this->options['options'] = array_merge( $_options, $this->options['options'] );
        }

        /* Is the default option in the array? */
        if( array_key_exists( $value, $this->options['options'] ) )
        {
            $this->selectedOption = $value;
        }
        else
        {
            if( is_array( $value ) AND array_key_exists( 'text', $value ) )
            {
                $this->options['options'][$value] = $value['text']; 
            }
            else
            {
                $_options['_INS_NULL_VAL_'] = '--';
                $this->options['options'] = array_merge( $_options, $this->options['options'] );
            }

            $this->selectedOption = $value;
        }
    }

    /** 
     * Renders HTML
     *
     * @return      void
     */
    public function renderHtml()
    {
        /* No Options? */
        if( empty( $this->options['options'] ) )
        {
            return;
        }

        $this->html = \INS\Template::i()->getTemplate( 'core', 'forms', 'select', $this )->renderOptions();

        $multiple = ( $this->options['multiple'] === TRUE ) ? ' multiple="multiple"' : '';
        $name = ( $this->options['multiple'] === TRUE ) ? $this->name . '[]' : $this->name;

        foreach( $this->options['options'] AS $value => $text )
        {
            /* What if the text contains HTML? */
            $text = htmlentities( $text, ENT_DISALLOWED | ENT_QUOTES, 'UTF-8' );
            /* Is this the selected option? */
            $selected = ( $value == $this->selectedOption ) ? 'selected="selected"' : '';
            /* Render them! */
            $options .= \INS\Template::i()->getTemplate( 'core', 'forms', 'select_option' )->sprintf( $value, $selected, $text );
        }

        $this->html = str_replace( 
            [ '{%name%}', '{%multiple%}', '{%attributes%}', '{%id%}', '{%validationjs%}', '{%required%}', '{%options%}' ], 
            [ $name, $multiple, $this->attributes, $this->id, $this->validationJS(), ( $this->required == TRUE ? 'required="required"' : '' ), $options ], 
            $this->html 
        );

        return $this->html;
    }

    /** 
     * Renders Options
     *
     * @return      void
     */
    public function renderOptions()
    {
    	$attributes = [
            'autofocus',
		];

		/* Place attributes */
        foreach( $attributes AS $attr )
        {
        	if( isset( $this->options[$attr] ) AND $this->options[$attr] !== NULL )
        	{
                if( $attr == 'autofocus' AND $this->options[$attr] === TRUE )
                {
                    $this->options[$attr] = 'autofocus';
                }

        		$this->attributes .= sprintf( " %s=\"%s\"", $attr, $this->options[$attr] );
        	}
        }

        /* Is this field disabled */
        $this->attributes .= ( $this->options['disabled'] === TRUE ) ? ' disabled="disabled"' : '';
        
        return \INS\Template::i()->lastRenderedTemplate;
    }

    /**
     * Gets value
	 *
	 * @return 		boolean
	 */
    public function get()
    {
    	$name = $this->name;
    	$value = \INS\Http::i()->$name;

        /* Remove Null Val if present */
        if( is_array( $value ) AND in_array( '_INS_NULL_VAL_', $value ) )
        {
            unset( $value[ array_search( '_INS_NULL_VAL_', $value ) ] );
        }
        else
        {
            if( $value === '_INS_NULL_VAL_' )
            {
                $value = '';
            }
        }

    	/* Trim spaces */
        $value = ( $this->options['trimValue'] === TRUE ) ? ( is_array( $value ) ? array_map( 'trim', $value ) : trim( $value ) ) : $value;

        return $value;
    }

    /**
     * Adds validation JS related Data
     *
     * @return      boolean
     */
    public function validationJS()
    {
        /* May be someone will find this useful, maybe? */
        if( $this->addValidationJS === TRUE )
        {
            return sprintf( 'data-js-extra=""', $this->options['js'] );
        }
    }

    /**
     * Validates the input
     *
     * @return      boolean
     */
    public function validate()
    {
        if( !parent::validate() )
        {
            return FALSE;
        }

        /* Not multiple but still the value is an array? Someone messing up, hmm */
        if( $this->options['multiple'] === FALSE AND is_array( $this->value ) )
        {
            $this->errors = \INS\Language::i()->strings['form_value_invalid'];
            return FALSE;
        }

        $value = ( !is_array( $this->value ) ) ? (array)$this->value : $this->value;

        if( empty( $value ) AND $this->required === TRUE AND $this->valid === TRUE )
        {
            $this->errors = \INS\Language::i()->strings['form_value_empty'];
            $this->valid = FALSE;
            return $this->valid;
        }
        
        /* Is the value inside the options? */
        if( !empty( array_diff( $value, array_keys( $this->options['options'] ) ) ) AND $this->valid === TRUE )
        { 
            $this->errors = \INS\Language::i()->strings['form_value_invalid'];
            $this->valid = FALSE;
            return $this->valid;
        }

        return $this->valid;
    }
}