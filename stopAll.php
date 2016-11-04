<?php    
    /**
     * Stop.
     *
     * @return void
     */
    public static function stopAll()
    {
        self::$_status = self::STATUS_SHUTDOWN;
        // For master process.
        if (self::$_masterPid === posix_getpid()) {
            self::log("Workerman[" . basename(self::$_startFile) . "] Stopping ...");
            $worker_pid_array = self::getAllWorkerPids();
            // Send stop signal to all child processes.
            foreach ($worker_pid_array as $worker_pid) {
                posix_kill($worker_pid, SIGINT);
                Timer::add(self::KILL_WORKER_TIMER_TIME, 'posix_kill', array($worker_pid, SIGKILL), false);
            }
        } // For child processes.
        else {
            // Execute exit.
            foreach (self::$_workers as $worker) {
                $worker->stop();
            }
            exit(0);
        }
    }
