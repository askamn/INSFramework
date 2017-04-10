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

namespace INS\Data;

class Form
{
    /**
     * @var         string  Name of the Form
     */
    public $name;

    /**
     * @var         string  Id of the form
     */
    public $id;

    /**
     * @var         string  Additional JS
     */
    public $additionalJS = '';

    /**
     * @var         string  Action attribute of the form
     */
    public $action;

    /**
     * @var         array   Array of options
     */
    public $options;

    /**
     * @var         boolean     
     */
    public $valid;

    /**
     * @var         array   Default button parameters
     * @brief 
        lang            -> Corresponds to the language identifier
        classes         -> Array of classes that will be added to this button
        id              -> id HTML attribute of the button
        name            -> name HTML attribute of the button
        js              -> JS based stuff to be added. Value type must be an array
     */
    public $defaultButton = [
        'lang'          => 'default',
        'classes'       => [ 'btn', 'ins-btn' ],
        'id'            => 'ins-btn',
        'name'          => 'submit',
        'js'            => '',
    ];

    /**
     * @var         array   Default Reset button parameters
     */
    public $resetButton = [
        'lang'          => 'reset',
        'classes'       => [ 'btn', 'ins-reset' ],
        'id'            => 'ins-btn',
        'name'          => 'resetForm',
        'js'            => [ "jQuery('.ins-reset').click(function(e){ jQuery('#<formname>').trigger('reset'); e.preventDefault(); });" ],
    ];

    /**
     * @var         array
     */
    public $globalFormTemplates = [ 'wrapperTemplate', 'label', 'hiddenfield', 'form', 'button', 'rowerror', 'rowdesc', 'ajaxify' ];


    /** 
     * Builds the form
     *
     * @param       string       The Form "action" attribute
     * @param       string       Name of the form
     * @param       string       "id" attribute of the Form
     * @param       array        Array of Options to be applied to the form
     * @param       string       Language Identifier of the button string
     * @param       boolean      Reset button to clear all fields
     * @param       array        Array of buttons to be added
     * @return      void
     */
    public function __construct( $name, $id=NULL, $action=NULL, $options=[], $button = NULL, $addResetButton = FALSE, $otherButtons = []  )
    {
        $this->name         = $name;
        $this->action       = ( $action === NULL ) ? \INS\Http::i()->link() : $action;
        $this->id           = ( is_null( $id ) ) ? $this->generateUniqueId() : \INS\Filter::i()->filterString( $id );
        $this->buttons      = $otherButtons;
        $this->buttons[]    = ( $button === NULL ? $this->defaultButton : array_merge( $this->defaultButton, $button ) );
        $this->buttons[]    = ( $addResetButton === TRUE ? $this->resetButton : NULL  );
        $this->options      = $options; 

        /* Initialise the hidden fields */
        $this->hiddenFields['crumb'] = \INS\Http::i()->generateCrumb(); 
    }

    /** 
     * Generates the id for the form
     *
     * @return      string      Generated id
     */
    public function generateUniqueId()
    {
        return 'ins_id_' . preg_replace( '#\s+#', '', $this->name );
    }

    /** 
     * Adds a field to the form
     *
     * @param       \INS\Data\Form\[Element]    The element object
     * @return      void
     */
    public function push( )
    {
        $args = func_get_args();
        
        if( func_num_args() == 1 )
        {
            $this->Fields[][ $args[0]->name ] = $args[0];
            return;
        }
        
        foreach( $args AS $field )
        {
            $this->Fields[][ $field->name ] = $field;
        }
    }

    /** 
     * Completes the HTML of the form
     *
     * @param       \INS\Data\Form\[Element]    The element object
     * @return      void
     */
    public function render( )
    {
        foreach( $this->Fields AS $field )
        {
            if( is_array( $field ) )
            {
                foreach( $field AS $name => $fieldObj )
                {
                    $string .= "\n" . $fieldObj;
                }
            }
            else
            {
                $string .= (string)$field;
            } 
        }

        if( !empty( $this->options ) )
        {
            foreach( $this->options AS $attribute => $value )
            {
                if( in_array( mb_strtolower( $attribute ), [ 'accept-charset', 'ajaxify' ] ) )
                {
                    continue;
                }

                $attr .= sprintf( ' %s="%s"', $attribute, $value );
            }
        }    

        if( $this->options['ajaxify'] === TRUE )
        {
            $this->additionalJS .= \INS\Template::i()->getTemplate( 'core', 'forms', 'ajaxify' )->sprintf( $this->name );
        }

        $string .= $this->renderHiddenFields();

        $i = 0;
        foreach( $this->buttons AS $button )
        {
            if( $button === NULL )
            {
                continue;
            }

            /* The user only wants to specify the language identifier, so we use the default options for the rest properties */
            if ( count( array_keys( $button ) ) == 1 AND isset( $button['lang'] ) )
            {
                $button['classes'] = $this->defaultButton['classes'];

                if( $i )
                {
                    $button['name'] = $this->defaultButton['name'] . $i;
                }

                $i++;
            } 

            if( is_array( $button['js'] ) )
            {
                \INS\Template::i()->pushJsToStack( str_replace( '<formname>', $this->id, implode( ' ', $button['js'] ) ) );
            }

            $buttons .= \INS\Template::i()->getTemplate( 'core', 'forms', 'button' )->sprintf( 
                ( isset( $button['id'] ) ? $button['id'] : 'ins-btn' ), 
                implode( ' ', $button['classes'] ), 
                \INS\Language::i()->strings[ 'form_buttons_' . $button['lang'] ],
                $button['name']
            ); 

            $buttons =  str_replace( '{%BUTTONTYPE%}', ( ( $button['name'] == 'submit' ) ? 'submit' : 'button'), $buttons );
        }

        return \INS\Template::i()->getTemplate( 'core', 'forms', 'form' )->sprintf( $this->name, $this->action, $this->id, $attr, $string, $buttons, $this->additionalJS );        
    }

    /** 
     * Renders the HTML of hidden fields
     *
     * @param       \INS\Data\Form\[Element]    The element object
     * @return      void
     */
    public function renderHiddenFields( )
    {
        foreach( $this->hiddenFields AS $name => $value )
        {
            $return .= \INS\Template::i()->getTemplate( 'core', 'forms', 'hiddenfield' )->sprintf( $name, $value ) . "\n";
        }

        return $return;
    }    

    /** 
     * Magic Method
     *
     * @return     string
     */
    public function __tostring( )
    {
        return $this->render();
    }    

    /**
     * Returns False if the form wasn't submitted or contains errors
     *
     * @return  boolean|array   
     */
    public function validate()
    {
        if( \INS\Http::i()->request_method == 'post' AND \INS\Http::i()->crumb === \INS\Session::i()->get( 'ins.csrfkey' ) )
        {
            foreach( $this->Fields AS $field )
            {
                if( !is_array($field) )
                {
                    $field = (array)$field;
                }

                foreach( $field AS $name => $fieldObj )
                {
                    /* Is this not a form field; */
                    if( !( $fieldObj instanceof Form\FormAbstract  ) )
                    {
                        continue;
                    }

                    if( !$fieldObj->valid )
                    {
                        return FALSE;
                    }

                    $return[$name] = $fieldObj->value;
                }
            }    

            return $return;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Adds a horizontal Separator
     *
     * @return  void
     */
    public function addSeparator()
    {
        $this->Fields[] = \INS\Template::i()->getTemplate( 'core', 'forms', 'separator' )->sprintf();
    }

    /**
     * Adds heading
     *
     * @param   string      The Text the heading will display
     * @param   int         The font size (in pixels)
     * @return  void
     */
    public function addHeading( $text, $fontSize = NULL )
    {
        if( !mb_strlen( $text ) )
        {
            return;
        }

        if( $fontSize !== NULL )
        {
            $text = sprintf( '<span style="font-size: %spx">%s</span>', $fontSize, $text );
        } 

        $this->Fields[] = \INS\Template::i()->getTemplate( 'core', 'forms', 'heading' )->sprintf( $text );
    }

    /**
     * Adds a horizontal Separator
     *
     * @return  void
     */
    public function preCacheTemplates( )
    {
        $templates = array_merge( $this->globalFormTemplates, func_get_args() );

        foreach( $templates AS $template )
        {
            /* Present in cache already */
            if( array_key_exists( 'core_forms_' . $template, \INS\Template::i()->fetchedTemplates ) )
                continue;
            else
                $list[] = $template;
        }

        if( !empty( $list ) ) 
        {
            \INS\Template::i()->pushToCache( '\'core_forms_' . implode( "', 'core_forms_", $list ) . '\'' );
        }
    }
}
