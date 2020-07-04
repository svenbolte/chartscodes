<?php
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit;

global $wpdb;
$ip_ranges_table_name = $wpdb->prefix . 'ipflag_ip_ranges';
$countries_table_name = $wpdb->prefix . 'ipflag_countries';

$wpdb->query('DROP TABLE IF EXISTS '.$ip_ranges_table_name.';');
$wpdb->query('DROP TABLE IF EXISTS '.$countries_table_name.';');
if(get_option('ipflag_db_version'))
    delete_option('ipflag_db_version');
if(get_option('ipflag_options'))
    delete_option('ipflag_options');
?>
