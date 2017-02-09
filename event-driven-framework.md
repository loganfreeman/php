Links
---
- [Workerman](https://github.com/walkor/Workerman)
- [PHP-Daemon](https://github.com/shaneharter/PHP-Daemon)
- [Create Daemons in PHP](http://kvz.io/blog/2009/01/09/create-daemons-in-php/)
- [react](https://github.com/reactphp/react)
- [event-loop](https://github.com/reactphp/event-loop)

daemonize
---
A session leader is a process where session id == process id. This sounds contrived, but the session id is inherited by child processes. Some operations within UNIX/Linux operate on process sessions, for example, negating the process id when sending to the kill system call or command. The most common use for this is when logging out of a shell. The OS will send kill -HUP -$$, which will send a SIGHUP (hangup) signal to all the processes with the same session id as the shell. When you disown a process, the session id of the process is changed from the shell, so it will not respond to the hangup signal. This is one part of the process to become a daemon process.
```php
protected static function daemonize()
    {
        if (!self::$daemonize) {
            return;
        }
        umask(0);
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new Exception('fork fail');
        } elseif ($pid > 0) {
            exit(0);
        }
        if (-1 === posix_setsid()) {
            throw new Exception("setsid fail");
        }
        // Fork again avoid SVR4 system regain the control of terminal.
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new Exception("fork fail");
        } elseif (0 !== $pid) {
            exit(0);
        }
    }
 ```
