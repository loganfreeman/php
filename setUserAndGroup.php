<?php
    /**
     * Set unix user and group for current process.
     *
     * @return void
     */
    public function setUserAndGroup()
    {
        // Get uid.
        $user_info = posix_getpwnam($this->user);
        if (!$user_info) {
            self::log("Warning: User {$this->user} not exsits");
            return;
        }
        $uid = $user_info['uid'];
        // Get gid.
        if ($this->group) {
            $group_info = posix_getgrnam($this->group);
            if (!$group_info) {
                self::log("Warning: Group {$this->group} not exsits");
                return;
            }
            $gid = $group_info['gid'];
        } else {
            $gid = $user_info['gid'];
        }
        // Set uid and gid.
        if ($uid != posix_getuid() || $gid != posix_getgid()) {
            if (!posix_setgid($gid) || !posix_initgroups($user_info['name'], $gid) || !posix_setuid($uid)) {
                self::log("Warning: change gid or uid fail.");
            }
        }
    }
