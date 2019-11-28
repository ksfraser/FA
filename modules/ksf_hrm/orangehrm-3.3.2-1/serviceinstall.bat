@echo off
rem -- Check if argument is INSTALL or REMOVE

if not ""%1"" == ""INSTALL"" goto remove

if exist C:\Bitnami\ORANGE~1.2-1\mysql\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\mysql\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\postgresql\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\postgresql\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\elasticsearch\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\elasticsearch\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\logstash\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\logstash\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\logstash-forwarder\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\logstash-forwarder\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\apache2\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\apache2\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\apache-tomcat\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\apache-tomcat\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\resin\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\resin\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\jboss\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\jboss\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\wildfly\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\wildfly\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\activemq\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\activemq\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\openoffice\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\openoffice\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\subversion\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\subversion\scripts\serviceinstall.bat INSTALL)
rem RUBY_APPLICATION_INSTALL
if exist C:\Bitnami\ORANGE~1.2-1\mongodb\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\mongodb\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\lucene\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\lucene\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\third_application\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\third_application\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\nginx\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\nginx\scripts\serviceinstall.bat INSTALL)
if exist C:\Bitnami\ORANGE~1.2-1\php\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\php\scripts\serviceinstall.bat INSTALL)
goto end

:remove

if exist C:\Bitnami\ORANGE~1.2-1\third_application\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\third_application\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\lucene\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\lucene\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\mongodb\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\mongodb\scripts\serviceinstall.bat)
rem RUBY_APPLICATION_REMOVE
if exist C:\Bitnami\ORANGE~1.2-1\subversion\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\subversion\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\openoffice\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\openoffice\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\jboss\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\jboss\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\wildfly\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\wildfly\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\resin\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\resin\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\activemq\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\activemq\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\apache-tomcat\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\apache-tomcat\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\apache2\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\apache2\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\logstash-forwarder\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\logstash-forwarder\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\logstash\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\logstash\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\elasticsearch\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\elasticsearch\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\postgresql\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\postgresql\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\mysql\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\mysql\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\php\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\php\scripts\serviceinstall.bat)
if exist C:\Bitnami\ORANGE~1.2-1\nginx\scripts\serviceinstall.bat (start /MIN C:\Bitnami\ORANGE~1.2-1\nginx\scripts\serviceinstall.bat)
:end
