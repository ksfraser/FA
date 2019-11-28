@echo off
"C:\Bitnami\orangehrm-3.3.2-1/mysql\bin\mysql.exe" --defaults-file="C:\Bitnami\orangehrm-3.3.2-1/mysql\my.ini" -u root -e "UPDATE mysql.user SET Password=password('%1') WHERE User='root';"
"C:\Bitnami\orangehrm-3.3.2-1/mysql\bin\mysql.exe" --defaults-file="C:\Bitnami\orangehrm-3.3.2-1/mysql\my.ini" -u root -e "DELETE FROM mysql.user WHERE User='';"
"C:\Bitnami\orangehrm-3.3.2-1/mysql\bin\mysql.exe" --defaults-file="C:\Bitnami\orangehrm-3.3.2-1/mysql\my.ini" -u root -e "FLUSH PRIVILEGES;"
