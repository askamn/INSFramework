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

class CheckBox Extends FormAbstract
{
	/** 
     * Default options of this field
     *
     * @var 	array
     * @brief 	If for showOnCheck and hideOnCheck, array elements do not begin with a '.' or '#' they will be assumed as ids.
     */
	protected $defaultOptions = [
		'disabled'  	       => FALSE, // disabled Attribute
        'showOnCheck'	   	   => [],    // Array of element ids/classes to show when the checkbox is checked
        'hideOnCheck'		   => [],    // Array of element ids/classes to hide when the checkbox is checked
        'checked'			   => FALSE,
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
    public function __construct( $name, $value, $required = FALSE, $options = [], $customValidationFunc = NULL, $fieldID = NULL, $addValidationJS = FALSE )
    {
    	if( empty( $value ) )
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
        $this->html = \INS\Template::i()->getTemplate( 'core', 'forms', 'checkbox', $this )->renderOptions();

        $text = \INS\Language::i()->strings[ INS_MODULE . '_' . $this->name . '_text' ];

        $this->html = str_replace( 
            [ '{%name%}', '{%value%}', '{%attributes%}', '{%id%}', '{%required%}', '{%text%}' ], 
            [ $this->name, $this->value, $this->attributes, $this->id, ( $this->required == TRUE ? 'required="required"' : '' ), $text ], 
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
		/* Place attributes */
        if( $this->options['checked'] !== FALSE )
        {
        	$this->attributes .= ' checked="checked"';
        }

        /* Is this field disabled */
        $this->attributes .= ( $this->options['disabled'] === TRUE ) ? ' disabled="disabled"' : '';
        
        if( !empty( $this->options['showOnCheck'] ) AND is_array( $this->options['showOnCheck'] ) )
        {	
        	/* May be he is using ids? */ 
    		if( preg_match( '/^(?!\.)/', $this->options['showOnCheck'][0] ) )
    		{
    			/* Assume ids */
    			if( preg_match( '/^(?!\#)/', $this->options['showOnCheck'][0] ) )
    			{
    				$this->attributes .= sprintf( ' data-toggles-oncheckshow="%s" ', '#' . implode( ',#', $this->options['showOnCheck'] ) );
    			}
    			else
    			{
    				$this->attributes .= sprintf( ' data-toggles-oncheckshow="%s" ', implode( ',', $this->options['showOnCheck'] ) );
    			}
      		}
    		/* The user *probably* is using classes */
    		else
    		{
    			$this->attributes .= sprintf( ' data-toggles-oncheckshow="%s" ', implode( ',', $this->options['showOnCheck'] ) );
    		}
        }

        if( !empty( $this->options['hideOnCheck'] ) AND is_array( $this->options['hideOnCheck'] ) )
        {
        	/* May be he is using ids? */ 
    		if( preg_match( '/^(?!\.)/', $this->options['hideOnCheck'][0] ) )
    		{
    			/* Assume ids */
    			if( preg_match( '/^(?!\#)/', $this->options['hideOnCheck'][0] ) )
    			{
    				$this->attributes .= sprintf( ' data-toggles-oncheckhide="%s" ', '#' . implode( ',#', $this->options['hideOnCheck'] ) );
    			}
    			else
    			{
    				$this->attributes .= sprintf( ' data-toggles-oncheckhide="%s" ', implode( ',', $this->options['hideOnCheck'] ) );
    			}
      		}
    		/* The user *probably* is using classes */
    		else
    		{
    			$this->attributes .= sprintf( ' data-toggles-oncheckhide="%s" ', implode( ',', $this->options['hideOnCheck'] ) );
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

        /* Lets check the validity and then move on */
        if( !$this->value OR $this->value !== $this->defaultValue )
        {
            $this->errors = \INS\Language::i()->strings['form_value_invalid'];
            $this->valid = FALSE;
            return $this->valid;
    	}

        return $this->valid;
    }
}

?>