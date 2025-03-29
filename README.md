# Updates from glli

* Notes storage folder is moved out of www root
  * Move xnotes folder to a safe place
  * Update xnotes_path in config.php
    * Replace '/jffs/var/lib/xnotes' with your xnotes folder path
* Support multiple users
  * In xnotes folder, copy admin folder and rename it to a new username, e.g. glli
  * Change 'admin' to 'glli' in glli/cfg/account.config
  * Login with 'glli' (password: root) and change the password
  * To delete a user, simply delete the user folder in xnotes folder
* Add readme in admin files and favicon

(Tested with php 7.4 on my dd-wrt router, may not work with newer php versions)

# X:/Notes

X:/Notes is a note taking web app developed in HTML, CSS and JavaScript (+jQuery) for the front-end, and PHP for the back-end. Please consult the "Help" page on the actual website for more information on how it all works and the different things you can do with it.

**Login Details**

**Username**: admin

**Password**: root

![X:/Notes](https://i.imgur.com/2nwcnC6.jpg)
