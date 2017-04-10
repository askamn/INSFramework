<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.6.0
 * Form Abstract Class
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 - SVN_YYYY Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.6.0
 * @version     SVN_VERSION_NUMBER
 */

namespace INS\Data\Form;

abstract class FormAbstract
{
    /**
     * @var     string
     */
    public $name;

    /**
     * @var     mixed
     */
    public $value;
    
    /**
     * @var     boolean
     */
    protected $required;
    
    /**
     * @var     array
     */
    protected $options;

    /**
     * @var     string The HTML id attribute
     */
    protected $id;

    /**
     * @var     string
     */
    protected $defaultValue;

    /**
     * @var     string
     */
    protected $errors;

    /**
     * @var     boolean
     */
    public $valid;

    /**
     * @var     string
     */
    protected $langIdentifier;

    /**
     * @var     array   Array of Pushed Elements
     */
    static protected $pushedElements = [];

    /**
     * @var     array   Array of occurences of an element
     */
    static protected $occurences = [];
    
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
    public function __construct( $fieldName, $fieldValue = NULL, $required = FALSE, $options = [], $customValidationFunc = NULL, $fieldId = NULL, $addValidationJS = FALSE )
    {
        $this->name                 = $fieldName;
        $this->defaultValue         = ( $fieldValue === NULL ) ? '' : $fieldValue;
        $this->id                   = ( is_null( $fieldId ) ) ? $this->generateUniqueFieldId() : \INS\Filter::i()->filterString( $fieldId );
        $this->options              = array_filter( array_merge( $this->defaultOptions, $options ) );
        $this->required             = $required;
        $this->addValidationJS      = $addValidationJS;
        $this->validationFunction   = $customValidationFunc;

        $this->set();
    }

    /** 
     * Sets the value of a field
     *
     * @return      void
     */
    public function set()
    {
        $this->langIdentifier = $this->name;
        
        if( !in_array( $this->name, static::$pushedElements ) )
        {
            static::pushToStack( $this->name );
        }
        else
        {
            $this->name = static::figureOutElementName( $this->name );
        }

        $name = $this->name;

        if( isset( \INS\Http::i()->$name ) )
        {
            $this->value = $this->get();

            /* We don't want html */
            \INS\Filter::i()->optionalRules = ['XSSTags'];
            $this->value = \INS\Http::i()->$name = \INS\Filter::i()->filter( $this->value );
            $this->validate();
        }
        else
        {
            $this->value = $this->defaultValue;
        }
    }

    /** 
     * Generates the id for a field
     *
     * @return      string      Generated field id
     */
    public function generateUniqueFieldId()
    {
        return 'ins_id_' . preg_replace( '#\s+#', '', $this->name );
    }

    /** 
     * Magic method
     *
     * @return      string      Generated field id
     */
    public function __tostring()
    {
        return $this->render();
    }

    /** 
     * Renders HTML
     *
     * @return      void
     */
    public function render()
    {  
        if( empty( $this->label ) )
        {
            $this->label = \INS\Template::i()->getTemplate( 'core', 'forms', 'label' )->sprintf( \INS\Language::i()->strings[ INS_MODULE . '_' . $this->langIdentifier . '_label' ] );
        }  
        if( !empty( $this->errors ) )
        {
            $this->errorbox = \INS\Template::i()->getTemplate( 'core', 'forms', 'rowerror' )->sprintf( $this->errors );
        }
        if( !empty( $this->description ) OR $this->options['description'] !== NULL )
        {
            $description = !empty( $this->description ) ? $this->description : $this->options['description'];
            $this->description = \INS\Template::i()->getTemplate( 'core', 'forms', 'rowdesc' )->sprintf( $description );
        }

        $elementHtml = '<div class="ins-input-wrap">' . $this->renderHtml() . '</div>';

        if( isset( $this->options['description_top'] ) AND $this->options['description_top'] === TRUE )
        {
            $this->description = str_replace( '{%TOP%}', ' top', $this->description );
            $this->html = \INS\Template::i()->getTemplate( 'core', 'forms', 'wrapperTemplate' )->sprintf( $this->label, $this->description, $elementHtml, $this->errorbox );
            return $this->html;
        }

        $this->description = str_replace( '{%TOP%}', '', $this->description );
        $this->html = \INS\Template::i()->getTemplate( 'core', 'forms', 'wrapperTemplate' )->sprintf( $this->label, $elementHtml, $this->description, $this->errorbox );

        return $this->html;
    }

    /**
     * Pushes an element's name in our array
     *
     * @return      void
     */
    static public function pushToStack( $name )
    {
        static::$pushedElements[] = $name;
        static::$occurences[$name] = ( !isset( static::$occurences[$name] ) ) ? 1 : static::$occurences[$name] + 1;
    }

    /**
     * Figure's out the name of the Element
     *
     * @return      void
     */
    static public function figureOutElementName( $name )
    {
        return $name . static::$occurences[$name]++;
    }

    /**
     * Validates input
     * @brief   Actually this function is just to 
     *          run Custom Validation Codes and catch their 
     *          exceptions and send them to the form.
     *
     * @return  boolean
     */
    public function validate()
    {
        if( is_array( $this->value ) AND empty( $this->value ) )
        {
            $this->errors = \INS\Language::i()->strings['form_value_empty'];
        }
        else
        {
            /* Field is required but is still empty */
            $this->valid = ( $this->required AND !mb_strlen( $this->value ) ? FALSE : TRUE );

            if( !$this->valid )
            {
                $this->errors = \INS\Language::i()->strings['form_value_empty'];
            }
        }

        if( !is_null( $this->validationFunction ) AND $this->valid )
        {
            try 
            {
                call_user_func( $this->validationFunction, $this->value );
            }
            catch ( \Exception $e )
            {
                $this->errors = (string)\INS\Language::i()->strings( $e->getMessage(), 'form' );
                $this->valid = FALSE;
            }
        }

        return $this->valid;
    }
}