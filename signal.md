[pcntl_signal](http://www.hackingwithphp.com/16/1/1/taking-control-of-php)
---
```php
bool pcntl_signal ( int $signo , callable|int $handler [, bool $restart_syscalls = true ] )
```
The `pcntl_signal()` function installs a new signal handler or replaces the current signal handler for the signal indicated by `signo`.
