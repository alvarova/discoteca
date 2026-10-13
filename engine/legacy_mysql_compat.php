<?php
/*
 * Temporary mysql_* compatibility layer for the Discoteca search flow.
 * It is intentionally limited to the APIs used by engine/config.php.
 */
if (!function_exists('mysql_connect')) {
    if (!defined('MYSQL_ASSOC')) {
        define('MYSQL_ASSOC', MYSQLI_ASSOC);
        define('MYSQL_NUM', MYSQLI_NUM);
        define('MYSQL_BOTH', MYSQLI_BOTH);
    }

    $GLOBALS['_legacy_mysql_default_link'] = null;

    function _legacy_mysql_link($link_identifier = null)
    {
        if ($link_identifier instanceof mysqli) {
            return $link_identifier;
        }

        return isset($GLOBALS['_legacy_mysql_default_link'])
            ? $GLOBALS['_legacy_mysql_default_link']
            : null;
    }

    function _legacy_mysql_server_parts($server)
    {
        $server = (string) $server;
        if ($server === '') {
            return array(null, null, null);
        }

        if (strpos($server, ':/') !== false || strpos($server, ':\\') !== false) {
            return array(null, null, $server);
        }

        if (preg_match('/^\[(.+)\]:(\d+)$/', $server, $matches)) {
            return array($matches[1], (int) $matches[2], null);
        }

        if (preg_match('/^([^:]+):(\d+)$/', $server, $matches)) {
            return array($matches[1], (int) $matches[2], null);
        }

        return array($server, null, null);
    }

    function mysql_connect($server = null, $username = null, $password = null, $new_link = false, $client_flags = 0)
    {
        list($host, $port, $socket) = _legacy_mysql_server_parts($server);
        $link = mysqli_init();

        if (!$link || !@mysqli_real_connect($link, $host, $username, $password, null, $port, $socket, $client_flags)) {
            return false;
        }

        $GLOBALS['_legacy_mysql_default_link'] = $link;
        return $link;
    }

    function mysql_close($link_identifier = null)
    {
        $link = _legacy_mysql_link($link_identifier);
        if (!$link) {
            return false;
        }

        $closed = mysqli_close($link);
        if ($link === $GLOBALS['_legacy_mysql_default_link']) {
            $GLOBALS['_legacy_mysql_default_link'] = null;
        }

        return $closed;
    }

    function mysql_select_db($database_name, $link_identifier = null)
    {
        $link = _legacy_mysql_link($link_identifier);
        return $link ? @mysqli_select_db($link, $database_name) : false;
    }

    function mysql_query($query, $link_identifier = null)
    {
        $link = _legacy_mysql_link($link_identifier);
        return $link ? @mysqli_query($link, $query) : false;
    }

    function mysql_fetch_array($result, $result_type = MYSQL_BOTH)
    {
        return $result instanceof mysqli_result ? mysqli_fetch_array($result, $result_type) : false;
    }

    function mysql_num_rows($result)
    {
        return $result instanceof mysqli_result ? mysqli_num_rows($result) : false;
    }

    function mysql_free_result($result)
    {
        return $result instanceof mysqli_result ? mysqli_free_result($result) : false;
    }

    function mysql_error($link_identifier = null)
    {
        $link = _legacy_mysql_link($link_identifier);
        return $link ? mysqli_error($link) : '';
    }

    function mysql_errno($link_identifier = null)
    {
        $link = _legacy_mysql_link($link_identifier);
        return $link ? mysqli_errno($link) : 0;
    }

    function mysql_insert_id($link_identifier = null)
    {
        $link = _legacy_mysql_link($link_identifier);
        return $link ? mysqli_insert_id($link) : 0;
    }

    function mysql_affected_rows($link_identifier = null)
    {
        $link = _legacy_mysql_link($link_identifier);
        return $link ? mysqli_affected_rows($link) : -1;
    }
}
