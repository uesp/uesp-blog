<?php
/**
 * This file implements the reCAPTCHA plugin for {@link http://b2evolution.net/}.
 *
 * @copyright (c)2009 by Cary Mathews - {@link http://epapyr.us/2009/01/recaptcha}.
 *
 * @license GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *
 * @package plugins
 *
 * @author Cary Mathews
 *
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


/**
 * PLUGIN_NAME reCAPTCHA
 *
 * http://recaptcha.net
 * http://epapyr.us/2009/01/recaptcha
 *
 * @package plugins
 */
class recaptcha_plugin extends Plugin
{
	/**
	 * Variables below MUST be overriden by plugin implementations,
	 * either in the subclass declaration or in the subclass constructor.
	 */
	var $name = 'reCAPTCHA';
	/**
	 * Code, if this is a renderer or pingback plugin.
	 */
	var $code = 'recaptcha';
	var $priority = 10;
	var $version = '1.0';
	var $author = 'Cary Mathews';
	var $help_url = 'http://epapyr.us/2009/01/recaptcha';
	var $number_of_installs = 1;

	var $apply_rendering = 'never';
	var $group = 'antispam';


	function PluginVersionChanged( & $params )
	{
		if ( $params['old_version'] != $this->version )
		{
			return true;
		} 
		else
		{
			return false;
		}
	}

	/**
	 * Init
	 *
	 * This gets called after a plugin has been registered/instantiated.
	 */
	function PluginInit( & $params )
	{
		// may need to move to GetDependencies()
		require_once('recaptcha-php-1.10/recaptchalib.php');

		$this->short_desc = $this->T_('b2evolution plugin of reCAPTCHA project');
		$this->long_desc = $this->T_('reCAPTCHA (http://recaptcha.net) is a program which combines antispam measures and digitizing books. It is sponsored by Carnegie Mellon University. (The author of this plugin is not associated with CMU or the reCAPTCHA project directly.)');

	}

	/**
	 * Define settings that the plugin uses/provides.
	 */
	function GetDefaultSettings()
	{
		return array(

			'pub_key' => array(
				'label' => $this->T_('Public Key'),
				'type' => 'text',
				'size' => 40,
				'maxlength' => 50,
				'note' => $this->T_('Your reCAPTCHA public key you recieved when you signed up'),
			),

			'priv_key' => array(
				'label' => $this->T_('Private Key'),
				'type' => 'text',
				'size' => 40,
				'maxlength' => 50,
				'note' => $this->T_('Your reCAPTCHA private key you recieved when you signed up'),
			),

			'req_to_reg' => array (
				'label' => $this->T_('Registration?'),
				'type' => 'checkbox',
				'defaultvalue' => 1,
				'note' => $this->T_('Check to enable reCAPTCHA on registration forms.'),
			),

			'req_to_comment' => array (
				'label' => $this->T_('Comment?'),
				'type' => 'checkbox',
				'defaultvalue' => 1,
				'note' => $this->T_('Check to enable reCAPTCHA on comment forms.'),
			),

			'req_to_msg' => array (
				'label' => $this->T_('Email?'),
				'type' => 'checkbox',
				'defaultvalue' => 1,
				'note' => $this->T_('Check to enable reCAPTCHA on email message forms.'),
			),

			'reg_user_pass' => array (
				'label' => $this->T_('Exempt Users?'),
				'type' => 'checkbox',
				'defaultvalue' => 1,
				'note' => $this->T_('Check to let registered users submit comments and messages without a captcha.'),
			),

			'use_ssl' => array(
				'label' => $this->T_('Enable SSL'),
				'type' => 'checkbox',
				'defaultvalue' => 0,
				'note' => $this->T_('Check to enable reCAPTCHA to query over SSL; only use if you\'re already using SSL'),
			),

			'rC_apply_theme' => array(
				'label' => $this->T_('Apply Theme?'),
				'type' => 'checkbox',
				'defaultvalue' => 0,
				'note' => $this->T_('Check to apply theme and tabindex changes below.'),
			),

			'rC_theme' => array(
				'label' => $this->T_('Theme'),
				'type' => 'select',
				'options' => array (
					'red' => $this->T_('Red'),
					'white' => $this->T_('White'),
					'blackglass' => $this->T_('Blackglass'),
					'clean' => $this->T_('Clean')
				),
				'defaultvalue' => 'red',
			),

			'rC_tab_index' => array(
				'label' => $this->T_('Tab Index'),
				'type' => 'integer',
				'defaultvalue' => 0,
				'size' => 3,
			),

			'rC_lang' => array(
				'label' => $this->T_('Language'),
				'type' => 'select',
				'options' => array (
					'en' => $this->T_('English'),
					'nl' => $this->T_('Dutch'),
					'fr' => $this->T_('French'),
					'de' => $this->T_('German'),
					'pt' => $this->T_('Portuguese'),
					'ru' => $this->T_('Russian'),
					'es' => $this->T_('Spanish'),
					'tr' => $this->T_('Turkish')
				),
				'defaultvalue' => 'en',
			),

		);
	}

	/**
	 * Establish CSS paths...
	 */
	function SkinBeginHtmlHead() 
	{
		require_css( $this->get_plugin_url().'recaptcha.css', true, 'reCAPTCHA' );
	}

	/**
	 * Define user settings that the plugin uses/provides.
	 */
	function GetDefaultUserSettings()
	{
		return array(

			);
	}


	// If you use hooks, that are not present in b2evo 1.8, you should also add
	// a GetDependencies() function and require the b2evo version your Plugin needs.
	// See http://doc.b2evolution.net/stable/plugins/Plugin.html#methodGetDependencies


	// Add the methods to hook into here...
	// See http://doc.b2evolution.net/stable/plugins/Plugin.html

	function DisplayCommentFormFieldset( & $params, $captcha_err = null )
	{
		// no need to generate a form if we're going to exempt them
		if ( $this->Settings->get( 'req_to_comment' ) && ! (is_logged_in() && $this->Settings->get( 'reg_user_pass' ) ) )
		{
			$Form = & $params['Form'];
			if (! isset($params['form_use_fieldset']) || $params['form_use_fieldset'] ) 
			{
				$Form->begin_fieldset();
			}

			// do generate reCAPTCHA
			echo $this->_recaptcha_plugin_gen_captcha( $captcha_err );

			if (! isset($params['form_use_fieldset']) || $params['form_use_fieldset'] ) 
			{
				$Form->end_fieldset();
			}
		}

	}

	function BeforeCommentFormInsert( &$params )
	{
		if ( ! empty($params['is_preview']) )
		{
			return;
		}
		if ( ! (is_logged_in() && $this->Settings->get( 'reg_user_pass' ) ) )  
		{
			$resp = $this->_recaptcha_plugin_response();

			if ( ! $resp->is_valid ) 
			{
				$this->_recpatcha_plugin_error_msg();
				// Return to form here...
			}
		}
	}

	function DisplayMessageFormFieldset( &$params )
	{
		// skip form if logged in...
		if ( $this->Settings->get( 'req_to_msg' ) && ! (is_logged_in() && $this->Settings->get( 'reg_user_pass' ) ) )
		{
			$Form = & $params['Form'];
			if (! isset($params['form_use_fieldset']) || $params['form_use_fieldset'] ) 
			{
				$Form->begin_fieldset();
			}

			// do generate reCAPTCHA
			echo $this->_recaptcha_plugin_gen_captcha();


			if (! isset($params['form_use_fieldset']) || $params['form_use_fieldset'] ) 
			{
				$Form->end_fieldset();
			}
		}
	}

	function DisplayRegisterFormFieldset ( &$params ) 
	{
		if ( $this->Settings->get( 'req_to_reg' ) )
		{
			$Form = & $params['Form'];
			if (! isset($params['form_use_fieldset']) || $params['form_use_fieldset'] ) 
			{
				$Form->begin_fieldset();
			}


			echo $this->_recaptcha_plugin_gen_captcha ();

			if (! isset($params['form_use_fieldset']) || $params['form_use_fieldset'] ) 
			{
				$Form->end_fieldset();
	
			}
		}
	}

	function RegisterFormSent ( &$parms )
	{
		$resp = $this->_recaptcha_plugin_response();

		if ( ! $resp->is_valid ) 
		{
			$this->_recaptcha_plugin_error_msg();	
		}
	
	}

	function _recaptcha_plugin_gen_captcha ( $reC_error = null )
	{
		// recaptcha_get_html ( $pubkey, $use_ssl [, $error])
		$captcha_code = '';
		if ( $this->Settings->get( 'rC_apply_theme' ) )
		{
			$captcha_code .= '<script type="text/javascript">';
			$captcha_code .= "var RecaptchaOptions = {";
			$captcha_code .= "   theme : '" . $this->Settings->get( 'rC_theme' )."',";
			$captcha_code .= "   tabindex : " . $this->Settings->get( 'rC_tab_index' ).",";
			$captcha_code .= "   lang : '" . $this->Settings->get( 'rC_lang' )."'";
			$captcha_code .= "};</script>\n";
		}

		$captcha_code .= recaptcha_get_html( $pubkey = $this->Settings->get( 'pub_key' ), $error = $reC_error, $use_ssl = $this->Settings->get( 'use_ssl' ) );

		return $captcha_code;

	}

	function _recaptcha_plugin_response () 
	{
		// recaptcha_challenge_field and recaptcha_response_field.
		$reC_challenge = isset($_POST['recaptcha_challenge_field']) ? $_POST['recaptcha_challenge_field'] : '';
		$reC_response = isset($_POST['recaptcha_response_field']) ? $_POST['recaptcha_response_field'] : '';
		if ($reC_challenge == '' || $reC_response == '')
		{
			print T_('error in _challenge and/or _response');
		}
		$reCr = recaptcha_check_answer($privkey = $this->Settings->get( 'priv_key' ), $remoteip = $_SERVER['REMOTE_ADDR'], $challenge = $reC_challenge, $response = $reC_response);

		return $reCr;
	}

	function _recpatcha_plugin_error_msg ()
	{
		$this->msg( $msg = "You provided an incorrect or invalid response to reCAPTCHA, please try again", $category = 'error' );
	}
		


} // end Plugin class
?>
