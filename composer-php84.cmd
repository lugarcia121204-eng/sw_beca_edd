@echo off
setlocal
set "PHP84=C:\laragon\bin\php\php-8.4.23-nts-Win32-vs17-x64"
if not exist "%PHP84%\php.exe" (
    echo No se encuentra PHP 8.4.23 en %PHP84%.
    exit /b 1
)
set "PATH=%PHP84%;%PATH%"
"%PHP84%\php.exe" "C:\ProgramData\ComposerSetup\bin\composer.phar" --working-dir="%~dp0." %*
exit /b %errorlevel%
