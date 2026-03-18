param(
	[string]$PluginFile = "portico_webworks_plugin.php",
	[string]$AdminPreviewFile = "admin-preview.html"
)

$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $MyInvocation.MyCommand.Definition

$pluginPath = Join-Path $root $PluginFile
$adminPreviewPath = Join-Path $root $AdminPreviewFile

if (!(Test-Path -LiteralPath $pluginPath)) {
	throw "Missing plugin file: $pluginPath"
}

if (!(Test-Path -LiteralPath $adminPreviewPath)) {
	throw "Missing admin preview file: $adminPreviewPath"
}

$pluginPhp = Get-Content -LiteralPath $pluginPath -Raw

$versionMatch = [regex]::Match(
	$pluginPhp,
	"define\(\s*[\x27\x22]PW_VERSION[\x27\x22]\s*,\s*[\x27\x22]([^\x27\x22]+)[\x27\x22]\s*\)"
)
if (!$versionMatch.Success) {
	throw "Could not find PW_VERSION define() in $pluginPath"
}

$version = $versionMatch.Groups[1].Value.Trim()
if ([string]::IsNullOrWhiteSpace($version)) {
	throw "PW_VERSION was empty"
}

$updatedPluginPhp = $pluginPhp
$updatedPluginPhp = [regex]::Replace(
	$updatedPluginPhp,
	'\*\s*Version:\s*[0-9A-Za-z\.\-]+',
	'* Version: ' + $version
)

$adminPreview = Get-Content -LiteralPath $adminPreviewPath -Raw

$updatedAdminPreview = $adminPreview
$updatedAdminPreview = [regex]::Replace(
	$updatedAdminPreview,
	'<div\s+class="ver">\s*v[0-9A-Za-z\.\-]+\s*</div>',
	'<div class="ver">v' + $version + '</div>'
)

$updatedAdminPreview = [regex]::Replace(
	$updatedAdminPreview,
	"<strong>Plugin version</strong>:\\s*v[0-9A-Za-z\\.\\-]+",
	"<strong>Plugin version</strong>: v$version"
)

if ($updatedPluginPhp -ne $pluginPhp) {
	Set-Content -LiteralPath $pluginPath -Value $updatedPluginPhp -NoNewline
}

if ($updatedAdminPreview -ne $adminPreview) {
	Set-Content -LiteralPath $adminPreviewPath -Value $updatedAdminPreview -NoNewline
}

Write-Output $version

