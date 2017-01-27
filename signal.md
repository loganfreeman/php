[pcntl_signal](http://www.hackingwithphp.com/16/1/1/taking-control-of-php)
---
```php
bool pcntl_signal ( int $signo , callable|int $handler [, bool $restart_syscalls = true ] )
```
The `pcntl_signal()` function installs a new signal handler or replaces the current signal handler for the signal indicated by `signo`.

很多纯PHP开发的后端框架中都使用了pcntl扩展提供的信号处理函数pcntl_signal，实际上这个函数的性能是很差的。首先看一段示例代码：
```php
declare(ticks = 1);
pcntl_signal(SIGINT, 'signalHandler');
```
这段代码在执行pcntl_signal前，先加入了declare(ticks = 1)。因为PHP的函数无法直接注册到操作系统信号设置中，所以pcntl信号需要依赖tick机制。通过查看pcntl.c的源码实现发现。pcntl_signal的实现原理是，触发信号后先将信号加入一个队列中。然后在PHP的ticks回调函数中不断检查是否有信号，如果有信号就执行PHP中指定的回调函数，如果没有则跳出函数。

这样就存在一个比较严重的性能问题，大家都知道PHP的ticks=1表示每执行1行PHP代码就回调此函数。实际上大部分时间都没有信号产生，但ticks的函数一直会执行。如果一个服务器程序1秒中接收1000次请求，平均每个请求要执行1000行PHP代码。那么PHP的pcntl_signal，就带来了额外的 1000 * 1000，也就是100万次空的函数调用。这样会浪费大量的CPU资源。

比较好的做法是去掉ticks，转而使用`pcntl_signal_dispatch`，在代码循环中自行处理信号。

The `pcntl_signal_dispatch()` function calls the signal handlers installed by pcntl_signal() for each pending signal.

Ignore `Ctr+C`
---
```php
declare (ticks = 1);
pcntl_signal(SIGINT, SIG_IGN, true);
```




