<#
.SYNOPSIS
    CRM Developer Kit Generator
    Ek module ke liye isolated development environment banata hai.

.DESCRIPTION
    Yeh script aapke production CRM se ek stripped-down, runnable copy create karta hai
    jismein sirf assigned module editable hota hai. Developer locally run kar sakta hai.

.EXAMPLE
    .\generate-devkit.ps1 -ModuleName "Lead" -DeveloperName "Rahul" -DeveloperEmail "rahul@dev.com"

.EXAMPLE
    .\generate-devkit.ps1 -ModuleName "StockMarket" -DeveloperName "Amit" -OutputPath "D:\DevKits"
#>

param(
    [Parameter(Mandatory=$true, HelpMessage="Module name (e.g., Lead, StockMarket, Ekyc)")]
    [string]$ModuleName,

    [Parameter(Mandatory=$true, HelpMessage="Developer ka naam")]
    [string]$DeveloperName,

    [string]$DeveloperEmail = "developer@devkit.local",

    [string]$OutputPath = "$env:USERPROFILE\Desktop",

    [string]$DeveloperPassword = "devkit123",

    [switch]$WithGit = $true,

    [switch]$Force = $false
)

# ============================================================================
# CONFIGURATION
# ============================================================================
$CrmPath = "c:\xampp\htdocs\crm"
$DevKitName = "crm-devkit-$($ModuleName.ToLower())"
$DevKitPath = Join-Path $OutputPath $DevKitName
$ModuleSrcPath = Join-Path $CrmPath "packages\workdo\$ModuleName"
$Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"

# Colors for output
function Write-Step { param([string]$msg) Write-Host "`n>> $msg" -ForegroundColor Cyan }
function Write-Ok { param([string]$msg) Write-Host "   [OK] $msg" -ForegroundColor Green }
function Write-Skip { param([string]$msg) Write-Host "   [SKIP] $msg" -ForegroundColor Yellow }
function Write-Err { param([string]$msg) Write-Host "   [ERROR] $msg" -ForegroundColor Red }

# ============================================================================
# STEP 0: VALIDATION
# ============================================================================
Write-Host ""
Write-Host "=============================================" -ForegroundColor Magenta
Write-Host "  CRM Developer Kit Generator v1.0" -ForegroundColor Magenta
Write-Host "=============================================" -ForegroundColor Magenta
Write-Host ""
Write-Host "  Module:     $ModuleName" -ForegroundColor White
Write-Host "  Developer:  $DeveloperName <$DeveloperEmail>" -ForegroundColor White
Write-Host "  Output:     $DevKitPath" -ForegroundColor White
Write-Host ""

# Check CRM exists
if (-not (Test-Path $CrmPath)) {
    Write-Err "CRM not found at: $CrmPath"
    exit 1
}

# Check module exists
if (-not (Test-Path $ModuleSrcPath)) {
    Write-Err "Module '$ModuleName' not found at: $ModuleSrcPath"
    Write-Host "   Available modules:" -ForegroundColor Yellow
    Get-ChildItem "$CrmPath\packages\workdo" -Directory | ForEach-Object { Write-Host "     - $($_.Name)" -ForegroundColor Yellow }
    exit 1
}

# Check output directory
if (Test-Path $DevKitPath) {
    if ($Force) {
        Write-Skip "Output directory exists. Removing (--Force used)..."
        Remove-Item -Recurse -Force $DevKitPath
    } else {
        Write-Err "Output directory already exists: $DevKitPath"
        Write-Host "   Use -Force to overwrite, or choose a different -OutputPath" -ForegroundColor Yellow
        exit 1
    }
}

# ============================================================================
# HELPER FUNCTIONS
# ============================================================================

function Copy-CoreDirectory {
    param(
        [string]$RelativePath,
        [string[]]$ExcludeDirs = @(),
        [string[]]$ExcludeFiles = @()
    )

    $src = Join-Path $CrmPath $RelativePath
    $dst = Join-Path $DevKitPath $RelativePath

    if (-not (Test-Path $src)) {
        Write-Skip "$RelativePath (not found)"
        return
    }

    # Copy the directory
    Copy-Item -Path $src -Destination $dst -Recurse -Force

    # Remove excluded subdirectories
    foreach ($dir in $ExcludeDirs) {
        $excludePath = Join-Path $dst $dir
        if (Test-Path $excludePath) {
            Remove-Item -Recurse -Force $excludePath
        }
    }

    # Remove excluded files
    foreach ($file in $ExcludeFiles) {
        $excludePath = Join-Path $dst $file
        if (Test-Path $excludePath) {
            Remove-Item -Force $excludePath
        }
    }

    Write-Ok $RelativePath
}

function Copy-CoreFile {
    param([string]$RelativePath)

    $src = Join-Path $CrmPath $RelativePath
    $dst = Join-Path $DevKitPath $RelativePath

    if (-not (Test-Path $src)) {
        Write-Skip "$RelativePath (not found)"
        return
    }

    $dstDir = Split-Path $dst -Parent
    if (-not (Test-Path $dstDir)) {
        New-Item -ItemType Directory -Force -Path $dstDir | Out-Null
    }

    Copy-Item -Path $src -Destination $dst -Force
    Write-Ok $RelativePath
}

function Remove-PhpMethod {
    <#
    .SYNOPSIS
        Removes a PHP method from file content by counting braces.
    #>
    param(
        [string]$Content,
        [string]$MethodSignature  # e.g. "public function musicStudent"
    )

    $idx = $Content.IndexOf($MethodSignature)
    if ($idx -eq -1) { return $Content }

    # Go back to find the start of the line (or comment block above)
    $startIdx = $Content.LastIndexOf("`n", [Math]::Max(0, $idx - 1))
    if ($startIdx -eq -1) { $startIdx = 0 } else { $startIdx++ }

    # Find opening brace
    $braceIdx = $Content.IndexOf("{", $idx)
    if ($braceIdx -eq -1) { return $Content }

    $depth = 1
    $pos = $braceIdx + 1

    while ($depth -gt 0 -and $pos -lt $Content.Length) {
        if ($Content[$pos] -eq [char]'{') { $depth++ }
        if ($Content[$pos] -eq [char]'}') { $depth-- }
        $pos++
    }

    # Skip trailing newline
    while ($pos -lt $Content.Length -and ($Content[$pos] -eq "`r" -or $Content[$pos] -eq "`n")) {
        $pos++
    }

    return $Content.Substring(0, $startIdx) + $Content.Substring($pos)
}

function Replace-PhpMethod {
    <#
    .SYNOPSIS
        Replaces a PHP method body with new content.
    #>
    param(
        [string]$Content,
        [string]$MethodSignature,
        [string]$NewMethodBody
    )

    $idx = $Content.IndexOf($MethodSignature)
    if ($idx -eq -1) { return $Content }

    $startIdx = $Content.LastIndexOf("`n", [Math]::Max(0, $idx - 1))
    if ($startIdx -eq -1) { $startIdx = 0 } else { $startIdx++ }

    $braceIdx = $Content.IndexOf("{", $idx)
    if ($braceIdx -eq -1) { return $Content }

    $depth = 1
    $pos = $braceIdx + 1

    while ($depth -gt 0 -and $pos -lt $Content.Length) {
        if ($Content[$pos] -eq [char]'{') { $depth++ }
        if ($Content[$pos] -eq [char]'}') { $depth-- }
        $pos++
    }

    while ($pos -lt $Content.Length -and ($Content[$pos] -eq "`r" -or $Content[$pos] -eq "`n")) {
        $pos++
    }

    return $Content.Substring(0, $startIdx) + $NewMethodBody + "`n" + $Content.Substring($pos)
}

# ============================================================================
# STEP 1: CREATE DIRECTORY STRUCTURE
# ============================================================================
Write-Step "Creating directory structure..."

New-Item -ItemType Directory -Force -Path $DevKitPath | Out-Null

# Storage directories (Laravel requires these)
$storageDirs = @(
    "storage\app\public",
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\testing",
    "storage\framework\views",
    "storage\logs"
)
foreach ($dir in $storageDirs) {
    New-Item -ItemType Directory -Force -Path (Join-Path $DevKitPath $dir) | Out-Null
}
# Create empty .gitkeep files
foreach ($dir in $storageDirs) {
    $gitkeep = Join-Path $DevKitPath "$dir\.gitkeep"
    "" | Set-Content $gitkeep
}

# Create packages directory
New-Item -ItemType Directory -Force -Path (Join-Path $DevKitPath "packages\workdo") | Out-Null

Write-Ok "Directory structure created"

# ============================================================================
# STEP 2: COPY CORE DIRECTORIES
# ============================================================================
Write-Step "Copying core directories..."

Copy-CoreDirectory "app"
Copy-CoreDirectory "bootstrap"
Copy-CoreDirectory "config"
Copy-CoreDirectory "database\migrations"
Copy-CoreDirectory "database\seeders"
Copy-CoreDirectory "database\factories"
Copy-CoreDirectory "public"
Copy-CoreDirectory "resources"
Copy-CoreDirectory "routes"
Copy-CoreDirectory "stubs"

# ============================================================================
# STEP 3: COPY ASSIGNED MODULE
# ============================================================================
Write-Step "Copying module: $ModuleName..."

Copy-Item -Path $ModuleSrcPath -Destination (Join-Path $DevKitPath "packages\workdo\$ModuleName") -Recurse -Force
Write-Ok "Module '$ModuleName' copied"

# Check for child modules (from module.json)
$moduleJsonPath = Join-Path $ModuleSrcPath "module.json"
if (Test-Path $moduleJsonPath) {
    $moduleJson = Get-Content $moduleJsonPath | ConvertFrom-Json
    if ($moduleJson.child_module -and $moduleJson.child_module.Count -gt 0) {
        Write-Host "   Child modules found: $($moduleJson.child_module -join ', ')" -ForegroundColor DarkCyan
        foreach ($childMod in $moduleJson.child_module) {
            $childPath = Join-Path $CrmPath "packages\workdo\$childMod"
            if (Test-Path $childPath) {
                Copy-Item -Path $childPath -Destination (Join-Path $DevKitPath "packages\workdo\$childMod") -Recurse -Force
                Write-Ok "Child module '$childMod' copied"
            } else {
                Write-Skip "Child module '$childMod' not found (optional)"
            }
        }
    }
}

# ============================================================================
# STEP 4: COPY ROOT FILES
# ============================================================================
Write-Step "Copying root files..."

$rootFiles = @(
    "artisan",
    "package.json",
    "phpunit.xml",
    "postcss.config.js",
    "tailwind.config.js",
    "vite.config.js",
    ".editorconfig",
    ".htaccess",
    "serviceWorker.js"
)

foreach ($file in $rootFiles) {
    Copy-CoreFile $file
}

# ============================================================================
# STEP 5: SANITIZE User.php
# ============================================================================
Write-Step "Sanitizing User.php (removing missing module references)..."

$userFile = Join-Path $DevKitPath "app\Models\User.php"
$userContent = [System.IO.File]::ReadAllText($userFile)

# Remove problematic use statements
$userContent = $userContent -replace "(?m)^use Workdo\\\\MusicInstitute\\\\.*?;\r?\n", ""
$userContent = $userContent -replace "(?m)^use Workdo\\\\School\\\\.*?;\r?\n", ""
$userContent = $userContent -replace "(?m)^use Laravel\\\\Paddle\\\\Billable;\r?\n", ""

# Remove JWTSubject if jwt-auth not needed in devkit
# Keep it for now as jwt-auth is in composer.json

# Remove Billable from traits line
$userContent = $userContent -replace ", Billable;", ";"
$userContent = $userContent -replace ", Billable,", ","

# Remove methods that reference missing modules
$userContent = Remove-PhpMethod -Content $userContent -MethodSignature "public function musicStudent()"
$userContent = Remove-PhpMethod -Content $userContent -MethodSignature "public function musicTeacher()"
$userContent = Remove-PhpMethod -Content $userContent -MethodSignature "public function schoolStudent()"
$userContent = Remove-PhpMethod -Content $userContent -MethodSignature "public function admission()"

# Replace employee() method with safe version
$safeEmployeeMethod = @'
    public function employee()
    {
        return null;
    }
'@
$userContent = Replace-PhpMethod -Content $userContent -MethodSignature "public function employee()" -NewMethodBody $safeEmployeeMethod

# Simplify getAccessibleUserIds to not depend on HRM module
$safeAccessibleMethod = @'
    private static $accessibleUserIdsCache = [];

    public function getAccessibleUserIds()
    {
        $cacheKey = $this->id . '_' . $this->active_workspace;
        if (isset(self::$accessibleUserIdsCache[$cacheKey])) {
            return self::$accessibleUserIdsCache[$cacheKey];
        }

        if ($this->type == 'company' || $this->visibility_level == 'all') {
            $ids = User::where('workspace_id', $this->active_workspace)->pluck('id')->toArray();
            self::$accessibleUserIdsCache[$cacheKey] = $ids;
            return $ids;
        }

        $user_ids = [$this->id];

        // Accessible Departments (simplified - no HRM dependency)
        // Accessible Users
        if (!empty($this->accessible_users)) {
            $user_ids = array_merge($user_ids, $this->accessible_users);
        }

        $result = array_unique($user_ids);
        self::$accessibleUserIdsCache[$cacheKey] = $result;
        return $result;
    }
'@
$userContent = Replace-PhpMethod -Content $userContent -MethodSignature "private static `$accessibleUserIdsCache" -NewMethodBody $safeAccessibleMethod

[System.IO.File]::WriteAllText($userFile, $userContent)
Write-Ok "User.php sanitized"

# ============================================================================
# STEP 6: SANITIZE WorkSpace.php
# ============================================================================
Write-Step "Sanitizing WorkSpace.php..."

$wsFile = Join-Path $DevKitPath "app\Models\WorkSpace.php"
$wsContent = [System.IO.File]::ReadAllText($wsFile)

# Remove Taskly reference
$wsContent = $wsContent -replace "(?m)^use Workdo\\\\Taskly\\\\.*?;\r?\n", ""

# Remove projects() method
$wsContent = Remove-PhpMethod -Content $wsContent -MethodSignature "public function projects()"

[System.IO.File]::WriteAllText($wsFile, $wsContent)
Write-Ok "WorkSpace.php sanitized"

# ============================================================================
# STEP 7: GENERATE SIMPLIFIED EventServiceProvider.php
# ============================================================================
Write-Step "Generating simplified EventServiceProvider.php..."

$espContent = @'
<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     * Simplified for DevKit — payment gateway events removed.
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
'@

$espFile = Join-Path $DevKitPath "app\Providers\EventServiceProvider.php"
[System.IO.File]::WriteAllText($espFile, $espContent)
Write-Ok "EventServiceProvider.php simplified"

# ============================================================================
# STEP 8: MODIFY composer.json
# ============================================================================
Write-Step "Cleaning composer.json (removing unnecessary packages)..."

$composerFile = Join-Path $DevKitPath "composer.json"
$composerSrc = Join-Path $CrmPath "composer.json"
$composer = Get-Content $composerSrc -Raw | ConvertFrom-Json

# Packages to remove (payment gateways, SMS, cloud services)
$packagesToRemove = @(
    "anandsiddharth/laravel-paytm-wallet",
    "authorizenet/authorizenet",
    "braintree/braintree_php",
    "coingate/coingate-php",
    "dcblogdev/laravel-box",
    "dcblogdev/laravel-dropbox",
    "dipesh79/laravel-phonepe",
    "fedapay/fedapay-php",
    "kavenegar/php",
    "laravel/cashier-paddle",
    "mailchimp/marketing",
    "mediaburst/clockworksms",
    "melipayamak/php",
    "midtrans/midtrans-php",
    "mollie/mollie-api-php",
    "munafio/chatify",
    "orhanerday/open-ai",
    "paypayopa/php-sdk",
    "salla/zatca",
    "smsgatewayme/client",
    "socialiteproviders/microsoft",
    "spatie/laravel-google-calendar",
    "srmklive/paypal",
    "stripe/stripe-php",
    "twilio/sdk",
    "tzsk/sms",
    "vonage/client",
    "webklex/laravel-imap",
    "xendit/xendit-php",
    "yoomoney/yookassa-sdk-php"
)

foreach ($pkg in $packagesToRemove) {
    if ($composer.require.PSObject.Properties[$pkg]) {
        $composer.require.PSObject.Properties.Remove($pkg)
    }
}

# Update project name
$composer.name = "stockology/crm-devkit-$($ModuleName.ToLower())"
$composer.description = "CRM DevKit for $ModuleName module development"

$composer | ConvertTo-Json -Depth 10 | Set-Content $composerFile -Encoding UTF8
Write-Ok "composer.json cleaned (removed $($packagesToRemove.Count) unnecessary packages)"

# ============================================================================
# STEP 9: GENERATE .env FILE
# ============================================================================
Write-Step "Generating .env file..."

$dbName = "crm_devkit_$($ModuleName.ToLower())"

$envContent = @"
APP_NAME="CRM DevKit - $ModuleName"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Kolkata
APP_URL=http://localhost:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$dbName
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file
CACHE_PREFIX=devkit_

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="devkit@example.com"
MAIL_FROM_NAME="CRM DevKit"

VITE_APP_NAME="CRM DevKit"
"@

$envFile = Join-Path $DevKitPath ".env"
[System.IO.File]::WriteAllText($envFile, $envContent)
Write-Ok ".env generated (DB: $dbName)"

# ============================================================================
# STEP 10: GENERATE DevKitSeeder.php
# ============================================================================
Write-Step "Generating DevKitSeeder.php..."

$seederContent = @"
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\WorkSpace;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\AddOn;
use App\Models\userActiveModule;
use App\Models\Plan;
use App\Models\Language;
use App\Models\Currency;

class DevKitSeeder extends Seeder
{
    /**
     * CRM DevKit Seeder
     * Creates test data for module development.
     * Module: $ModuleName
     * Generated: $Timestamp
     */
    public function run(): void
    {
        `$this->command->info('=== CRM DevKit Seeder ===');

        // -----------------------------------------------
        // 1. Create Plans
        // -----------------------------------------------
        `$plan = Plan::create([
            'name'                => 'DevKit Plan',
            'price'               => 0,
            'duration'            => 'Unlimited',
            'number_of_user'      => -1,
            'number_of_workspace' => -1,
            'is_free_plan'        => 1,
            'description'         => 'Development plan for DevKit',
        ]);
        `$this->command->info('Plan created.');

        // -----------------------------------------------
        // 2. Create Super Admin
        // -----------------------------------------------
        `$superAdmin = User::create([
            'name'             => 'Super Admin',
            'email'            => 'admin@devkit.local',
            'password'         => Hash::make('admin123'),
            'type'             => 'super admin',
            'active_workspace' => 0,
            'active_plan'      => `$plan->id,
            'total_user'       => -1,
            'total_workspace'  => -1,
            'lang'             => 'en',
            'is_enable_login'  => 1,
            'email_verified_at'=> now(),
            'created_by'       => 0,
        ]);

        // Super admin role
        `$superAdminRole = Role::create([
            'name'       => 'super admin',
            'guard_name' => 'web',
            'module'     => 'Base',
            'created_by' => `$superAdmin->id,
        ]);
        `$superAdmin->addRole(`$superAdminRole);
        `$this->command->info('Super Admin created: admin@devkit.local / admin123');

        // -----------------------------------------------
        // 3. Create Workspace
        // -----------------------------------------------
        `$workspace = WorkSpace::create([
            'name'       => 'DevKit Workspace',
            'created_by' => 2, // company user (created next)
            'is_disable' => 1,
        ]);
        `$this->command->info('Workspace created.');

        // -----------------------------------------------
        // 4. Create Company User (main developer account)
        // -----------------------------------------------
        `$company = User::create([
            'name'             => '$DeveloperName',
            'email'            => '$DeveloperEmail',
            'password'         => Hash::make('$DeveloperPassword'),
            'type'             => 'company',
            'active_workspace' => `$workspace->id,
            'workspace_id'     => `$workspace->id,
            'active_plan'      => `$plan->id,
            'total_user'       => -1,
            'total_workspace'  => -1,
            'lang'             => 'en',
            'is_enable_login'  => 1,
            'email_verified_at'=> now(),
            'created_by'       => `$superAdmin->id,
        ]);

        // Company role
        `$companyRole = Role::create([
            'name'       => 'company',
            'guard_name' => 'web',
            'module'     => 'Base',
            'created_by' => `$company->id,
        ]);
        `$company->addRole(`$companyRole);

        // Create staff & client roles
        `$company->MakeRole();

        `$this->command->info("Developer account created: $DeveloperEmail / $DeveloperPassword");

        // -----------------------------------------------
        // 5. Create Staff User (for testing)
        // -----------------------------------------------
        `$staff = User::create([
            'name'             => 'Test Staff',
            'email'            => 'staff@devkit.local',
            'password'         => Hash::make('staff123'),
            'type'             => 'staff',
            'active_workspace' => `$workspace->id,
            'workspace_id'     => `$workspace->id,
            'lang'             => 'en',
            'is_enable_login'  => 1,
            'email_verified_at'=> now(),
            'created_by'       => `$company->id,
        ]);
        `$staffRole = Role::where('name', 'staff')->where('created_by', `$company->id)->first();
        if (`$staffRole) {
            `$staff->addRole(`$staffRole);
        }
        `$this->command->info('Staff account created: staff@devkit.local / staff123');

        // -----------------------------------------------
        // 6. Enable Module
        // -----------------------------------------------
        `$addon = AddOn::create([
            'module'     => '$ModuleName',
            'name'       => '$ModuleName Module',
            'is_enable'  => 1,
        ]);

        userActiveModule::create([
            'user_id' => `$company->id,
            'module'  => '$ModuleName',
        ]);

        `$this->command->info("Module '$ModuleName' enabled.");

        // -----------------------------------------------
        // 7. Company Settings
        // -----------------------------------------------
        User::CompanySetting(`$company->id, `$workspace->id);

        `$extraSettings = [
            'storage_setting'            => 'local',
            'local_storage_validation'   => 'jpeg,jpg,png,svg,zip,txt,gif,docx,pdf,xlsx',
            'local_storage_max_upload_size' => '20480',
            'company_name'               => 'DevKit Company',
            'title_text'                 => 'CRM DevKit',
            'footer_text'               => 'CRM DevKit - Development Environment',
            'color'                      => 'theme-1',
            'cust_darklayout'            => 'off',
            'site_rtl'                   => 'off',
            'site_transparent'           => 'on',
        ];

        foreach (`$extraSettings as `$key => `$value) {
            Setting::updateOrInsert(
                ['key' => `$key, 'workspace' => `$workspace->id, 'created_by' => `$company->id],
                ['value' => `$value]
            );
        }

        // Admin settings
        foreach (`$extraSettings as `$key => `$value) {
            Setting::updateOrInsert(
                ['key' => `$key, 'workspace' => 0, 'created_by' => `$superAdmin->id],
                ['value' => `$value]
            );
        }

        `$this->command->info('Settings configured.');

        // -----------------------------------------------
        // 8. Default Language & Currency
        // -----------------------------------------------
        Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'status' => 1]
        );

        Currency::updateOrCreate(
            ['name' => 'INR'],
            ['symbol' => '₹', 'code' => 'INR']
        );

        `$this->command->info('Language & Currency set.');

        // -----------------------------------------------
        // 9. Create Module Permissions
        // -----------------------------------------------
        `$modulePerms = `$this->getModulePermissions();
        foreach (`$modulePerms as `$perm) {
            Permission::firstOrCreate([
                'name'       => `$perm,
                'guard_name' => 'web',
                'module'     => '$ModuleName',
            ]);
        }

        // Give all module permissions to company role
        `$allPerms = Permission::where('module', '$ModuleName')->get();
        foreach (`$allPerms as `$perm) {
            `$companyRole->givePermission(`$perm);
            if (`$staffRole) {
                `$staffRole->givePermission(`$perm);
            }
        }
        `$this->command->info('Permissions created and assigned.');

        // -----------------------------------------------
        // DONE
        // -----------------------------------------------
        `$this->command->info('');
        `$this->command->info('=================================');
        `$this->command->info('  DevKit Setup Complete!');
        `$this->command->info('=================================');
        `$this->command->info("  Login: $DeveloperEmail / $DeveloperPassword");
        `$this->command->info('  Admin: admin@devkit.local / admin123');
        `$this->command->info('  Staff: staff@devkit.local / staff123');
        `$this->command->info('=================================');
    }

    /**
     * Module-specific permissions.
     * Add more permissions as needed for your module.
     */
    private function getModulePermissions(): array
    {
        // Common permission patterns for CRM modules
        `$baseName = strtolower('$ModuleName');
        `$perms = [
            // These are common patterns - the module's listener may add more
            "{\$baseName} manage",
        ];

        return `$perms;
    }
}
"@

$seederFile = Join-Path $DevKitPath "database\seeders\DevKitSeeder.php"
[System.IO.File]::WriteAllText($seederFile, $seederContent)

# Also update DatabaseSeeder.php to call DevKitSeeder
$dbSeederContent = @'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DevKitSeeder::class,
        ]);
    }
}
'@
$dbSeederFile = Join-Path $DevKitPath "database\seeders\DatabaseSeeder.php"
[System.IO.File]::WriteAllText($dbSeederFile, $dbSeederContent)

Write-Ok "DevKitSeeder.php generated"

# ============================================================================
# STEP 11: GENERATE SMART .gitignore
# ============================================================================
Write-Step "Generating smart .gitignore..."

$gitignoreContent = @"
# =============================================================
# CRM DevKit .gitignore
# Module: $ModuleName
# Purpose: Track ONLY module changes, ignore everything else
# =============================================================

# ---------- Dependencies ----------
/vendor/
/node_modules/

# ---------- Environment ----------
.env
.env.backup
.env.production

# ---------- Laravel Generated ----------
/storage/*.key
/storage/framework/cache/data/*
/storage/framework/sessions/*
/storage/framework/views/*
/storage/logs/*
!storage/logs/.gitkeep
!storage/framework/cache/.gitkeep
!storage/framework/sessions/.gitkeep
!storage/framework/views/.gitkeep

# ---------- Compiled / Build ----------
/public/build/
/public/hot
/public/storage
npm-debug.log
yarn-error.log

# ---------- IDE ----------
/.idea/
/.vscode/
*.swp
*.swo
.DS_Store
Thumbs.db

# ---------- Testing ----------
.phpunit.result.cache
/coverage/

# ---------- DevKit Specific ----------
# The core CRM files are NOT meant to be edited.
# Only edit files inside packages/workdo/$ModuleName/
# If you accidentally edit core files, use:
#   git checkout -- <file>
"@

$gitignoreFile = Join-Path $DevKitPath ".gitignore"
[System.IO.File]::WriteAllText($gitignoreFile, $gitignoreContent)
Write-Ok ".gitignore generated"

# ============================================================================
# STEP 12: GENERATE DEVELOPER_README.md
# ============================================================================
Write-Step "Generating DEVELOPER_README.md..."

$cb = '```'  # code block marker

$readmeContent = @"
# CRM DevKit - $ModuleName Module

**Developer:** $DeveloperName ($DeveloperEmail)
**Generated:** $Timestamp
**Module:** $ModuleName

---

## Quick Setup (5 minutes)

### Prerequisites
- XAMPP / Laragon (PHP 8.2+, MySQL, Apache)
- Composer (https://getcomposer.org)
- Git

### Step 1: Install Dependencies
${cb}bash
cd $DevKitName
composer install
${cb}

### Step 2: Create Database
Open phpMyAdmin (http://localhost/phpmyadmin) and create a new database:
- Database name: $dbName
- Collation: utf8mb4_unicode_ci

### Step 3: Generate App Key
${cb}bash
php artisan key:generate
${cb}

### Step 4: Run Migrations and Seed Data
${cb}bash
php artisan migrate --seed
${cb}

### Step 5: Start the Server
${cb}bash
php artisan serve
${cb}

### Step 6: Login
Open http://localhost:8000 and login with:

| Role | Email | Password |
|------|-------|----------|
| Developer (Company) | $DeveloperEmail | $DeveloperPassword |
| Admin | admin@devkit.local | admin123 |
| Staff | staff@devkit.local | staff123 |

---

## Your Working Directory

ONLY edit files inside this folder:

    packages/workdo/$ModuleName/
    +-- composer.json          (Module dependencies)
    +-- module.json            (Module metadata)
    +-- src/
        +-- Database/          (Migrations and seeders)
        +-- Entities/          (Models - Eloquent)
        +-- Events/            (Event classes)
        +-- Http/Controllers/  (Your controllers)
        +-- Listeners/         (Event listeners)
        +-- Providers/         (Service providers)
        +-- Resources/views/   (Blade templates)
        +-- Routes/            (web.php, api.php)

WARNING: DO NOT edit files outside packages/workdo/$ModuleName/

---

## Common Helper Functions

| Function | Purpose | Returns |
|----------|---------|---------|
| creatorId() | Company owner ka ID | int |
| getActiveWorkSpace() | Active workspace ID | int |
| company_setting(key) | Company setting value | mixed |
| admin_setting(key) | Admin setting value | mixed |
| module_is_active(name) | Module active check | bool |
| upload_file(req,key,name,path) | File upload | array |

---

## Submitting Your Work

### Using Git
${cb}bash
git status
git add packages/workdo/$ModuleName/
git commit -m "feat($ModuleName): description of change"
git push origin main
${cb}

### Using ZIP
1. ZIP the packages/workdo/$ModuleName/ folder
2. Send the ZIP file to the admin

---

## Rules
1. Edit files inside packages/workdo/$ModuleName/ only
2. Create new files inside the module directory
3. Add new migrations in src/Database/Migrations/
4. Do NOT edit core files (app/, config/, resources/layouts/)
5. Do NOT modify root composer.json
6. Do NOT commit .env file

---

## Troubleshooting

### Class not found error
${cb}bash
composer dump-autoload
php artisan cache:clear
php artisan config:clear
php artisan view:clear
${cb}

### Database migration error
${cb}bash
php artisan migrate:fresh --seed
${cb}

### Permission/Role issues
Login as admin (admin@devkit.local) and check the module settings.
"@

$readmeFile = Join-Path $DevKitPath "DEVELOPER_README.md"
[System.IO.File]::WriteAllText($readmeFile, $readmeContent)
Write-Ok "DEVELOPER_README.md generated"

# ============================================================================
# STEP 13: GENERATE MERGE_BACK_GUIDE.md (for Admin)
# ============================================================================
Write-Step "Generating MERGE_BACK_GUIDE.md..."

$mergeGuideContent = @"
# Merge Back Guide - Admin Reference

This guide is for YOU (the CRM admin) to merge developer work back into production.

## Method 1: Git-based (Recommended)

${cb}bash
cd c:\xampp\htdocs\crm

# Backup current module
xcopy "packages\workdo\$ModuleName" "packages\workdo\${ModuleName}_backup" /E /I /Y

# Copy developer module
xcopy "<developer-devkit-path>\packages\workdo\$ModuleName" "packages\workdo\$ModuleName" /E /I /Y

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Run any new migrations
php artisan migrate

# Test
php artisan serve
${cb}

## Method 2: ZIP-based

1. Developer sends ZIP of packages/workdo/$ModuleName/
2. Extract and replace in production
3. Run php artisan migrate for new migrations
4. Clear all caches

## Pre-merge Checklist

- [ ] Review all changed files
- [ ] Check for hardcoded URLs or paths
- [ ] Verify no debug code left (dd, var_dump, console.log)
- [ ] Check new migrations are safe for production data
- [ ] Test on staging before production
- [ ] Backup production database before deploying

## Review Developer Changes

${cb}bash
cd <devkit-path>
git log --oneline
git diff --stat HEAD~5
git diff HEAD~5 -- packages/workdo/$ModuleName/
${cb}
"@

$mergeGuideFile = Join-Path $DevKitPath "MERGE_BACK_GUIDE.md"
[System.IO.File]::WriteAllText($mergeGuideFile, $mergeGuideContent)
Write-Ok "MERGE_BACK_GUIDE.md generated"

# ============================================================================
# STEP 14: INITIALIZE GIT
# ============================================================================
if ($WithGit) {
    Write-Step "Initializing Git repository..."

    Push-Location $DevKitPath
    try {
        & git init 2>$null | Out-Null
        & git add -A 2>$null | Out-Null
        & git commit -m "Initial DevKit setup for $ModuleName module - Developer: $DeveloperName" --quiet 2>$null | Out-Null
        Write-Ok "Git repository initialized with initial commit"
    } catch {
        Write-Skip "Git initialization skipped (git not available)"
    }
    Pop-Location
}

# ============================================================================
# STEP 15: CLEANUP — Remove files developer shouldn't have
# ============================================================================
Write-Step "Final cleanup..."

# Remove any leftover debug/test scripts that might have been copied
$cleanupPatterns = @(
    "debug_*.php", "fix_*.php", "test_*.php", "check_*.php",
    "emergency_*.php", "quick_*.php", "setup_*.php",
    "*.sql", "*.pdf", "*.bat"
)

# These would only be in root, which we didn't copy individual PHP files for
# But clean up any that might be in public/ or other dirs
$publicCleanup = Join-Path $DevKitPath "public"
Get-ChildItem $publicCleanup -Filter "*.sql" -ErrorAction SilentlyContinue | Remove-Item -Force

Write-Ok "Cleanup complete"

# ============================================================================
# SUMMARY
# ============================================================================
Write-Host ""
Write-Host "=============================================" -ForegroundColor Green
Write-Host "  ✅ DevKit Generated Successfully!" -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Location:  $DevKitPath" -ForegroundColor White
Write-Host "  Module:    $ModuleName" -ForegroundColor White
Write-Host "  Developer: $DeveloperName <$DeveloperEmail>" -ForegroundColor White
Write-Host ""
Write-Host "  Login Credentials:" -ForegroundColor Cyan
Write-Host "  ─────────────────────────────────────────" -ForegroundColor DarkGray
Write-Host "  Developer: $DeveloperEmail / $DeveloperPassword" -ForegroundColor White
Write-Host "  Admin:     admin@devkit.local / admin123" -ForegroundColor DarkGray
Write-Host "  Staff:     staff@devkit.local / staff123" -ForegroundColor DarkGray
Write-Host ""
Write-Host "  Next Steps for Developer:" -ForegroundColor Yellow
Write-Host "  1. cd $DevKitPath" -ForegroundColor White
Write-Host "  2. composer install" -ForegroundColor White
Write-Host "  3. Create database: $dbName" -ForegroundColor White
Write-Host "  4. php artisan key:generate" -ForegroundColor White
Write-Host "  5. php artisan migrate --seed" -ForegroundColor White
Write-Host "  6. php artisan serve" -ForegroundColor White
Write-Host ""
Write-Host "  Files included:" -ForegroundColor Cyan
$totalFiles = (Get-ChildItem $DevKitPath -Recurse -File).Count
$totalSizeMB = [math]::Round((Get-ChildItem $DevKitPath -Recurse -File | Measure-Object -Property Length -Sum).Sum / 1MB, 1)
Write-Host "  $totalFiles files, ${totalSizeMB} MB total" -ForegroundColor White
Write-Host ""
Write-Host "=============================================" -ForegroundColor Green
