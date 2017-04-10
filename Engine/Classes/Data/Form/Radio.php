<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Radio
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

class Radio Extends CheckBoxMultiple
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

        $this->html = '';

        foreach( $this->options['options'] AS $value => $array )
        {
            $html = \INS\Template::i()->getTemplate( 'core', 'forms', 'radio', $this )->renderOptions( $array, $value );
            $this->id = $this->generateUniqueFieldId();
            $html = str_replace( 
                [ '{%name%}', '{%value%}', '{%attributes%}', '{%id%}', '{%required%}', '{%text%}' ], 
                [ $this->name, $value, $this->attributes, $this->id, ( $this->required == TRUE ? 'required="required"' : '' ), $array['text'] ], 
                $html 
            );

            $this->html .= $html;
        }

        $this->html = \INS\Template::i()->getTemplate( 'core', 'forms', 'checkbox_multiple' )->sprintf( $this->html );

        return $this->html;
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

        if( empty( $this->value ) AND $this->required === TRUE AND $this->valid === TRUE )
        {
            $this->errors = \INS\Language::i()->strings['form_value_empty'];
            $this->valid = FALSE;
            return $this->valid;
        }
        
        /* Is the value inside the options? */
        if( !in_array( $this->value, array_keys( $this->options['options'] ) ) AND $this->valid === TRUE )
        { 
            $this->errors = \INS\Language::i()->strings['form_value_invalid'];
            $this->valid = FALSE;
            return $this->valid;
        }

        return $this->valid;
    }
}

?>