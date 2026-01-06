@echo off
echo ================================================
echo Glasgow Subway Status - Installation Script
echo ================================================
echo.

REM Check if composer is available
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Composer not found. Please install Composer first.
    echo Visit: https://getcomposer.org/download/
    pause
    exit /b 1
)

echo Step 1: Installing Laravel dependencies...
echo.
call composer install

echo.
echo Step 2: Generating application key...
echo.
call php artisan key:generate

echo.
echo Step 3: Database setup
echo.
echo Please ensure MySQL is running in WAMP!
echo Database name: subway_checker
echo.
pause

echo.
echo Step 4: Running migrations...
echo.
call php artisan migrate

echo.
echo Step 5: Seeding database...
echo.
call php artisan db:seed

echo.
echo ================================================
echo Installation Complete!
echo ================================================
echo.
echo To start the development server:
echo   php artisan serve
echo.
echo Then visit: http://localhost:8000
echo.
echo To test with fixture data:
echo   1. Set SPT_SOURCE=fixture in .env
echo   2. Run: php artisan spt:poll
echo.
echo See QUICKSTART.md for more details.
echo.
pause
