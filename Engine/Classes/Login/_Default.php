<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.6.0
 * Login Abstract Class
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

namespace INS\Login;

class _Default extends \INS\Login
{
    /**
     * @var     string      Name of Login Handler
     */
    public $name = '_Default';

    /**
     * @var     array       Array of required fields
     */
    public $fields = array();

    /** 
     * Loads the instance of this class
     *
     * @return      resource
     * @access      public
     */
    public static function i()
    {
        $arguments = func_get_args();
        return !empty( $arguments ) ? \INS\Core::getInstance( __CLASS__, $arguments) : \INS\Core::getInstance( __CLASS__ );
    }

    /**
     * Constructor of class.
     *
     * @param       integer
     * @return      void
     */
    public function __construct()
    {
        \INS\Core::checkState( __CLASS__ ); 
    }

    /**
     * Authenticate a user
     *
     * @param       array   (Optional) Array of values on which login handler will work on
     * @return      void
     * @access      public
     */
    public function authenticate()
    { 
        if( parent::authenticate() !== TRUE )
        {
            $this->valid = FALSE;
        }

        if( $this->valid === TRUE )
        {
            if( ( $member = \INS\Users::i()->validate( $this->values['username'], $this->values['password'] ) ) !== FALSE )
            {
                $this->member = $member;
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Returns thw html of this handler
     */
    public function getHandlerForm()
    {
        $form = new \INS\Data\Form( 'login', NULL, NULL, [ 'style' => 'width: 100%' ], [ 'lang' => 'login' ] );

        /* Build the cache */
        $form->preCacheTemplates( 'textbox', 'password', 'heading' );

        $form->addHeading( \INS\Language::i()->strings['members_login_form_heading'] );

        /* Build the form fields */
        $form->push( new \INS\Data\Form\Text( 'username', NULL, TRUE,
            [ 
                'minlength' => ( \INS\Core::$settings['members']['username_minlength'] ) ? \INS\Core::$settings['members']['username_minlength'] : 4,
                'maxlength' => ( \INS\Core::$settings['members']['username_maxlength'] ) ? \INS\Core::$settings['members']['username_maxlength'] : 16, 
                'autofocus' => TRUE,
                'icon'      => 'user'
            ]
        ) );

        /* Password field */
        $form->push( new \INS\Data\Form\Password( 'password', NULL, TRUE, [ 'icon' => 'lock' ] ) );

        /* Only proceed further if the stuff is valid */
        if( $this->values = $form->validate() )
        {
            if( $this->authenticate() )
            {
                \INS\Http::i()->redirect( \INS\Core::$settings['site']['url'] );
            }

            \INS\Template::i()->addNotification( \INS\Language::i()->strings['members_login_failed'] );
        }   

        return (string)$form;
    }
}