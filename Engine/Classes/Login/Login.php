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

namespace INS;

class Login
{
    /**
     * @var     string 		Name of Login Handler
     */
    public $name;

    /** 
     * @var 	boolean 	Login Validity
     */
    public $valid = FALSE;

    /**
     * @var 	array 		Array of required fields
     */
    public $fields = array();

    /**
     * @var 	array 		Array of Form Values
     */
    public $values = NULL;

    /**
     * @var 	array 		Array of login handlers
     */
    public $handlers = [
    	'default'  => '_Default', 
    	'facebook' => 'Facebook', 
    	'twitter'  => 'Twitter',
   	];

    /** 
     * Loads the instance of this class
     *
     * @return      resource
     * @access 		public
     */
    public static function i()
    {
        $arguments = func_get_args();
        return !empty( $arguments ) ? \INS\Core::getInstance( __CLASS__, $arguments) : \INS\Core::getInstance( __CLASS__ );
    }

	/**
	 * Constructor of class.
	 *
	 * @param 		integer
	 * @return 		void
	 */
	public function __construct( $handler = NULL )
	{
		\INS\Core::checkState( __CLASS__ ); 

		if( $values === NULL OR !in_array( $handler, $this->handlers ) )
		{
			$this->handler = 'default';
		} 

		/* Handler Disabled? */
		if( \INS\Core::$loginHandlers[ $this->handler ] === FALSE )
		{
			\INS\Template::i()->error( \INS\Language::i()->strings['ins_errors_global_loginhandlerdisabled'] );
		}

		$name = '\\INS\\Login\\' . $this->handlers[ $this->handler ];
		$this->handlerInstance = $name::i();
	}

    /**
	 * Authenticate a user
	 *
	 * @param 		array 	(Optional) Array of values on which login handler will work on
	 * @return 		void
	 * @access  	public
	 */
    public function authenticate()
    {
    	foreach( $this->fields AS $field => $options )
    	{
    		if( empty( $this->values[ $field ] ) )
    		{
    			if( isset( $options['optional'] ) AND $options['optional'] === TRUE )
    			{
    				continue;
    			}

    			$this->valid = FALSE;
    			break;
    		}
    	}

    	$this->valid = TRUE;

    	return TRUE;
    }

    /**
     * Hash Function
     */
    public function hash()
    {

    }

    /**
     * Generates the form HTML
     */
    public function getHandlerForm()
    {
    	return $this->handlerInstance->getHandlerForm();
    }
}
