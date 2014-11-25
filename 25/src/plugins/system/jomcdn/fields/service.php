<?php
/**
 * @version		$Id: service.php 1 2009-10-27 20:56:04Z rafael $
 * @package		jomCDN
 * @copyright	Copyright (C) 2010 'corePHP' / corephp.com. All rights reserved.
 * @license		GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die( 'Restricted access' );
/**
 * Renders a jomcdn Form
 *
 * @package		jomCDN
 */
class JFormFieldservice extends JFormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */

	public $type = 'service';
	protected function getInput()
	{
		if($this->value=="")
		{
			$this->value = "s3";
		}
		$session = JFactory::getSession();
		$session->set('serviceType', $this->value);
		$serviceType = $session->get('serviceType');
		$version = new JVersion();
		
		?>
		<script>
			window.onload=function()
			{
				var service = '<?php echo $serviceType;?>';
				var jversion = '<?php echo $version->RELEASE;?>';
				getServiceParameters(service, jversion);
			}
		</script>

		<?php

		$service = array();
		$service[]   = JHTML::_('select.option', 's3', 'Amazon S3 with CloudFront');
		$service[]   = JHTML::_('select.option', 'rs', 'Rackspace Cloud Files');
		$service[]   = JHTML::_('select.option', 'maxcdn', 'MaxCDN');

		return JHTML::_('select.genericlist',  $service, $this->name, 'class="inputbox" onChange="getServiceParameters(this.value);"', 'value', 'text', $this->value, $this->id);
	}
}
?>

<script>

function getServiceParameters(servicetype, jversion)
{
	if(servicetype == 'rs')
	{

		document.getElementById('jform_params_s3_access_key-lbl').style.display='none';
		document.getElementById('jform_params_s3_access_key').style.display='none';
		document.getElementById('jform_params_s3_secret_key-lbl').style.display='none';
		document.getElementById('jform_params_s3_secret_key').style.display='none';
		document.getElementById('jform_params_s3_use_ssl-lbl').style.display='none';
		document.getElementById('jform_params_s3_use_ssl').style.display='none';
		document.getElementById('jform_params_s3_bucket-lbl').style.display='none';
		document.getElementById('jform_params_s3_bucket').style.display='none';
		document.getElementById('jform_params_s3_cloudfront_domain-lbl').style.display='none';
		document.getElementById('jform_params_s3_cloudfront_domain').style.display='none';
		document.getElementById('jform_params_rs_api_key-lbl').style.display='block';
		document.getElementById('jform_params_rs_api_key').style.display='block';
		document.getElementById('jform_params_rs_username-lbl').style.display='block';
		document.getElementById('jform_params_rs_username').style.display='block';
		document.getElementById('jform_params_rs_bucket-lbl').style.display='block';
		document.getElementById('jform_params_rs_bucket').style.display='block';
		document.getElementById('jform_params_rs_account_is_uk-lbl').style.display='block';
		document.getElementById('jform_params_rs_account_is_uk').style.display='block';
		document.getElementById('jform_params_root-lbl').style.display='none';
		document.getElementById('jform_params_root').style.display='none';
		document.getElementById('jform_params_cdn-lbl').style.display='none';
		document.getElementById('jform_params_cdn').style.display='none';
		document.getElementById('jform_params_filetypes-lbl').style.display='none';
		document.getElementById('jform_params_filetypes').style.display='none';
		document.getElementById('jform_params_extratypes-lbl').style.display='none';
		document.getElementById('jform_params_extratypes').style.display='none';
		document.getElementById('jform_params_ignorefiles-lbl').style.display='none';
		document.getElementById('jform_params_ignorefiles').style.display='none';
		document.getElementById('jform_params_enable_in_scripts-lbl').style.display='none';
		document.getElementById('jform_params_enable_in_scripts').style.display='none';
		document.getElementById('advanced-options').style.display='block';

	}

	if(servicetype == 's3')
	{
		document.getElementById('jform_params_s3_access_key-lbl').style.display='block';
		document.getElementById('jform_params_s3_access_key').style.display='block';
		document.getElementById('jform_params_s3_secret_key-lbl').style.display='block';
		document.getElementById('jform_params_s3_secret_key').style.display='block';
		document.getElementById('jform_params_s3_use_ssl-lbl').style.display='block';
		document.getElementById('jform_params_s3_use_ssl').style.display='block';
		document.getElementById('jform_params_s3_bucket-lbl').style.display='block';
		document.getElementById('jform_params_s3_bucket').style.display='block';
		document.getElementById('jform_params_s3_cloudfront_domain-lbl').style.display='block';
		document.getElementById('jform_params_s3_cloudfront_domain').style.display='block';
		document.getElementById('advanced-options').style.display='block';
		document.getElementById('jform_params_rs_api_key-lbl').style.display='none';
		document.getElementById('jform_params_rs_api_key').style.display='none';
		document.getElementById('jform_params_rs_username-lbl').style.display='none';
		document.getElementById('jform_params_rs_username').style.display='none';
		document.getElementById('jform_params_rs_bucket-lbl').style.display='none';
		document.getElementById('jform_params_rs_bucket').style.display='none';
		document.getElementById('jform_params_rs_account_is_uk-lbl').style.display='none';
		document.getElementById('jform_params_rs_account_is_uk').style.display='none';
		document.getElementById('jform_params_root-lbl').style.display='none';
		document.getElementById('jform_params_root').style.display='none';
		document.getElementById('jform_params_cdn-lbl').style.display='none';
		document.getElementById('jform_params_cdn').style.display='none';
		document.getElementById('jform_params_filetypes-lbl').style.display='none';
		document.getElementById('jform_params_filetypes').style.display='none';
		document.getElementById('jform_params_extratypes-lbl').style.display='none';
		document.getElementById('jform_params_extratypes').style.display='none';
		document.getElementById('jform_params_ignorefiles-lbl').style.display='none';
		document.getElementById('jform_params_ignorefiles').style.display='none';
		document.getElementById('jform_params_enable_in_scripts-lbl').style.display='none';
		document.getElementById('jform_params_enable_in_scripts').style.display='none';
	}

	if(servicetype == 'maxcdn')
	{
		document.getElementById('jform_params_s3_access_key-lbl').style.display='none';
		document.getElementById('jform_params_s3_access_key').style.display='none';
		document.getElementById('jform_params_s3_secret_key-lbl').style.display='none';
		document.getElementById('jform_params_s3_secret_key').style.display='none';
		document.getElementById('jform_params_s3_use_ssl-lbl').style.display='none';
		document.getElementById('jform_params_s3_use_ssl').style.display='none';
		document.getElementById('jform_params_s3_bucket-lbl').style.display='none';
		document.getElementById('jform_params_s3_bucket').style.display='none';
		document.getElementById('jform_params_s3_cloudfront_domain-lbl').style.display='none';
		document.getElementById('jform_params_s3_cloudfront_domain').style.display='none';
		document.getElementById('jform_params_rs_api_key-lbl').style.display='none';
		document.getElementById('jform_params_rs_api_key').style.display='none';
		document.getElementById('jform_params_rs_username-lbl').style.display='none';
		document.getElementById('jform_params_rs_username').style.display='none';
		document.getElementById('jform_params_rs_bucket-lbl').style.display='none';
		document.getElementById('jform_params_rs_bucket').style.display='none';
		document.getElementById('jform_params_rs_account_is_uk-lbl').style.display='none';
		document.getElementById('jform_params_rs_account_is_uk').style.display='none';
		document.getElementById('advanced-options').style.display='none';

		document.getElementById('jform_params_root-lbl').style.display='block';
		document.getElementById('jform_params_root').style.display='block';
		document.getElementById('jform_params_cdn-lbl').style.display='block';
		document.getElementById('jform_params_cdn').style.display='block';
		document.getElementById('jform_params_filetypes-lbl').style.display='block';
		document.getElementById('jform_params_filetypes').style.display='block';
		document.getElementById('jform_params_extratypes-lbl').style.display='block';
		document.getElementById('jform_params_extratypes').style.display='block';
		document.getElementById('jform_params_ignorefiles-lbl').style.display='block';
		document.getElementById('jform_params_ignorefiles').style.display='block';
		document.getElementById('jform_params_enable_in_scripts-lbl').style.display='block';
		document.getElementById('jform_params_enable_in_scripts').style.display='block';
	}
}

</script>