@echo off
rem -- Check if argument is INSTALL or REMOVE

if not ""%1"" == ""INSTALL"" goto remove

"C:\Bitnami\orangehrm-3.3.2-1/mysql\bin\mysqld.exe" --install "orangehrmMySQL" --defaults-file="C:\Bitnami\orangehrm-3.3.2-1/mysql\my.ini"

net start "orangehrmMySQL" >NUL
goto end

:remove
rem -- STOP SERVICES BEFORE REMOVING

net stop "orangehrmMySQL" >NUL
"C:\Bitnami\orangehrm-3.3.2-1/mysql\bin\mysqld.exe" --remove "orangehrmMySQL"

:end
exit
