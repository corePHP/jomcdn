<?php
/**
 * This is the PHP Cloud Files API.
 *
 * See the included tests directory for additional sample code.
 *
 * Requres PHP 5.x (for Exceptions and OO syntax) and PHP's cURL module.
 *
 * It uses the supporting "cloudfiles_http.php" module for HTTP(s) support and
 * allows for connection re-use and streaming of content into/out of Cloud Files
 * via PHP's cURL module.
 *
 * See COPYING for license information.
 *
 * @author Eric "EJ" Johnson <ej@racklabs.com>
 * @copyright Copyright (c) 2008, Rackspace US, Inc.
 * @package php-cloudfiles
 */

/**
 */
require_once("cloudfiles_exceptions.php");
require("cloudfiles_http.php");
define("DEFAULT_CF_API_VERSION", 1);
define("MAX_CONTAINER_NAME_LEN", 256);
define("MAX_OBJECT_NAME_LEN", 1024);
define("MAX_OBJECT_SIZE", 5*1024*1024*1024+1); # bigger than S3! ;-)

/**
 * Class for handling Cloud Files Authentication, call it's {@link authenticate()}
 * method to obtain authorized service urls and an authentication token.
 *
 * Example:
 * <code>
 * # Create the authentication instance
 * #
 * $auth = new CF_Authentication("username", "api_key");
 *
 * # NOTE: Some versions of cURL include an outdated certificate authority (CA)
 * #       file.  This API ships with a newer version obtained directly from
 * #       cURL's web site (http://curl.haxx.se).  To use the newer CA bundle,
 * #       call the CF_Authentication instance's 'ssl_use_cabundle()' method.
 * #
 * # $auth->ssl_use_cabundle(); # bypass cURL's old CA bundle
 *
 * # Perform authentication request
 * #
 * $auth->authenticate();
 * </code>
 *
 * @package php-cloudfiles
 */
require 'vendor/autoload.php';
use OpenCloud\Rackspace;
class CF_Authentication

{

    public $dbug;
    public $username;
    public $api_key;
    public $auth_host;
    public $account;
    public $region;

    /**
     * Class constructor (PHP 5 syntax)
     *
     * @param string $username Mosso username
     * @param string $api_key Mosso API Access Key
     * @param string $region rackspace account region
     * @param string $account  <i>Account US = 0, UK =1</i>
     * @param string $auth_host  <i>Authentication service URI</i>
     */

    function __construct($username=NULL, $api_key=NULL,  $region= 'ORD', $account=NULL, $auth_host=NULL)
    {
        $this->dbug = False;
        $this->username = $username;
        $this->api_key = $api_key;
        $this->region = $region;
        $this->account_name = $account;
    }

    function putObject($absolute_path,$container_name,$file_name, $input, $metaHeaders = array(), $requestHeaders = array())
    {
        // Instantiate a Rackspace client.
        if($this->account_name)
        {
            $client = new Rackspace('https://lon.identity.api.rackspacecloud.com/v2.0/'
            , array(
                'username' => $this->username,
                'apiKey'   => $this->api_key
            ));
        }
        else
        {
            $client = new Rackspace('https://identity.api.rackspacecloud.com/v2.0/'
            , array(
                'username' => $this->username,
                'apiKey'   => $this->api_key
            ));
        }


        if ($container_name != "0" and !isset($container_name))
            throw new SyntaxException("Container name not set.");


        if (!isset($container_name) or $container_name == "")
            throw new SyntaxException("Container name not set.");



        if (strpos($container_name, "/") !== False) {
            $r = "Container name '".$container_name;
            $r .= "' cannot contain a '/' character.";
            throw new SyntaxException($r);
        }

        if (strlen($container_name) > MAX_CONTAINER_NAME_LEN) {
            throw new SyntaxException(sprintf(
                "Container name exeeds %d bytes.",
                MAX_CONTAINER_NAME_LEN));
        }

        // Obtain an Object Store service object from the client.
        $objectStoreService = $client->objectStoreService(null, $this->region);

        // Create a container for your objects (also referred to as files).
        $container = $objectStoreService->createContainer($container_name);

        $container = $objectStoreService->getContainer($container_name);


        $container->enableCdn();

        // Upload an object to the container.
        $localFileName  = $absolute_path;
        $remoteFileName = $file_name;

        $handle = fopen($localFileName, 'r');
        $object = $container->uploadObject($remoteFileName, $handle);

        return $cdnUrl = $object->getPublicUrl();
    }

}

?>
