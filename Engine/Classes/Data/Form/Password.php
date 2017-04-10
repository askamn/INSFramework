<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Password
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

class Password extends Text
{
    /** 
     * Renders HTML
     *
     * @return      void
     */
    public function renderHtml()
    {
        $this->html = \INS\Template::i()->getTemplate( 'core', 'forms', 'password', $this )->renderOptions();

        $icon = ( $this->options['icon'] !== NULL ) ? sprintf( '<i class="icon fa fa-%s"></i>', $this->options['icon'] ) : '';

        $this->html = str_replace( 
            [ '{%name%}', '{%value%}', '{%attributes%}', '{%id%}', '{%validationjs%}', '{%required%}', '{%icon%}' ], 
            [ $this->name, $this->value, $this->attributes, $this->id, $this->validationJS(), ( $this->required == TRUE ? 'required=""' : '' ), $icon ], 
            $this->html 
        );

        return $this->html;
    }

    /**
     * Validates the input
	 *
	 * @return 		boolean
	 */
    public function validate()
    {
        /* Don't go any further if previous checks failed */
        if( !parent::validate() )
        {
            return FALSE;
        }

        if( \INS\Core::$settings['members']['requireComplexPassword'] )
        {
            if( \INS\Data\Validation::i()->isComplex( $this->value ) === FALSE )
            {
                $this->errors = \INS\Language::i()->strings['form_password_notComplex'];
                $this->valid = FALSE;
            }
        }

        return $this->valid;        
    }
}