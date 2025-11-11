@echo off
setlocal enabledelayedexpansion
REM Auto-update covers & descriptions. Schedule this file in Task Scheduler.

REM Ensure logs directory exists
if not exist "C:\Apache24\htdocs\StorySphere\logs" mkdir "C:\Apache24\htdocs\StorySphere\logs"
set "LOGFILE=C:\Apache24\htdocs\StorySphere\logs\cover_updates.log"

REM Resolve php.exe (assumes it's on PATH). If not, set full path below, e.g. set PHP=C:\php\php.exe
set "PHP=php"
where %PHP% >nul 2>&1
if errorlevel 1 (
	echo [%date% %time%] WARNING: php.exe not found on PATH. Edit update_covers.bat to set full path.>>"%LOGFILE%"
)

pushd C:\Apache24\htdocs\StorySphere\Utils
echo [%date% %time%] Starting auto_update_covers.php --silent>>"%LOGFILE%"
%PHP% auto_update_covers.php --silent >>"%LOGFILE%" 2>&1
set "EC=%ERRORLEVEL%"
echo [%date% %time%] Finished with exit code !EC!>>"%LOGFILE%"
popd

REM Normalize exit code so Task Scheduler reports success even when updates occurred (php returns #updated)
exit /b 0
