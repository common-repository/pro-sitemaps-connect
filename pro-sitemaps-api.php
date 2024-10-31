<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PRO Sitemaps Connect
 * PRO Sitemaps API handler
 *
 * @package           PRO_Sitemaps_Connect
 * @author            PRO Sitemaps
 * @copyright         2024 PRO Sitemaps
 * @license           GPL-2.0-or-later
 *
 *
 */

/**
 * WP API handler class
 *
 */
class Pro_Sitemaps_Connect_WPAPI
{
    protected $ps_api_version = '20230928';
    // protected $ps_api_endpoint = 'https://pro-sitemaps.jam/api/';
    protected $ps_api_endpoint = 'https://pro-sitemaps.com/api/';

    protected $ps_helper = null;

    /**
     * constructor. setting up hooks.
     * @param [string] $plugin_base [plugin's root file location]
     * @param [object] $_pshelper Plugin Helper instance
     */
    public function __construct($plugin_base, $_pshelper)
    {
        $this->ps_helper = $_pshelper;
        if ($this->ps_helper->_get_option('ps_robots_txt'))
        {
            add_filter('robots_txt', [$this, 'do_robots_txt'], 90, 2);
        }
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('init', [$this, 'add_rewrite_rules']);
        add_action('parse_query', [$this, 'parse_request_query']);
        if ($this->ps_helper->_get_option('ps_update_sitemap'))
        {
        	add_action('transition_post_status', [$this, 'check_new_post'], 10, 3 );
    	}
    }

    /**
     * get_sitemap_info - retrieve sitemap details
     * @return [array] sitemap details according with PRO Sitemaps API
     */
    public function get_sitemap_info()
    {
        $api_response = $this->do_api_request('get_sitemap');
        if (!isset($api_response['error']))
        {
            $_result = $api_response['api_body']['result'];
            $api_response['sitemap_list'] = $_result['top_sitemap_info'];
        }
        return $api_response;
    }

    /**
     * serve_sitemap - get sitemap contents via API and send it to the client
     * @return
     */
    public function serve_sitemap($sitemap_name)
    {
        $api_response = $this->do_api_request('download_sitemap', ['sitemap_id' => $sitemap_name]);
        $error_result = '';
        if (isset($api_response['error']))
        {
        	$error_result = $api_response['error'];
        }
        else {
        	$content_type = $api_response['headers']['content-type'];
        	if(strstr($content_type, 'json'))
        	{
        		$error_result = 'API Error. '.$api_response['api_body']['result_desc'];
        	}
        }

        if ($error_result)
        {
	        $status_message = '503 Service Temporarily Unavailable';
	        wp_die($status_message.': '. $error_result, '', 503);
        }else {
            $xml_content = $api_response['body'];
            if(strstr($content_type, '/xml'))
            {
                header('Content-Type: application/xml');
                header('Content-Length: ' . strlen($xml_content));
                echo $xml_content;

                exit;

            }else
            {
                wp_die($xml_content.': '. $error_result, '', 503);
            }
        }
    }

    /**
     * do_api_request - send an API request to PRO Sitemaps and return its response
     * @param $method API method
     * @return [array] API response
     */
    public function do_api_request($method, $parameters = [])
    {
        if (!$this->ps_helper->has_api_info())
        {
            return [
                'error' => __('PRO Sitemaps API settings not defined', 'pro-sitemaps-connect'),
            ];
        }
        $api_args = [
            'method' => $method,
            'api_ver' => $this->ps_api_version,
            'api_key' => $this->ps_helper->_get_option('ps_apikey'),
            'site_id' => $this->ps_helper->_get_option('ps_siteid'),
            'sitemap_slug' => $this->ps_helper->_get_option('ps_sitemap_name'),
            'sitemap_self_path' => $this->ps_helper->get_sitemap_local_path(),
        ];
        $api_args = array_merge($api_args, $parameters);

        $server_args = [
            'if_mod_since' => 'HTTP_IF_MODIFIED_SINCE',
            'remote_ip' => 'REMOTE_ADDR',
            'remote_user_agent' => 'HTTP_USER_AGENT',
        ];
        foreach($server_args as $_sk => $_sv)
        	if(isset($_SERVER[$_sv]))
        		$api_args[$_sk] = sanitize_text_field($_SERVER[$_sv]);
        $return = [
            'request_url' => $this->ps_api_endpoint,
            'request_body' => $api_args,
        ];

        $wp_response = wp_remote_post($this->ps_api_endpoint, ['body' => $api_args]);
        if (is_wp_error($wp_response))
        {
            $return['error'] = $wp_response->get_error_message();
        }
        else
        {
            $_errors = [];
            $return = array_merge($return, $wp_response);
            if ($wp_response['response']['code'] != '200')
            {
                $_errors[] = __('Error ', 'pro-sitemaps-connect') . $wp_response['response']['code'];
            }
            $http_headers = $wp_response['headers'];
            $api_body = $wp_response['body'];

            if ($http_headers && strstr($http_headers['content-type'], 'json'))
            {
                $api_body = json_decode($api_body, true);
                if (!$api_body['api_success'])
                $_errors[] = $api_body['result_desc'];
            }
            if ($_errors)
            $return['error'] = implode('. ', $_errors);

            $return['api_body'] = $api_body;
        }
        return $return;

    }

    /**
     * do_robots_txt - update robots.txt with sitemap links
     * @param $output - current robots.txt file content
     * @param $public - if the blog is open to public
     *
     * @return $output
     */
    public function do_robots_txt($output, $public)
    {
        if ($public != '0')
        {
            $output .= $this->get_robots_txt_entries();
        }
        return $output;
    }

    /**
     * get_robots_txt_entries - return suggested robots.txt lines
     * @return [string]
     */
    public function get_robots_txt_entries()
    {
    	$output = '';
        $sitemap_list = $this->ps_helper->_get_option('sitemap_list');
        foreach ($sitemap_list as $_sminfo)
        {
            if ($_sminfo['se_submit'] && ($_sminfo['elements_count'] > 0))
            {
                $output .= "\nsitemap: " . $_sminfo['client_url'];
            }
        }
        return $output;
    }

    /**
     * add_rewrite_rules - setup rewrite rules to handle sitemaps
     *
     * @return
     */
    public function add_rewrite_rules()
    {
        $sitemap_slug = str_replace('.xml', '', $this->ps_helper->_get_option('ps_sitemap_name'));
        if ($sitemap_slug)
        {
            add_rewrite_rule(
                '^(' . $sitemap_slug . '[^/]*\.(xml|txt|html|xsl))',
                'index.php?prositemaps-get=$matches[1]',
                'top'
            );
        }

    }

    /**
     * check_new_post - update sitemap when new post is created
     *
     * @return
     */
    public function check_new_post($new_status, $old_status, $post)
    {
    	if ( $new_status == 'publish' && $old_status != 'publish' ) {
    		$api_response = $this->do_api_request('update_sitemap');
    	}


    }


    /**
     * parse_request_query - handle sitemap file requests
     *
     * @return
     */
    public function parse_request_query()
    {
    	if( $ps_requested = get_query_var( 'prositemaps-get' ) ){
    		$this->serve_sitemap($ps_requested);

        }

    }


    /**
     * add_query_vars - add custom rewrite query var
     *
     * @param [array] $queryvars
     *
     *
     * @return [array] $queryvars
     */
    public function add_query_vars($queryvars)
    {
    	$queryvars[] = 'prositemaps-get';
    	return $queryvars;

    }

}
