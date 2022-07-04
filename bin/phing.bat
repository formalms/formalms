@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../html/vendor/phing/phing/bin/phing
php "%BIN_TARGET%" %*
