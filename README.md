# PHP_Mafia
===========

**Warning: This code is extremely old and essentially just being updated to record all changes that were made. It is *not* up to snuff security-wise and should just be a starting point for a workable web version, not an actual product. Can't stress this enough, this is just a dumping ground of oldness**

##Goal
This project hoped to bring the amazing game of Mafia (also known as 
Werewolf) to the web in a non-forum manner. It won't be as detailed as some of
the forum run games can be, due to the sheer number of special cases in roles
but all basic roles will be represented, as well as most common roles. Most of this was accomplished.

Instructions:
1. Clone this repository
2. Move <code>includes/mysql_config.php.sample</code> to <code>includes/mysql_config.php</code>
3. Fill in the pertinent database details for <code>includes/mysql_config.php</code>
4. Import the <code>create_database.sql</code> into your database, this will create the necessary tables and fill in default values. Delete this file after importing it.
5. Edit the <code>includes/site_functions.php</code> file to update the <code>$site_domain</code> variable at the top with the domain you are using.
6. Be very aware that this will default to http and if you want to use https, edit the <code>includes/site_functions.php</code> file to update the <code>$site_protocol</code> variable to "https" and put certs in the right places
7. Login with the <code>admin</code> user, using <code>admin</code> as the password. Be sure to click 'Account' and change your password immediately.
8. If you have the <code>mail</code> command set up on your PHP host, then you can send invitation emails to friends directly if you navigate to /invite.php. Otherwise, create new users in MySQL and use MD5 (I said it was old) as the pssword hash.

