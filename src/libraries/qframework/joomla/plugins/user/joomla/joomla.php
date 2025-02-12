<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.joomla
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Registry\Registry;

/**
 * Joomla User plugin
 *
 * @since  1.5
 */
class PlgUserJoomla //extends JPlugin
{
    /**
     * Application object
     *
     * @var    JApplicationCms
     * @since  3.2
     */
    //protected $app;

    /**
     * Database object
     *
     * @var    JDatabaseDriver
     * @since  3.2
     */
    protected $db;

    public function __construct()
    {
        $this->db = \QFactory::getDbo();
    }
    
    /**
     * Remove all sessions for the user name
     *
     * Method is called after user data is deleted from the database
     *
     * @param   array    $user     Holds the user data
     * @param   boolean  $success  True if user was successfully stored in the database
     * @param   string   $msg      Message
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function onUserAfterDelete($user, $success, $msg)
    {
        if (!$success)
        {
            return false;
        }

        $sql = "DELETE FROM ".PRFX."session WHERE userid = ". $this->db->qstr((int) $user['id']);

        try
        {
            $this->db->Execute($sql);
        }
        catch (JDatabaseExceptionExecuting $e)
        {
            return false;
        }
        /*
        $query = $this->db->getQuery(true)
            ->delete($this->db->quoteName('#__messages'))
            ->where($this->db->quoteName('user_id_from') . ' = ' . (int) $user['id']);

        try
        {
            $this->db->setQuery($query)->execute();
        }
        catch (JDatabaseExceptionExecuting $e)
        {
            return false;
        }
        */
        return true;
    }

    /** Not currently used in QWcrm
     * Utility method to act on a user after it has been saved.
     *
     * This method sends a registration email to new users created in the backend.
     *
     * @param   array    $user     Holds the new user data.
     * @param   boolean  $isnew    True if a new user is stored.
     * @param   boolean  $success  True if user was successfully stored in the database.
     * @param   string   $msg      Message.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onUserAfterSave($user, $isnew, $success, $msg)
    {
        $mail_to_user = $this->params->get('mail_to_user', 1);

        if (!$isnew || !$mail_to_user)
        {
            return;
        }

        // TODO: Suck in the frontend registration emails here as well. Job for a rainy day.
        // The method check here ensures that if running as a CLI Application we don't get any errors
        if (method_exists($this->app, 'isClient') && !$this->app->isClient('administrator'))
        {
            return;
        }

        // Check if we have a sensible from email address, if not bail out as mail would not be sent anyway
        if (strpos($this->app->get('mailfrom'), '@') === false)
        {
            $this->app->enqueueMessage(Text::_('JERROR_SENDING_EMAIL'), 'warning');

            return;
        }

        $lang = Factory::getLanguage();
        $defaultLocale = $lang->getTag();

        /**
         * Look for user language. Priority:
         *     1. User frontend language
         *     2. User backend language
         */
        $userParams = new Registry($user['params']);
        $userLocale = $userParams->get('language', $userParams->get('admin_language', $defaultLocale));

        if ($userLocale !== $defaultLocale)
        {
            $lang->setLanguage($userLocale);
        }

        $lang->load('plg_user_joomla', JPATH_ADMINISTRATOR);

        // Compute the mail subject.
        $emailSubject = Text::sprintf(
            'PLG_USER_JOOMLA_NEW_USER_EMAIL_SUBJECT',
            $user['name'],
            $this->app->get('sitename')
        );

        // Compute the mail body.
        $emailBody = Text::sprintf(
            'PLG_USER_JOOMLA_NEW_USER_EMAIL_BODY',
            $user['name'],
            $this->app->get('sitename'),
            Uri::root(),
            $user['username'],
            $user['password_clear']
        );

        $res = Factory::getMailer()->sendMail(
            $this->app->get('mailfrom'),
            $this->app->get('fromname'),
            $user['email'],
            $emailSubject,
            $emailBody
        );

        if ($res === false)
        {
            $this->app->enqueueMessage(Text::_('JERROR_SENDING_EMAIL'), 'warning');
        }

        // Set application language back to default if we changed it
        if ($userLocale !== $defaultLocale)
        {
            $lang->setLanguage($defaultLocale);
        }
    }

    /**
     * This method should handle any login logic and report back to the subject
     *
     * @param   array  $user     Holds the user data
     * @param   array  $options  Array holding options (remember, autoregister, group)
     *
     * @return  boolean  True on success
     *
     * @since   1.5
     */
    public function onUserLogin($user, $options = array())
    {
        $instance = $this->_getUser($user, $options);

        // If _getUser returned an error, then pass it back.
        if ($instance instanceof Exception)
        {
            return false;
        }

        // If the user is blocked, redirect with an error
        if ($instance->block == 1)
        {
            //$this->app->enqueueMessage(_gettext("Login denied! Your account has either been blocked or you have not activated it yet."), 'warning');

            return false;
        }

        /*
        // Authorise the user based on the group information - see authentication.php:351
        if (!isset($options['group']))
        {
            $options['group'] = 'USERS';
        }

        // Check the user can login.
        $result = $instance->authorise($options['action']);

        if (!$result)
        {
            $this->app->enqueueMessage(_gettext("Login denied! Your account has either been blocked or you have not activated it yet."), 'warning');            

            return false;
        }
        */
        
        // Mark the user as logged in
        $instance->guest = 0;

        $session = \QFactory::getSession();

        // Grab the current session ID
        $oldSessionId = $session->getId();

        // Fork the session - regenerates it
        $session->fork();

        // install the logged in user's object into the session
        $session->set('user', $instance);

        // Ensure the new session's metadata is written to the database
        //$this->app->checkSession();
        $session->checkSession();  

        // Purge the old session
        $sql = "DELETE FROM ".PRFX."session WHERE session_id = " . $this->db->qstr($oldSessionId);

        try
        {
            $this->db->Execute($sql);
        }
        catch (RuntimeException $e)
        {
            // The old session is already invalidated, don't let this block logging in
        }

        // Hit the user last visit field
        $instance->setLastVisit();

        // Add "user state" cookie used for reverse caching proxies like Varnish, Nginx etc.
        $config = \QFactory::getConfig();
        if (QFactory::isClient('site'))
        {
            $cookie = new \Joomla\CMS\Input\Cookie;
            $cookie->set(
                'qwcrm_user_state',
                'logged_in',
                0,
                $config->get('cookie_path', '/'),
                $config->get('cookie_domain', ''),
                \QFactory::isHttpsForced(),
                true
            );
        }

        return true;
    }

    /**
     * This method should handle any logout logic and report back to the subject
     *
     * @param   array  $user     Holds the user data.
     * @param   array  $options  Array holding options (client, ...).
     *
     * @return  boolean  True on success
     *
     * @since   1.5
     */
    public function onUserLogout($user, $options = array())
    {
        $my      = \QFactory::getUser();
        $session = \QFactory::getSession();
        $config  = \QFactory::getConfig();
        $cookie  = new \Joomla\CMS\Input\Cookie;

        // Make sure we're a valid user first
        if ($user['id'] == 0 && !$my->get('tmp_user'))
        {
            return true;
        }

        $sharedSessions = $config->get('shared_session', '0');

        // Check to see if we're deleting the current session
        if ($my->id == $user['id'] && ($sharedSessions || (!$sharedSessions && $options['clientid'] == \QFactory::getClientId())))
        {
            // Hit the user last visit field
            $my->setLastVisit();

            // Destroy the php session for this user
            $session->destroy();
        }

        // Enable / Disable Forcing logout all users with same userid - this setting is in the 'User - Joomla!' plugin, by default this is on.
        //$forceLogout = $this->params->get('forceLogout', 1);
        $forceLogout = 1;        

        if ($forceLogout)
        {
            $sql = "DELETE FROM ".PRFX."session WHERE userid = " . (int) $user['id'];

            if (!$sharedSessions)
            {
                //$query->where($this->db->quoteName('client_id') . ' = ' . (int) $options['clientid']);
                $sql .= "\nAND " . $this->db->qstr('clientid') . " = " . (int) $options['clientid'];
            }

            try
            {
                $this->db->Execute($sql);
            }
            catch (RuntimeException $e)
            {
                return false;
            }
        }

        // Delete "user state" cookie used for reverse caching proxies like Varnish, Nginx etc.
        if (QFactory::isClient('site'))
        {
            $cookie->set('qwcrm_user_state', '', 1, $config->get('cookie_path', '/'), $config->get('cookie_domain', ''));
        }

        return true;
    }

    /**
     * This method will return a user object
     *
     * If options['autoregister'] is true, if the user doesn't exist yet they will be created
     *
     * @param   array  $user     Holds the user data.
     * @param   array  $options  Array holding options (remember, autoregister, group).
     *
     * @return  User
     *
     * @since   1.5
     */
    protected function _getUser($user, $options = array())
    {
        $instance = \Joomla\CMS\User\User::getInstance();
        $id = (int) \Joomla\CMS\User\UserHelper::getUserId($user['username']);

        if ($id)
        {
            $instance->load($id);

            return $instance;
        }

        // TODO : move this out of the plugin
        //$params = ComponentHelper::getParams('com_users');

        // Read the default user group option from com_users
        //$defaultUserGroup = $params->get('new_usertype', $params->get('guest_usergroup', 1));

        $instance->id = 0;
        $instance->name = $user['fullname'];
        $instance->username = $user['username'];
        $instance->password_clear = $user['password_clear'];

        // Result should contain an email (check).
        $instance->email = $user['email'];
        //$instance->groups = array($defaultUserGroup);

        /*
        // If autoregister is set let's register the user
        $autoregister = isset($options['autoregister']) ? $options['autoregister'] : $this->params->get('autoregister', 1);

        if ($autoregister)
        {
            if (!$instance->save())
            {
                JLog::add('Error in autoregistration for user ' . $user['username'] . '.', JLog::WARNING, 'error');
            }
        }
        else
        {
            // No existing user and autoregister off, this is a temporary user.
            $instance->set('tmp_user', true);
        }
        */

        return $instance;
    }
}
