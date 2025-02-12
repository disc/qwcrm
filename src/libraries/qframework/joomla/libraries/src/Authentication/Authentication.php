<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Authentication;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Plugin\PluginHelper;
/**
 * Authentication class, provides an interface for the Joomla authentication system
 *
 * @since  1.7.0
 */
class Authentication //extends \JObject
{
    // Shared success status
    /**
     * This is the status code returned when the authentication is success (permit login)
     *
     * @var    integer
     * @since  1.7.0
     */
    const STATUS_SUCCESS = 1;

    // These are for authentication purposes (username and password is valid)
    /**
     * Status to indicate cancellation of authentication (unused)
     *
     * @var    integer
     * @since  1.7.0
     */
    const STATUS_CANCEL = 2;

    /**
     * This is the status code returned when the authentication failed (prevent login if no success)
     *
     * @var    integer
     * @since  1.7.0
     */
    const STATUS_FAILURE = 4;

    // These are for authorisation purposes (can the user login)
    /**
     * This is the status code returned when the account has expired (prevent login)
     *
     * @var    integer
     * @since  1.7.0
     */
    const STATUS_EXPIRED = 8;

    /**
     * This is the status code returned when the account has been denied (prevent login)
     *
     * @var    integer
     * @since  1.7.0
     */
    const STATUS_DENIED = 16;

    /**
     * This is the status code returned when the account doesn't exist (not an error)
     *
     * @var    integer
     * @since  1.7.0
     */
    const STATUS_UNKNOWN = 32;

    /**
     * An array of Observer objects to notify
     *
     * @var    array
     * @since  3.0.0
     */
    protected $observers = array();

    /**
     * The state of the observable object
     *
     * @var    mixed
     * @since  3.0.0
     */
    protected $state = null;

    /**
     * A multi dimensional array of [function][] = key for observers
     *
     * @var    array
     * @since  3.0.0
     */
    protected $methods = array();

    /**
     * @var    Authentication  Authentication instances container.
     * @since  1.7.3
     */
    protected static $instance;

    /**
     * Constructor
     *
     * @since   1.7.0
     */
    
    // Authentication plugins that I added
    protected $PlgSystemRemember = null;
    protected $PlgAuthenticationCookie = null;
    protected $PlgUserJoomla = null;
    
    public function __construct()
    {
        
        // Authentication plugins that I added
        $this->PlgSystemRemember  = new \PlgSystemRemember;
        $this->PlgAuthenticationCookie = new \PlgAuthenticationCookie;
        $this->PlgUserJoomla  = new \PlgUserJoomla;
        
        /*
        $isLoaded = JPluginHelper::importPlugin('authentication');

        if (!$isLoaded)
        {
            \JLog::add(JText::_('JLIB_USER_ERROR_AUTHENTICATION_LIBRARIES'), \JLog::WARNING, 'jerror');
        }
        */
    }

    /**
     * Returns the global authentication object, only creating it
     * if it doesn't already exist.
     *
     * @return  Authentication  The global Authentication object
     *
     * @since   1.7.0
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            self::$instance = new Authentication;
        }

        return self::$instance;
    }

    /**
     * Get the state of the Authentication object
     *
     * @return  mixed    The state of the object.
     *
     * @since   1.7.0
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Attach an observer object
     *
     * @param   object  $observer  An observer object to attach
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function attach($observer)
    {
        if (is_array($observer))
        {
            if (!isset($observer['handler']) || !isset($observer['event']) || !is_callable($observer['handler']))
            {
                return;
            }

            // Make sure we haven't already attached this array as an observer
            foreach ($this->observers as $check)
            {
                if (is_array($check) && $check['event'] == $observer['event'] && $check['handler'] == $observer['handler'])
                {
                    return;
                }
            }

            $this->observers[] = $observer;
            end($this->observers);
            $methods = array($observer['event']);
        }
        else
        {
            if (!($observer instanceof Authentication))
            {
                return;
            }

            // Make sure we haven't already attached this object as an observer
            $class = get_class($observer);

            foreach ($this->observers as $check)
            {
                if ($check instanceof $class)
                {
                    return;
                }
            }

            $this->observers[] = $observer;
            $methods = array_diff(get_class_methods($observer), get_class_methods('\\JPlugin'));
        }

        $key = key($this->observers);

        foreach ($methods as $method)
        {
            $method = strtolower($method);

            if (!isset($this->methods[$method]))
            {
                $this->methods[$method] = array();
            }

            $this->methods[$method][] = $key;
        }
    }

    /**
     * Detach an observer object
     *
     * @param   object  $observer  An observer object to detach.
     *
     * @return  boolean  True if the observer object was detached.
     *
     * @since   1.7.0
     */
    public function detach($observer)
    {
        $retval = false;

        $key = array_search($observer, $this->observers);

        if ($key !== false)
        {
            unset($this->observers[$key]);
            $retval = true;

            foreach ($this->methods as &$method)
            {
                $k = array_search($key, $method);

                if ($k !== false)
                {
                    unset($method[$k]);
                }
            }
        }

        return $retval;
    }

    /**
     * Finds out if a set of login credentials are valid by asking all observing
     * objects to run their respective authentication routines.
     *
     * @param   array  $credentials  Array holding the user credentials.
     * @param   array  $options      Array holding user options.
     *
     * @return  AuthenticationResponse  Response object with status variable filled in for last plugin or first successful plugin.
     *
     * @see     AuthenticationResponse
     * @since   1.7.0
     * 
     * will read cookie and then Joomla standard username and password
     */
    public function authenticate($credentials, $options = array())
    {
        // Get plugins - this checks to see if a cookie can authenticate
        // $plugins = JPluginHelper::getPlugin('authentication');
        $plugins = array(
                        array ('className' => 'PlgAuthenticationCookie',
                                'type' => 'Authentication',
                                'name' => 'Cookie'),
                        array ('className' => 'PlgAuthenticationJoomla',
                                'type' => 'Authentication',
                                'name' => 'Joomla')
                        );
                
        // Create authentication response - holds the response(s)
        $response = new AuthenticationResponse;
        
        /*
         * Loop through the plugins and check if the credentials can be used to authenticate
         * the user
         *
         * Any errors raised in the plugin should be returned via the AuthenticationResponse
         * and handled appropriately.
         */
        foreach ($plugins as $plugin)
        {
            //$className = 'plg' . $plugin->type . $plugin->name;
            $className = 'plg' . $plugin['type'] . $plugin['name'];            

            if (class_exists($className))
            {
                $plugin = new $className($this, (array) $plugin);
            }
            else
            {
                // Bail here if the plugin can't be created
                //\JLog::add(\JText::sprintf('JLIB_USER_ERROR_AUTHENTICATION_FAILED_LOAD_PLUGIN', $className), \JLog::WARNING, 'jerror');                
                continue;
            }

            // Try to authenticate
            $plugin->onUserAuthenticate($credentials, $options, $response);            
            
            // If authentication is successful break out of the loop
            if ($response->status === self::STATUS_SUCCESS)
            {
                if (empty($response->type))
                {
                    $response->type = isset($plugin->_name) ? $plugin->_name : $plugin->name;
                }

                break;
            }
        }        

        if (empty($response->username))
        {
            $response->username = $credentials['username'];
        }

        if (empty($response->fullname))
        {
            $response->fullname = $credentials['username'];
        }

        if (empty($response->password) && isset($credentials['password']))
        {
            $response->password = $credentials['password'];
        }        
        
        return $response;
    }

    /**
     * Authorises that a particular user should be able to login
     *
     * @param   AuthenticationResponse  $response  response including username of the user to authorise
     * @param   array                    $options   list of options
     *
     * @return  AuthenticationResponse[]  Array of authentication response objects
     *
     * @since  1.7.0
     */
    public static function authorise($response, $options = array())
    {
        
        // this is supposed to cycle through auth plugins that have this event and process them
        // cookie.php (rememberme) and joomla.php do not have this    
        
        // if user has been blocked or deactivates return the result - i can add stuff here        
        
        /*
        // Get plugins in case they haven't been imported already
        //PluginHelper::importPlugin('user');
        //PluginHelper::importPlugin('authentication');        
        //$dispatcher = \JEventDispatcher::getInstance();
        //$results = $dispatcher->trigger('onUserAuthorisation', array($response, $options));
        */
        
        $results = array();

        return $results;
    }
    
    /**************************** Extra Functions Added ****************************/
    
    /**
     * Login authentication function.  - from libraries/legacy/application/application.php
     *
     * Username and encoded password are passed the onUserLogin event which
     * is responsible for the user validation. A successful validation updates
     * the current session record with the user's details.
     *
     * Username and encoded password are sent as credentials (along with other
     * possibilities) to each observer (authentication plugin) for user
     * validation.  Successful validation will update the current session with
     * the user details.
     *
     * @param   array  $credentials  Array('username' => string, 'password' => string)
     * @param   array  $options      Array('remember' => boolean)
     *
     * @return  boolean|JException  True on success, false if failed or silent handling is configured, or a JException object on authentication error.
     *
     * @since   3.2
     */
    public function login($credentials, $options = array())
    {
        // Get the global JAuthentication object.
        //$authenticate = JAuthentication::getInstance();        
        $response = $this->authenticate($credentials, $options); // this cycles through the plugins (qwcrm.php remember.php methods) and collates the responses in a 'reponse class' and then returns it

        // Import the user plugin group. // not sure what this is for
        //JPluginHelper::importPlugin('user');

        // if after the collation of response messages generated by plugin onUserAuthenticate() functions leaves STATUS_SUCCESS
        // - the STATUS_SUCCESS is set by onUserAuthenticate() in the functions
        // - STATUS_SUCCESS just measn there is a valid authentication method that can be used
        // - Valid plugins have been cycled through for responses (cookie || username and password)
        // -  this code is not used currently but i could use it to block users
        if ($response->status === Authentication::STATUS_SUCCESS)
        {
            /*
             * Validate that the user should be able to login (different to being authenticated).
             * This permits authentication plugins blocking the user.
             * 
             * This cycle through plugins responses (remember.php and qwcrm.php). Only acitve plugins send a response, these are set manully in QWcrm.      
             * and then executes their login failures routine (if any), and then returns an error message.
             * If there is no error the process continues to log the user on with onUserLogin() (or my manual version).
             * 
             */
            $authorisations = $this->authorise($response, $options);            

            foreach ($authorisations as $authorisation)
            {
                $denied_states = array(Authentication::STATUS_EXPIRED, Authentication::STATUS_DENIED);
                
                if(in_array($authorisation->status, $denied_states))
                {
                    ////// Trigger onUserAuthorisationFailure Event.
                    //$this->triggerEvent('onUserAuthorisationFailure', array((array) $authorisation));

                    // If silent is set, just return false.
                    if (isset($options['silent']) && $options['silent'])
                    {
                        return false;
                    }

                    // Return the error.
                    switch ($authorisation->status)
                    {
                        case Authentication::STATUS_EXPIRED:
                            //return JError::raiseWarning('102002', JText::_('JLIB_LOGIN_EXPIRED'));
                            return _gettext("Login Expired");
                            break;
                            
                        case Authentication::STATUS_DENIED:
                            //return JError::raiseWarning('102003', JText::_('JLIB_LOGIN_DENIED'));
                            return _gettext("Login Denied");
                            break;

                        default:
                            //return JError::raiseWarning('102004', JText::_('JLIB_LOGIN_AUTHORISATION'));
                            return _gettext("Username and password do not match or you do not have an account yet.");  // this is a guess
                            break;
                    }
                }
            }

            ////// OK, the credentials are authenticated and user is authorised.  Let's fire the onLogin event. (stored qwcrm.php and remember.php methods)
            //$results = $this->triggerEvent('onUserLogin', array((array) $response, $options));                     
            //$user['username'] = $response->username;
            //$user['fullname'] = $response->fullname;
            //$user['password_clear'] = $response->password_clear; // Causes undefined variable error     
            //$user['email'] = $response->email;            
            $results = array($this->PlgUserJoomla->onUserLogin(get_object_vars($response), $options));            
            
            /*
             * If any of the user plugins did not successfully complete the login routine
             * then the whole method fails.
             *
             * Any errors raised should be done in the plugin as this provides the ability
             * to provide much more information about why the routine may have failed.
             */
            $user = \QFactory::getUser();

            if ($response->type == 'Cookie')
            {
                $user->set('cookieLogin', true);
            }

            // if no failed authentication results (currently just PlgUserJoomla) set cookie
            if (in_array(false, $results, true) == false)
            {
                $options['user'] = $user;
                $options['responseType'] = $response->type;

                ////// The user is successfully logged in. Run the after login events  (stored qwcrm.php and remember.php methods)
                //$this->triggerEvent('onUserAfterLogin', array($options));                              
                $this->PlgAuthenticationCookie->onUserAfterLogin($options);   // Trigger Cookie operations for onUserAfterLogin  
            }

            return true;
        }

        ////// Trigger onUserLoginFailure Event.
        //$this->triggerEvent('onUserLoginFailure', array((array) $response));   (stored qwcrm.php and remember.php methods)

        // If silent is set, just return false.
        if (isset($options['silent']) && $options['silent'])
        {
            return false;
        }

        // If status is not success, any error will have been raised by the user plugin
        if ($response->status !== Authentication::STATUS_SUCCESS)
        {
            //JLog::add($response->error_message, JLog::WARNING, 'jerror');
        }

        return false;
    }

    /**
     * Logout authentication function.
     *
     * Passed the current user information to the onUserLogout event and reverts the current
     * session record back to 'anonymous' parameters.
     * If any of the authentication plugins did not successfully complete
     * the logout routine then the whole method fails. Any errors raised
     * should be done in the plugin as this provides the ability to give
     * much more information about why the routine may have failed.
     *
     * @param   integer  $userid   The user to load - Can be an integer or string - If string, it is converted to ID automatically
     * @param   array    $options  Array('clientid' => array of client id's)
     *
     * @return  boolean  True on success
     *
     * @since   3.2
     */
    public function logout($userid = null, $options = array())
    {
        // Get a user object from the JApplication.       
        $user = \QFactory::getUser($userid);       
        
        // Get config
        $config = \QFactory::getConfig();

        // Build the credentials array.        
        $parameters['username'] = $user->username;
        $parameters['id'] = $user->id;

        // Set clientid in the options array if it hasn't been set already and shared sessions are not enabled.
        if (!$config->get('shared_session', '0') && !isset($options['clientid']))
        {
            $options['clientid'] = \QFactory::getClientId();
        }

        // Import the user plugin group.
        //JPluginHelper::importPlugin('user');

        ////// OK, the credentials are built. Lets fire the onLogout event.
        //$results = $this->triggerEvent('onUserLogout', array($parameters, $options));   //(stored qwcrm.php and remember.php methods)
        $results = array(
                        $this->PlgSystemRemember->onUserLogout($parameters, $options),
                        $this->PlgUserJoomla->onUserLogout($parameters, $options)       
                        );        

        // Check if any of the plugins failed. If none did, success.
        if (!in_array(false, $results, true))
        {
            $options['username'] = $user->get('username');
            
            //////$this->triggerEvent('onUserAfterLogout', array($options));          // (stored qwcrm.php and remember.php methods)            
            $this->PlgUserJoomla->onUserLogout($parameters, $options);
            $this->PlgAuthenticationCookie->onUserAfterLogout($options);
            
            return true;
        }

        ////// Trigger onUserLoginFailure Event.
        //$this->triggerEvent('onUserLogoutFailure', array($parameters));         // (stored qwcrm.php and remember.php methods)
        
        return false;
    }
        
}