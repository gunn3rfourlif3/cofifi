<#
.SYNOPSIS
    Bootstrap a local Cofifi WordPress install on XAMPP.

.DESCRIPTION
    Downloads WordPress, creates the database and wp-config, installs WordPress
    and WooCommerce, activates the Cofifi theme, then runs setup/provision.php
    to create pages, menus, categories and sample products.

    Idempotent: safe to re-run. Each step is skipped when already done.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File setup\install.ps1

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File setup\install.ps1 -DbPass "secret" -AdminPass "correct-horse"
#>

[CmdletBinding()]
param(
    # WordPress document root. Defaults to two levels above this script
    # (setup/ lives in the theme, the theme in wp-content/themes).
    [string] $Root,

    [string] $SiteUrl    = 'http://localhost/development/cofifi',
    [string] $SiteTitle  = 'Cofifi',

    [string] $DbName     = 'cofifi',
    [string] $DbUser     = 'root',
    [string] $DbPass     = '',
    [string] $DbHost     = 'localhost',
    [string] $DbPrefix   = 'cof_',

    [string] $AdminUser  = 'cofifi',
    [string] $AdminPass  = '',
    [string] $AdminEmail = 'admin@example.com',

    [string] $XamppPath  = 'C:\xampp',

    [switch] $SkipProducts,
    [switch] $SkipDownload
)

$ErrorActionPreference = 'Stop'

function Step   ($m) { Write-Host "  → $m" -ForegroundColor Cyan }
function Ok     ($m) { Write-Host "  ✓ $m" -ForegroundColor Green }
function Skip   ($m) { Write-Host "  · $m" -ForegroundColor DarkGray }
function Warn   ($m) { Write-Host "  ! $m" -ForegroundColor Yellow }
function Fail   ($m) { Write-Host "  ✗ $m" -ForegroundColor Red; exit 1 }

Write-Host ''
Write-Host '  Cofifi — local install' -ForegroundColor White
Write-Host '  ----------------------' -ForegroundColor DarkGray

# ---------------------------------------------------------------------------
# Paths
# ---------------------------------------------------------------------------

$ThemeDir = Split-Path -Parent $PSScriptRoot          # wp-content/themes/cofifi
if (-not $Root) {
    # themes -> wp-content -> document root
    $Root = Split-Path -Parent (Split-Path -Parent (Split-Path -Parent $ThemeDir))
}

$Php = Join-Path $XamppPath 'php\php.exe'
if (-not (Test-Path $Php)) {
    $cmd = Get-Command php -ErrorAction SilentlyContinue
    if ($cmd) { $Php = $cmd.Source } else { Fail "PHP not found. Looked in $Php and on PATH. Set -XamppPath." }
}

Ok "PHP        $Php"
Ok "Root       $Root"
Ok "Theme      $ThemeDir"

if (-not (Test-Path (Join-Path $ThemeDir 'style.css'))) {
    Fail "The theme is not where this script expects it. Run this from wp-content/themes/cofifi/setup."
}

# ---------------------------------------------------------------------------
# MySQL must be running
# ---------------------------------------------------------------------------

Step 'Checking MySQL'
$mysqld = Get-Process -Name 'mysqld' -ErrorAction SilentlyContinue
if (-not $mysqld) {
    Fail 'MySQL is not running. Start MySQL in the XAMPP Control Panel and run this again.'
}
Ok 'MySQL is running'

# ---------------------------------------------------------------------------
# WP-CLI
# ---------------------------------------------------------------------------

$BinDir  = Join-Path $PSScriptRoot 'bin'
$WpPhar  = Join-Path $BinDir 'wp-cli.phar'

if (-not (Test-Path $WpPhar)) {
    Step 'Downloading WP-CLI'
    New-Item -ItemType Directory -Force -Path $BinDir | Out-Null
    try {
        Invoke-WebRequest -Uri 'https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar' `
                          -OutFile $WpPhar -UseBasicParsing
    } catch {
        Fail "Could not download WP-CLI: $($_.Exception.Message)"
    }
    Ok 'WP-CLI downloaded'
} else {
    Skip 'WP-CLI already present'
}

function Wp {
    param([Parameter(ValueFromRemainingArguments = $true)] $Args)
    & $Php $WpPhar --path="$Root" @Args
    if ($LASTEXITCODE -ne 0) { Fail "wp $($Args -join ' ') failed (exit $LASTEXITCODE)" }
}

function WpQuiet {
    param([Parameter(ValueFromRemainingArguments = $true)] $Args)
    & $Php $WpPhar --path="$Root" @Args 2>&1 | Out-Null
    return $LASTEXITCODE
}

# ---------------------------------------------------------------------------
# WordPress core
# ---------------------------------------------------------------------------

New-Item -ItemType Directory -Force -Path $Root | Out-Null

if (Test-Path (Join-Path $Root 'wp-settings.php')) {
    Skip 'WordPress core already downloaded'
} elseif ($SkipDownload) {
    Fail 'WordPress core is missing and -SkipDownload was passed.'
} else {
    Step 'Downloading WordPress'
    Wp core download --locale=en_GB
    Ok 'WordPress downloaded'
}

# ---------------------------------------------------------------------------
# wp-config.php
# ---------------------------------------------------------------------------

if (Test-Path (Join-Path $Root 'wp-config.php')) {
    Skip 'wp-config.php already exists'
} else {
    Step 'Writing wp-config.php'
    Wp config create --dbname=$DbName --dbuser=$DbUser --dbpass=$DbPass --dbhost=$DbHost `
                     --dbprefix=$DbPrefix --locale=en_GB --skip-check
    Wp config set WP_DEBUG true --raw
    Wp config set WP_DEBUG_LOG true --raw
    Wp config set WP_DEBUG_DISPLAY false --raw
    Wp config set WP_ENVIRONMENT_TYPE local
    Ok 'wp-config.php written'
}

# ---------------------------------------------------------------------------
# Database
# ---------------------------------------------------------------------------

if ((WpQuiet db check) -eq 0) {
    Skip "Database '$DbName' already reachable"
} else {
    Step "Creating database '$DbName'"
    Wp db create
    Ok 'Database created'
}

# ---------------------------------------------------------------------------
# Install WordPress
# ---------------------------------------------------------------------------

if ((WpQuiet core is-installed) -eq 0) {
    Skip 'WordPress already installed'
} else {
    if (-not $AdminPass) {
        $AdminPass = -join ((48..57) + (65..90) + (97..122) | Get-Random -Count 18 | ForEach-Object { [char]$_ })
        $generated = $true
    }
    Step 'Installing WordPress'
    Wp core install --url=$SiteUrl --title=$SiteTitle --admin_user=$AdminUser `
                    --admin_password=$AdminPass --admin_email=$AdminEmail --skip-email
    Ok 'WordPress installed'
    if ($generated) {
        Write-Host ''
        Write-Host '  ADMIN PASSWORD (shown once — save it now)' -ForegroundColor Yellow
        Write-Host "    user: $AdminUser" -ForegroundColor Yellow
        Write-Host "    pass: $AdminPass" -ForegroundColor Yellow
        Write-Host ''
    }
}

# ---------------------------------------------------------------------------
# WooCommerce
# ---------------------------------------------------------------------------

if ((WpQuiet plugin is-active woocommerce) -eq 0) {
    Skip 'WooCommerce already active'
} else {
    Step 'Installing WooCommerce'
    Wp plugin install woocommerce --activate
    Ok 'WooCommerce active'
}

# ---------------------------------------------------------------------------
# Theme + provisioning
# ---------------------------------------------------------------------------

Step 'Activating the Cofifi theme'
Wp theme activate cofifi
Ok 'Theme active'

Step 'Provisioning content'
$provision = Join-Path $PSScriptRoot 'provision.php'
if ($SkipProducts) {
    Wp eval-file "$provision" --skip-products
} else {
    Wp eval-file "$provision"
}

Wp rewrite flush --hard

Write-Host ''
Write-Host "  Done. Open $SiteUrl" -ForegroundColor Green
Write-Host "  Admin:   $SiteUrl/wp-admin" -ForegroundColor Green
Write-Host ''
