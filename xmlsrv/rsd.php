<?php
/**
 * RSD Really Simple Discoverability
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2013 by Francois Planque - {@link http://fplanque.com/}
 *
 * @todo fp> do we want to restrict API definitions to a specific blog with blogID="" ?
 *
 * @see http://archipelago.phrasewise.com/rsd
 * @see http://en.wikipedia.org/wiki/Really_Simple_Discovery
 * @see http://cyber.law.harvard.edu/blogs/gems/tech/rsd.html
 *
 * @package xmlsrv
 *
 * @version $Id: rsd.php 3328 2013-03-26 11:44:11Z yura $
 */
header('Content-type: text/xml; charset=UTF-8', true);

require_once dirname(__FILE__).'/../conf/_config.php';

require_once $inc_path.'_main.inc.php';

param( 'blog', 'integer', NULL );

echo '<?xml version="1.0" encoding="UTF-8"?'.'>';
?>
<rsd version="1.0" xmlns="http://archipelago.phrasewise.com/rsd">
	<service>
	  <engineName>b2evolution</engineName>
	  <engineLink>http://b2evolution.net/</engineLink>
	  <homePageLink><?php echo $baseurl; ?></homePageLink>
	  <apis>
	    <api name="WordPress" preferred="true" apiLink="<?php echo $xmlsrv_url; ?>xmlrpc.php"<?php
	    	if( !empty($blog) ) { echo ' blogID="'.$blog.'"'; }
	    	?>>
				<settings>
					<docs>https://codex.wordpress.org/XML-RPC_wp</docs>
				</settings>
			</api>
	    <api name="MetaWeblog" preferred="false" apiLink="<?php echo $xmlsrv_url; ?>xmlrpc.php"<?php
	    	if( !empty($blog) ) { echo ' blogID="'.$blog.'"'; }
	    	?>>
				<settings>
					<docs>http://b2evolution.net/man/metaweblog-api</docs>
				</settings>
			</api>
	    <api name="MovableType" preferred="false" apiLink="<?php echo $xmlsrv_url; ?>xmlrpc.php"<?php
	    	if( !empty($blog) ) { echo ' blogID="'.$blog.'"'; }
	    	?>>
				<settings>
					<docs>http://b2evolution.net/man/movabletype-api</docs>
				</settings>
			</api>
	    <api name="Blogger" preferred="false" apiLink="<?php echo $xmlsrv_url; ?>xmlrpc.php"<?php
	    	if( !empty($blog) ) { echo ' blogID="'.$blog.'"'; }
	    	?>>
				<settings>
					<docs>http://b2evolution.net/man/blogger-api</docs>
				</settings>
			</api>
	    <api name="b2" preferred="false" apiLink="<?php echo $xmlsrv_url; ?>xmlrpc.php"<?php
	    	if( !empty($blog) ) { echo ' blogID="'.$blog.'"'; }
	    	?>>
				<settings>
					<docs>http://b2evolution.net/man/b2-api</docs>
				</settings>
			</api>
	  </apis>
	</service>
</rsd>