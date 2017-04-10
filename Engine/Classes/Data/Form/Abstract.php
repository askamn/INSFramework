<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Form Abstract Class
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

abstract class Abstract
{
    /**
     * @var     string
     */
    protected $name;

    /**
     * @var     mixed
     */
    protected $value;
    
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
    public function __construct( $fieldName, $fieldValue = NULL, $required = FALSE, $options = [], $fieldId = NULL, $addValidationJS = FALSE )
    {
        $this->name             = $fieldName;
        $this->value            = $fieldValue;
        $this->id               = ( is_null( $fieldId ) ) ? $this->generateUniqueFieldId() : \INS\Filter::i()->filterString( $fieldId );
        $this->options          = $options;
        $this->required         = $required;
        $this->addValidationJS  = $addValidationJS;
    }

    /** 
     * Generates the id for a field
     *
     * @return      string      Generated field id
     */
    public function generateUniqueFieldId()
    {
        return 'id_' . preg_replace( '#\s+#', '', $this->name );
    }

    /** 
     * Magic method
     *
     * @return      string      Generated field id
     */
    public function __tostring()
    {
        return $this->renderHtml();
    }

    /** 
     * Renders HTML
     *
     * @return      void
     */
    public function renderHtml()
    {
        $this->html = (string)$this->renderedElementObj;

        /* Create attributes */
        foreach( $this->options AS $attr => $value )
            $attributes .= sprintf( " %s=\"%s\"", $attr, $value );

        $this->html = str_replace( 
            [ '{%name%}', '{%value%}', '{%attributes%}', '{%id%}', '{%validationjs%}', '{%required%}' ], 
            [ $this->name, $this->value, $attributes, $this->id, $this->validationJS(), ( $required == TRUE ? 'required=""' : '' ) ], 
            $this->html 
        );

        $this->html = \INS\Template::i()->getTemplate( 'core', 'forms', 'wrapperTemplate', $this )->renderDivisions( );
    }

    /** 
     * Adds <div> tag to the rendered Html with proper attributes
     *
     * @return      string      renderedDivs
     */
    public function renderDivisions()
    {
        return sprintf( \INS\Template::i()->lastRenderedTemplate, $this->html );
    }
}