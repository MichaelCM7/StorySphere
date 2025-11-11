@echo off
REM Auto-update book covers every hour
REM Save this as: C:\Apache24\htdocs\StorySphere\update_covers.bat
REM Then schedule it in Windows Task Scheduler

cd /d C:\Apache24\htdocs\StorySphere\Utils
php auto_update_covers.php

REM Optional: Log the output
REM php auto_update_covers.php >> C:\Apache24\htdocs\StorySphere\logs\cover_updates.log 2>&1
