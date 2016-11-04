<?php    
    /**
     * Set process name.
     *
     * @param string $title
     * @return void
     */
    protected static function setProcessTitle($title)
    {
        // >=php 5.5
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($title);
        } // Need proctitle when php<=5.5 .
        elseif (extension_loaded('proctitle') && function_exists('setproctitle')) {
            @setproctitle($title);
        }
    }
