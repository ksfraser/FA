@echo off
rem -- Check if argument is INSTALL or REMOVE

if not ""%1"" == ""INSTALL"" goto remove

"C:/Bitnami/orangehrm-3.3.2-1/apache2\bin\httpd.exe" -k install -n "orangehrmApache" -f "C:/Bitnami/orangehrm-3.3.2-1/apache2\conf\httpd.conf"

net start orangehrmApache >NUL
goto end

:remove
rem -- STOP SERVICE BEFORE REMOVING

net stop orangehrmApache >NUL
"C:/Bitnami/orangehrm-3.3.2-1/apache2\bin\httpd.exe" -k uninstall -n "orangehrmApache"

:end
exit
