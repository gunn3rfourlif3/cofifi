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

    [string] $SiteUrl    = 'http://localhost/cofifi',
    [string] $SiteTitle  = 'Cofifi',

    [string] $DbName     = 'cofifi',
    [string] $DbUser     = 'root',
    [string] $DbPass     = '',
    # Left blank on purpose — read from XAMPP's my.ini below, because XAMPP is
    # often moved off 3306 to avoid clashing with another MySQL.
    [string] $DbHost     = '',
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
Write-Host '  Cofifi - local install' -ForegroundColor White
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

# WP-CLI's `db` commands shell out to mysql / mysqladmin / mysqlcheck. XAMPP
# does not put those on PATH, so add them for this process only.
$MysqlBin = Join-Path $XamppPath 'mysql\bin'
if (Test-Path $MysqlBin) { $env:PATH = "$MysqlBin;$env:PATH" }

# XAMPP's MySQL is frequently on a non-default port. Read it rather than
# assuming 3306 — connecting to the wrong port is the most common failure here.
if (-not $DbHost) {
    $DbHost = 'localhost'
    $MyIni  = Join-Path $XamppPath 'mysql\bin\my.ini'
    if (Test-Path $MyIni) {
        $inMysqld = $false
        foreach ($line in Get-Content $MyIni) {
            $t = $line.Trim()
            if ($t -match '^\[(.+)\]$') { $inMysqld = ($Matches[1] -eq 'mysqld'); continue }
            if ($inMysqld -and $t -match '^port\s*=\s*(\d+)') {
                if ($Matches[1] -ne '3306') { $DbHost = "localhost:$($Matches[1])" }
                break
            }
        }
    }
}

Ok "PHP        $Php"
Ok "Root       $Root"
Ok "Theme      $ThemeDir"
Ok "DB host    $DbHost"

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

# Probe form: never throws, just reports the exit code. Used for "is this
# already done?" checks, where a non-zero exit is an expected answer.
function WpQuiet {
    param([Parameter(ValueFromRemainingArguments = $true)] $Args)
    $previous = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try {
        & $Php $WpPhar --path="$Root" @Args 2>&1 | Out-Null
        return $LASTEXITCODE
    } catch {
        return 1
    } finally {
        $ErrorActionPreference = $previous
    }
}

# ---------------------------------------------------------------------------
# WordPress core
# ---------------------------------------------------------------------------

New-Item -ItemType Directory -Force -Path $Root | Out-Null

# Windows sets the read-only attribute on folders for reasons unrelated to
# permissions, and PHP's is_writable() then reports the directory as unwritable.
# WP-CLI refuses to work in that state. Clear it before anything else.
& attrib.exe -R "$Root" /D 2>$null | Out-Null

if (Test-Path (Join-Path $Root 'wp-settings.php')) {
    Skip 'WordPress core already downloaded'
} elseif ($SkipDownload) {
    Fail 'WordPress core is missing and -SkipDownload was passed.'
} else {
    # Fetched and extracted directly rather than via `wp core download`, which
    # trips over the same is_writable() check on Windows even once the attribute
    # is cleared. Copy-Item merges into the existing wp-content, so the theme
    # already sitting in wp-content/themes/cofifi survives untouched.
    Step 'Downloading WordPress'
    $zip = Join-Path $env:TEMP 'wordpress-latest.zip'
    $tmp = Join-Path $env:TEMP ('wp-' + [guid]::NewGuid().ToString('N'))

    try {
        $ProgressPreference = 'SilentlyContinue'
        Invoke-WebRequest -Uri 'https://en-gb.wordpress.org/latest-en_GB.zip' -OutFile $zip -UseBasicParsing
    } catch {
        try {
            Invoke-WebRequest -Uri 'https://wordpress.org/latest.zip' -OutFile $zip -UseBasicParsing
        } catch {
            Fail "Could not download WordPress: $($_.Exception.Message)"
        }
    }

    Expand-Archive -Path $zip -DestinationPath $tmp -Force
    Copy-Item -Path (Join-Path $tmp 'wordpress\*') -Destination $Root -Recurse -Force
    Remove-Item $tmp -Recurse -Force -ErrorAction SilentlyContinue
    Remove-Item $zip -Force -ErrorAction SilentlyContinue

    if (-not (Test-Path (Join-Path $Root 'wp-settings.php'))) {
        Fail 'WordPress extracted but wp-settings.php is missing.'
    }
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
