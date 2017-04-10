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

class CheckBoxMultiple Extends FormAbstract
{
	/** 
     * Default options of this field
     *
     * @var 	array
     * @brief 	$defaultoptions['options'] is an array of the format:
                array( 'value1' => array(
                            'text'               => 'string' // Text to display next to checkbox 
                            'disabled'           => FALSE,   // disabled Attribute
                            'showOnCheck'        => [],      // Array of element ids/classes to show when the checkbox is checked
                            'hideOnCheck'        => [],      // Array of element ids/classes to hide when the checkbox is checked 
                        ),
                        'value2' => array(
                            'text'               => 'string' // Text to display next to checkbox 
                            'disabled'           => FALSE,   // disabled Attribute
                            'showOnCheck'        => [],      // Array of element ids/classes to show when the checkbox is checked
                            'hideOnCheck'        => [],      // Array of element ids/classes to hide when the checkbox is checked 
                        ),
                        ....
                )
     */
	protected $defaultOptions = [
        'options'              => [],
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
    public function __construct( $name, $value, $required = FALSE, $options = [], $customValidationFunc = NULL, $fieldID = NULL, $addValidationJS = FALSE )
    {
    	if( empty( $options['options'] ) )
    	{
    		return;
    	}

        parent::__construct( $name, $value, $required, $options, $customValidationFunc, $fieldID, $addValidationJS );
    }

    /** 
     * Renders HTML
     *
     * @return      void
     */
    public function renderHtml()
    {
        if( empty( $this->options ) )
        {
            return;
        }

        $name = $this->name . '[]';
        $this->html = '';

        foreach( $this->options['options'] AS $value => $array )
        {
            $html = \INS\Template::i()->getTemplate( 'core', 'forms', 'checkbox', $this )->renderOptions( $array, $value );
            $this->id = $this->generateUniqueFieldId();
            $html = str_replace( 
                [ '{%name%}', '{%value%}', '{%attributes%}', '{%id%}', '{%required%}', '{%text%}' ], 
                [ $name, $value, $this->attributes, $this->id, ( $this->required == TRUE ? 'required="required"' : '' ), $array['text'] ], 
                $html 
            );

            $this->html .= $html;
        }

        $this->html = \INS\Template::i()->getTemplate( 'core', 'forms', 'checkbox_multiple' )->sprintf( $this->html );

        return $this->html;
    }

    /** 
     * Renders Options
     *
     * @return      void
     */
    public function renderOptions( $options, $value = NULL )
    {
        $this->attributes = '';
        
        /* Place attributes */
        if( $this->defaultValue === $value OR $options['checked'] === TRUE )
        {
            $this->attributes .= ' checked="checked"';
        }

        /* Is this field disabled */
        $this->attributes .= ( $options['disabled'] === TRUE ) ? ' disabled="disabled"' : '';
        
        if( !empty( $options['showOnCheck'] ) AND is_array( $options['showOnCheck'] ) )
        {   
            /* May be he is using ids? */ 
            if( preg_match( '/^(?!\.)/', $options['showOnCheck'][0] ) )
            {
                /* Assume ids */
                if( preg_match( '/^(?!\#)/', $options['showOnCheck'][0] ) )
                {
                    $this->attributes .= sprintf( ' data-toggles-oncheckshow="%s" ', '#' . implode( ',#', $options['showOnCheck'] ) );
                }
                else
                {
                    $this->attributes .= sprintf( ' data-toggles-oncheckshow="%s" ', implode( ',', $options['showOnCheck'] ) );
                }
            }
            /* The user *probably* is using classes */
            else
            {
                $this->attributes .= sprintf( ' data-toggles-oncheckshow="%s" ', implode( ',', $options['showOnCheck'] ) );
            }
        }

        if( !empty( $options['hideOnCheck'] ) AND is_array( $options['hideOnCheck'] ) )
        {
            /* May be he is using ids? */ 
            if( preg_match( '/^(?!\.)/', $options['hideOnCheck'][0] ) )
            {
                /* Assume ids */
                if( preg_match( '/^(?!\#)/', $options['hideOnCheck'][0] ) )
                {
                    $this->attributes .= sprintf( ' data-toggles-oncheckhide="%s" ', '#' . implode( ',#', $options['hideOnCheck'] ) );
                }
                else
                {
                    $this->attributes .= sprintf( ' data-toggles-oncheckhide="%s" ', implode( ',', $options['hideOnCheck'] ) );
                }
            }
            /* The user *probably* is using classes */
            else
            {
                $this->attributes .= sprintf( ' data-toggles-oncheckhide="%s" ', implode( ',', $options['hideOnCheck'] ) );
            }
        }

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

        return $value;
    }

    /** 
     * Generates the id for a field
     *
     * @return      string      Generated field id
     */
    public function generateUniqueFieldId()
    {
        return 'ins_id_' . preg_replace( '#\s+#', '', $this->name ) . '_' . uniqid();
    }

    /**
     * Validates the input
	 *
	 * @return 		boolean
	 */
    public function validate()
    {
        if( !parent::validate() )
        {
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

?>