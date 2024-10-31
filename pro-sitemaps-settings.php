<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PRO Sitemaps Connect
 * Wordpress settings handler class
 *
 * @package           PRO_Sitemaps_Connect
 * @author            PRO Sitemaps
 * @copyright         2024 PRO Sitemaps
 * @license           GPL-2.0-or-later
 *
 *
 */

/**
 * WP Settings handler class
 *
 */
class Pro_Sitemaps_Connect_WPSettings
{

    /**
     *
     */
    protected $ps_helper = null;
    protected $ps_api = null;

    /**
     * constructor. setting up hooks.
     * @param [string] $plugin_base [plugin's root file location]
     * @param [object] $_pshelper Plugin Helper instance
     * @param [object] $_psapi Plugin API interface
     */
    public function __construct($plugin_base, $_pshelper, $_psapi)
    {
        $this->ps_helper = $_pshelper;
        $this->ps_api = $_psapi;
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_options_page']);
        register_activation_hook($plugin_base, [$this, 'on_activate']);
        register_uninstall_hook($plugin_base, [$this, 'on_uninstall']);
    }

    /**
     * activation_hook
     * @return
     */
    public function on_activate()
    {

    }

    /**
     * admin init hook. add default settings.
     * @return
     */
    public function register_settings()
    {
        /**
         * default plugin's options list
         * used for register_option / add_option
         */
         $ps_options_desc = [
            'ps_siteid' => [
                'type' => 'number',
                'title' => __('PRO Sitemaps Site ID', 'pro-sitemaps-connect'),
                'description' => __('Enter the Site ID of your PRO Sitemaps account', 'pro-sitemaps-connect'),
            ],
            'ps_apikey' => [
                'type' => 'text',
                'size' => '50',
                'title' => __('PRO Sitemaps API Key', 'pro-sitemaps-connect'),
                'description' => 'Enter the API Key displayed in your PRO Sitemaps account',
            ],
            'ps_sitemap_name' => [
                'type' => 'text',
                'title' => __('Sitemap name', 'pro-sitemaps-connect'),
                'description' => __('Customize the URL of your sitemap', 'pro-sitemaps-connect'),
            ],
            'ps_robots_txt' => [
                'type' => 'checkbox',
                'title' => __('Update robots.txt', 'pro-sitemaps-connect'),
                'description' => __('Add sitemap entry to your website\'s robots.txt file', 'pro-sitemaps-connect'),
            ],
            'ps_update_sitemap' => [
                'type' => 'checkbox',
                'title' => __('Sitemap Auto update', 'pro-sitemaps-connect'),
                'description' => __('Send an API request to update sitemap every time a new post is created', 'pro-sitemaps-connect'),
            ],
        ];
        register_setting('prositemaps_group', 'pro_sitemaps_connect_options');
        add_option('pro_sitemaps_connect_options', $this->ps_helper->default_options());

        add_settings_section('prositemaps_settings_general',
            __('PRO Sitemaps API Configuration', 'pro-sitemaps-connect'),
            [$this, 'display_settings_section'],
            'prositemaps_settings'
        );
        foreach ($ps_options_desc as $_optname => $_oarr)
        {
            $_oarr['label_for'] = $_optname;
            add_settings_field('pro_sitemaps_connect_options[' . $_optname . ']',
                $_oarr['title'],
                [$this, 'display_setting_field'],
                'prositemaps_settings',
                'prositemaps_settings_general',
                $_oarr
            );
        }
        add_filter('plugin_action_links_pro-sitemaps/pro-sitemaps.php', [$this, 'plugin_links']);
    }

    /**
     * plugin_action_links - add link to the settings page
     * @return
     */
    public function plugin_links($links)
    {
        $url = get_admin_url() . "options-general.php?page=prositemaps_settings";
        $links[] = '<a href="' . $url . '">' . __('Settings', 'pro-sitemaps-connect') . '</a>';
        return $links;
    }
    /**
     * add_options_page - add menu item with the link to a new settings page
     * @return
     */
    public function add_options_page()
    {
        add_options_page(
            __('PRO Sitemaps Plugin Settings', 'pro-sitemaps-connect'),
            __('PRO Sitemaps', 'pro-sitemaps-connect'),
            'manage_options',
            'prositemaps_settings',
            [$this, 'display_settings_page']
        );
    }

    /**
     * uninstall_hook - remove plugin's settings on uninstall
     * @return
     */
    public function on_uninstall()
    {
        delete_option('pro_sitemaps_connect_options');

    }

    /**
     * display_settings_page - show the settings form
     * @return
     */
    public function display_settings_page()
    {
        if (!current_user_can('manage_options'))
        {
            return;
        }
        if (isset($_GET['settings-updated']))
        {
            // check custom sitemap name (must have the ".xml" extension)
            $_sitemapname = $this->ps_helper->_get_option('ps_sitemap_name');
            if (substr($_sitemapname, -4) !== '.xml')
            {
                $this->ps_helper->_update_option('ps_sitemap_name', $_sitemapname . '.xml');
            }
        	$this->ps_api->add_rewrite_rules();
            flush_rewrite_rules();
        }

        settings_errors('ps_messages');
        echo '<script type="text/javascript">'.
        	'function ps_formchange(){'.
        	'jQuery(\'#ps_slug_id\').text(jQuery(\'#ps_sitemap_name\').val());'.
        	'return false;'.
        	'}'.
        	'</script>';
        echo '<style type="text/css">'.
        	'.ps_postbox {padding: 8px;}.ps_wrap,.ps_wrap p {font-size: 14px}'.
        	'.ps_toggle {border: 1px dashed; padding: 6px}'.
        	'.ps_wrap img {max-width: 100%}'.
        	'.ps_smtable td {padding: 4px} .ps_wrap .ps_debug{width:100%}'.
        	'</style>';
        echo '<div class="wrap ps_wrap">
				<h1>' . esc_html(get_admin_page_title()) . '</h1>
				';
        if ($this->ps_helper->has_api_info())
        {
        	$this->test_api_connection();
        }
        echo '<form action="options.php" method="post" onkeyup="return ps_formchange()" onchange="return ps_formchange()">';
        settings_fields('prositemaps_group');
        do_settings_sections('prositemaps_settings');
        submit_button('Save Settings');
        echo '</form></div>';
    }

    /**
     * display_settings_section - show the settings section info
     * @return
     */
    public function display_settings_section()
    {
        if ($this->ps_helper->has_api_info())
        {
            $this->format_toggled_box(
                __('Show PRO Sitemaps API Key instructions', 'pro-sitemaps-connect'),
                $this->format_apikey_help()
            );
        }
        else
        {
            echo wp_kses_post($this->format_apikey_help());
        }
    }

    /**
     * test_api_connection - perform a testing aAPI request
     * @return
     */
    public function test_api_connection()
    {
            echo '<div class="postbox ps_postbox">' . esc_html__('Testing your API connection', 'pro-sitemaps-connect') . '... ';
            ob_flush();
            flush();
            $sitemap_info = $this->ps_api->get_sitemap_info();
            if (isset($sitemap_info['error']))
            {
                echo '<b>' . esc_html__('Error: ', 'pro-sitemaps-connect') . esc_html($sitemap_info['error']) . '</b>';
            }
            else
            {

                echo '<b>' . esc_html__('Success', 'pro-sitemaps-connect') . '!</b>';
            	$site_id = $this->ps_helper->_get_option('ps_siteid');
            	$site_link = 'https://pro-sitemaps.com/site/'.intval($site_id).'/';
                echo '<p>'.esc_html__('Your PRO Sitemaps site account settings','pro-sitemaps-connect').
                	': <a href="'.esc_attr($site_link).'">'.esc_html($site_link).'</a></p>';
                if (isset($sitemap_info['sitemap_list']) && $sitemap_info['sitemap_list'])
                {
                    $smlist = '';
                    $sumbit_smlist = [];
                    $this->ps_helper->_update_option('sitemap_list', $sitemap_info['sitemap_list']);
                    foreach ($sitemap_info['sitemap_list'] as $_sminfo)
                    {
                    	if(!$smlist)
                    	{
                    		// display domain names in the first row
	                    	$smlist .= '<tr>'.
	                    		'<td>'.dirname($_sminfo['client_url']).'/</td>'.
	                    		'<td>'.dirname($_sminfo['sitemap_url']).'/</td>'.
	                    		'</tr>';
                    	}
                    	$smlist .= '<tr>'.
                    		'<td><a href="'.esc_html($_sminfo['client_url']).'" target="_blank">'.
                    		basename($_sminfo['client_url']).'</a></td>'.
                    		'<td>&lt;- <a href="'.esc_html($_sminfo['sitemap_url']).'" target="_blank">'.
                    		basename($_sminfo['sitemap_url']).'</a></td>'.
                    		'</tr>';
                        if ($_sminfo['se_submit'] && ($_sminfo['elements_count'] > 0 ))
                        {
                        	$sumbit_smlist[] = '<li><a href="'.esc_html($_sminfo['client_url']).'" target="_blank">'.
                        		esc_html($_sminfo['client_url']).'</a></li>';
                        }
                    }
                    $smlist = '<table class="ps_smtable">'.
                    	'<tr><th>'.__('Your Sitemap URL', 'pro-sitemaps-connect').'</th>'.
                    	'<th>'.__('Original Sitemap', 'pro-sitemaps-connect').'</th></tr>'.
                    	$smlist.
                    	'</table>';
                    if($sumbit_smlist)
                    {
                    	echo '<h3>'.esc_html__('You are all set now.','pro-sitemaps-connect').'</h3>'.
                    		wp_kses_post('<p>We recommend to enable the "Update robots.txt" setting below on this page'.
                    		' and submit the following sitemaps in your '.
                    		'<a href="https://search.google.com/search-console" target="_blank">Google Search Console</a> account:'.
                    		'</p>', 'pro-sitemaps-connect').
                    		'<ul>'.wp_kses_post(implode('', $sumbit_smlist)).'</ul>'.
                    		'<hr />';
                	}
                    $this->format_toggled_box(
                        __('Show the full list of found sitemaps', 'pro-sitemaps-connect'),
                        $smlist
                    );
                }else{
                	echo esc_html__('No sitemaps have been detected, please check your PRO Sitemaps account', 'pro-sitemaps-connect');
                }
            }

            $this->format_toggled_box(
                __('Show debug info of the testing API request', 'pro-sitemaps-connect'),
                '<textarea rows="40" class="ps_debug" readonly>' . esc_textarea(print_r($sitemap_info, true)) . '</textarea>'
            );
            echo '</div>';
    }
    /**
     * display_setting_field - show the settings form input fields
     * @return
     */
    public function display_setting_field($args)
    {
        $_optname = $args['label_for'];
        $_is_cb = ($args['type'] === 'checkbox');
        $_value = $this->ps_helper->_get_option($_optname);
        $_field_notice = [];

        if ($_optname === 'ps_robots_txt')
        {
            $args['description'] = preg_replace('#(robots\.txt)#',
                '<a href="' . trailingslashit(get_home_url()) . '$01">$01</a>',
                $args['description']);

            if (!$this->ps_helper->has_api_info())
            {
                $_field_notice[] = __('Note: This feature is not available until correct API options above are provided', 'pro-sitemaps-connect');
            }
            if ($this->ps_helper->has_robots_file())
            {
                $_field_notice[] = __('Note: This feature is not available while a physical robots.txt file exists in the blog folder', 'pro-sitemaps-connect');
                if($robots_entries = $this->ps_api->get_robots_txt_entries())
                $_field_notice[] = __('We recommend adding these entries in your robots.txt file:', 'pro-sitemaps-connect').
            		nl2br(esc_html($robots_entries));
            }
        }
        if ($_optname === 'ps_sitemap_name')
        {
        	$permalinks_on = get_option( 'permalink_structure' );
            $args['description'] .= ':<br />'.
            	$this->ps_helper->get_sitemap_local_path().
            	'<span id="ps_slug_id">'.esc_html($_value).'</span>';
    	}
        if ($_optname === 'ps_update_sitemap')
        {
            $_field_notice[] = __('Note: This API method is only available for upgraded PRO Sitemaps accounts', 'pro-sitemaps-connect');
    	}

        echo '<input type="hidden" name="pro_sitemaps_connect_options[_set_' . esc_attr($_optname) . ']" value="1" />';
        echo '<input type="' . esc_attr($args['type']) . '" id="' . esc_attr($_optname) . '" ' .
        'name="pro_sitemaps_connect_options[' . esc_attr($_optname) . ']" ' .

        ($_is_cb ? 'value="1" ' . ($_value ? 'checked="checked" ' : '') :
            'value="' . esc_attr($_value) . '" '),

            (isset($args['size']) ? 'size="' . esc_attr($args['size']) . '" ' : '') .
            ' />';
        if (isset($args['description']))
        {
            if ($_is_cb)
            echo '<label for="' . esc_attr($_optname) . '">' . wp_kses_post($args['description']) . '</label>';

            else
            echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
        }
        if ($_field_notice)
        {
            foreach($_field_notice as $_fnotice)
            {
                echo wp_kses_post('<br />' . $_fnotice);
            }
        }
    }

    /**
     * format_toggled_box - show text box with the toggle link
     * @return
     */
    public function format_toggled_box($_linktext, $_boxtext)
    {
        echo
            '<p><a href="#" onclick="jQuery(this).parent().next().toggle();return false;">' .
            	esc_html($_linktext) . '<b>&#x25BE;</b></a></p>' .
            '<div class="hidden ps_toggle">' . wp_kses_post($_boxtext) . '</div>' .
            '';
    }

    /**
     * format_apikey_help - show the API Key help text
     * @return
     */
    public function format_apikey_help()
    {
    	$esc_blog_link = esc_html(trailingslashit(get_home_url()));
        return '<div class="ps_postbox postbox"><h2>How to obtain PRO Sitemaps API key</h2>' .
        '<ol>' .
        '<li>Login to your PRO Sitemaps account on <a href="https://pro-sitemaps.com" target="_blank">https://pro-sitemaps.com</a>' .
        ', or <a href="https://pro-sitemaps.com?addurl='.$esc_blog_link.'">create a new free account</a> if you don\'t have one.</li>' .
        '<li>Select your <i>' . $esc_blog_link . '</i> site entry in PRO Sitemaps dashboard, ' .
        'or <a href="https://pro-sitemaps.com?addurl='.$esc_blog_link.'">create a new site entry</a> for it.</li>' .
        '<li>Select "More" -&gt; "Use API" in the navigation menu.' .
        '<p><img src="' . plugins_url('images/ps_screen_menu.png', __FILE__) . '"  /></p>' .
        '</li>' .
        '<li>You will find your API Key and Site ID on that page.<br />' .
        '<p><img src="' . plugins_url('images/ps_screen_apikey.png', __FILE__) . '"  />' .
        '<img src="' . plugins_url('images/ps_screen_siteid.png', __FILE__) . '"  /></p>' .
            '</li>' .
            '<li>Enter both in the settings below on this page and click "Save".</li>' .
            '</ol></div>';
    }
}

/**
 * WP Settings handler class
 *
 */
class Pro_Sitemaps_Connect_WPHelper
{
    /**
     * default plugin's options list
     * used for register_option / add_option
     */

    protected $ps_default_options = [
        'ps_siteid' => '',
        'ps_apikey' => '',
        'ps_sitemap_name' => 'pro-sitemaps.xml',
        'ps_robots_txt' => 1,
        'ps_update_sitemap' => 1,
    ];

    /**
     * constructor. setting up hooks.
     */
    public function __construct()
    {
    }

    /**
     * default_options - return default options
     * @return [array] default plugin options
     */
    public function default_options()
    {
        return $this->ps_default_options;
    }

    /**
     * _get_option - return either default or current setting value
     * @return [mixed] option value
     */
    public function _get_option($optname)
    {
        $current_options = get_option('pro_sitemaps_connect_options');
        if (isset($current_options[$optname]))
        {
            return $current_options[$optname];
        }
        else
        if (isset($current_options['_set_' . $optname]))
        {
            return false;
        }
        else
        {
            return $this->ps_default_options[$optname];
        }
    }
    /**
     * _update_option - update plugin's option in the array
     * @return
     */
    public function _update_option($optname, $newvalue)
    {
        $current_options = get_option('pro_sitemaps_connect_options');
        $current_options[$optname] = $newvalue;
        update_option('pro_sitemaps_connect_options', $current_options);
    }

    /**
     * get_sitemap_local_path - return local sitemap path depending on whether URL rewrites are enabled
     * @return [string]
     */
    public function get_sitemap_local_path()
    {
    	$sitemap_path = trailingslashit(get_home_url());
    	if(!get_option( 'permalink_structure' ))
    	{
    		$sitemap_path .= 'index.php?prositemaps-get=';
    	}
        return $sitemap_path;

    }

    /**
     * has_api_info - check if API key settings were defined
     * @return [bool]
     */
    public function has_api_info()
    {
        return $this->_get_option('ps_apikey') && $this->_get_option('ps_siteid');

    }

    /**
     * has_robots_file - check if physical robots.txt file exists
     * @return [bool]
     */
    public function has_robots_file()
    {
        return file_exists(ABSPATH . 'robots.txt');

    }
}
