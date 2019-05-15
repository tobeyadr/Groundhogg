<?php
namespace Groundhogg;

use Groundhogg\DB\Manager;

class Main_Installer extends Installer
{

    /**
     * Activate the Groundhogg plugin.
     */
    protected function activate()
    {
        // Install our DBS...
        Plugin::$instance->dbs->install_dbs();

        // Add roles and caps...
        Plugin::$instance->roles->install_roles_and_caps();

        // Install Default tags for tag mapping.
        Plugin::$instance->tag_mapping->install_default_tags();
    }

    protected function deactivate()
    {
        // TODO: Implement deactivate() method.
    }

    /**
     * The path to the main plugin file
     *
     * @return string
     */
    function get_plugin_file()
    {
        return GROUNDHOGG__FILE__;
    }

    /**
     * Get the plugin version
     *
     * @return string
     */
    function get_plugin_version()
    {
        return GROUNDHOGG_VERSION;
    }

    /**
     * A unique name for the updater to avoid conflicts
     *
     * @return string
     */
    protected function get_installer_name()
    {
        return 'main';
    }

    /**
     * Drop these tables when uninstalling MU site.
     *
     * @return string[]
     */
    protected function get_table_names()
    {
        return Plugin::$instance->dbs->get_table_names();
    }

    /**
     * Fires after the 'activated_plugin' hook.
     *
     * @param $plugin
     */
    public function plugin_activated( $plugin )
    {
        if( $plugin == plugin_basename( GROUNDHOGG__FILE__ ) ) {
            if ( Plugin::$instance->settings->is_option_enabled( 'gh_guided_setup_finished' ) ){
                exit( wp_redirect( admin_url( 'admin.php?page=groundhogg' ) ) );
            } else {
                exit( wp_redirect( admin_url( 'admin.php?page=gh_guided_setup' ) ) );
            }
        }
    }
}