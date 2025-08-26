@echo off
cd C:\xampp\htdocs\aplicaciones\api
echo Iniciando ejecucion: %date% %time% >> C:\logs\laravel.log
C:\xampp\php\php.exe artisan actualizar:estado-automatico >> C:\logs\laravel.log 2>&1
echo Finalizado: %date% %time% >> C:\logs\laravel.log
exit