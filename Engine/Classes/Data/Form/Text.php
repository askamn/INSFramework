<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * [Form] Text
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

class Text extends FormAbstract
{
	/** 
     * Default options of this field
     *
     * @var 	array
     */
	protected $defaultOptions = [
		'minLength' 	       => NULL,  // minlength Attribute
		'maxLength'  	       => NULL,  // maxlength Attribute
		'regex'		 	       => NULL,  // Value will be checked against this regex
		'size' 		 	       => NULL,  // Size of the field
		'disabled'  	       => FALSE, // disabled Attribute
		'placeholder'          => NULL,  // Placeholder for this field
		'trimValue'		       => TRUE,  // Does the value need to be trimmed?
		'autoComplete'	       => FALSE, // Does this field AutoComplete
        'validationPattern'    => NULL,  // JS Based Validation Pattern
        'autofocus'            => FALSE, // HTML 5 Autofocus attr
        'icon'                 => FALSE, // FontAwesome Icon
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
        parent::__construct( $name, $value, $required, $options, $customValidationFunc, $fieldID, $addValidationJS );
    }

    /** 
     * Renders HTML
     *
     * @return      void
     */
    public function renderHtml()
    {
        $this->html = \INS\Template::i()->getTemplate( 'core', 'forms', 'textbox', $this )->renderOptions();

        $icon = ( $this->options['icon'] !== NULL ) ? sprintf( '<i class="icon fa fa-%s"></i>', $this->options['icon'] ) : '';

        $this->html = str_replace( 
            [ '{%name%}', '{%value%}', '{%attributes%}', '{%id%}', '{%validationjs%}', '{%required%}', '{%icon%}' ], 
            [ $this->name, $this->value, $this->attributes, $this->id, $this->validationJS(), ( $this->required == TRUE ? 'required="required"' : '' ), $icon ], 
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
			'size',     
			'placeholder',
            'autofocus',
            'maxLength',
            'regex'
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

                /* Because, in html, maxlength is lowercase */
                if( $attr == 'maxLength' )
                {
                    $attr = 'maxlength';
                }

                if( $attr == 'regex' )
                {
                    $attr = 'pattern';
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

    	/* Trim spaces */
        $value = ( $this->options['trimValue'] === TRUE ) ? trim( $value ) : $value;

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
        if( !$this->valid )
        {
            $this->errors = \INS\Language::i()->strings['form_value_invalid'];
            return $this->valid;
    	}

        if( $this->name === 'username' )
        {
            $filters = [
                'minlength' => ( \INS\Core::$settings['members']['username_minlength'] ) ? \INS\Core::$settings['members']['username_minlength'] : 4,
                'maxlength' => ( \INS\Core::$settings['members']['username_maxlength'] ) ? \INS\Core::$settings['members']['username_maxlength'] : 16, 
            ];

            if( !\INS\Data\Validation::i()->lengthInBetween( $this->value, $filters['minlength'], $filters['maxlength'], TRUE ) )
            {
                $this->errors = \INS\Language::i()->strings('form_value_lengthMustBeInBetween')->parse( $filters['minlength'], $filters['maxlength'] );
                $this->valid = FALSE;
            }
        }
        else
        {
            if( !empty( $this->options['minLength'] ) )
            {
                if( !\INS\Data\Validation::i()->minLength( $this->value, $this->options['minLength'] ) )
                {
                    $this->errors = \INS\Language::i()->strings('form_value_minLength')->parse( $this->options['minLength'] );
                    $this->valid = FALSE;
                }
            }

            if( !empty( $this->options['maxLength'] ) )
            {
                if( !\INS\Data\Validation::i()->maxLength( $this->value, $this->options['maxLength'] ) )
                {
                    $this->errors = \INS\Language::i()->strings('form_value_maxLength')->parse( $this->options['maxLength'] );
                    $this->valid = FALSE;
                }
            }
        }

        /* Apply the regex filter */
    	if( ( isset( $this->options['regex'] ) AND $this->options['regex'] !== NULL ) AND $this->valid )
    	{
    		try
            {
    			if( !preg_match( '#' . $this->options['regex'] . '#', $this->value ) )
                {
    				$this->errors = \INS\Language::i()->strings['form_value_invalid'];
                    $this->valid = FALSE;
    		    }
            }
            catch( \Exception $e )
            {
    			\INS\Errorhandler::logErrorToFile( "Invalid regex supplied to form filter." );
    	    }
        }

        return $this->valid;
    }

    /**
     * Adds validation JS related Data
     *
     * @return      boolean
     */
    public function validationJS()
    {
        if( $this->addValidationJS === TRUE )
        {
            return sprintf( 'data-validate=""', $this->options['validationPattern'] );
        }
    }
}