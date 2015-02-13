Moodle admin tool plugin - External database grade import
===================

Information
-----------

This admin tool allow you to an external database (of nearly any kind) to import grades in your courses. It is assumed that the external database includes two tables. One to create the elements of assessment and the other to insert student grades.

It was created by Gilles-Philippe Leblanc, developer at Université de Montréal.

To install it using git, type this command in the admin/tool folder of your Moodle install:
```
git clone https://github.com/leblangi/moodle-tool_dbgradeimport.git dbgradeimport
```
Then add /admin/tool/dbgradeimport to your git ignore.

Alternatively, download the zip from
<https://github.com/leblangi/moodle-tool_dbgradeimport/archive/master.zip>,
unzip it into the local folder, and then rename the new folder to "dbgradeimport".

After you have installed this admin tool plugin, you
should see a new option in the settings block:

> Site administration -> Courses -> External database grade import -> Settings

Once these settings are configured correctly, simply configure a new cron to proceed with the creation of the grade items and to sync student grades.

Sample cron entry:
```
# 5 minutes past 4am
5 4 * * * $sudo -u www-data /usr/bin/php /var/www/moodle/admin/tool/dbgradeimport/cli/sync.php
```

Notes:
- it is required to use the web server account when executing PHP CLI scripts
- you need to change the "www-data" to match the apache user account
- use "su" if "sudo" not available

I hope you find this tool useful. Please feel free to enhance it.
Report any idea or bug @
<https://github.com/leblangi/moodle-tool_purgeautobackups/issues>, thanks!
