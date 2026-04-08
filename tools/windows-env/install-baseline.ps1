[CmdletBinding()]
param(
    [ValidateSet("Core", "Extended", "Full")]
    [string]$Profile = "Core",
    [switch]$UpgradeIfInstalled,
    [switch]$DryRun,
    [switch]$PrepareStarterProjects,
    [string]$StartersDir = ".\\starters"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Test-WingetAvailable {
    return [bool](Get-Command winget -ErrorAction SilentlyContinue)
}

function Test-ProgramInstalled {
    param(
        [Parameter(Mandatory = $true)][string[]]$Patterns
    )

    $paths = @(
        "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*",
        "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*",
        "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*"
    )

    foreach ($path in $paths) {
        $items = Get-ItemProperty -Path $path -ErrorAction SilentlyContinue
        foreach ($item in $items) {
            if (-not $item.DisplayName) {
                continue
            }

            foreach ($pattern in $Patterns) {
                if ($item.DisplayName -match $pattern) {
                    return $true
                }
            }
        }
    }

    return $false
}

function Install-WithWinget {
    param(
        [Parameter(Mandatory = $true)][string]$Id,
        [Parameter(Mandatory = $true)][string]$Name
    )

    $args = @(
        "install",
        "--id", $Id,
        "--exact",
        "--accept-package-agreements",
        "--accept-source-agreements",
        "--disable-interactivity"
    )

    if ($UpgradeIfInstalled) {
        $args = @(
            "upgrade",
            "--id", $Id,
            "--exact",
            "--accept-package-agreements",
            "--accept-source-agreements",
            "--disable-interactivity"
        )
    }

    if ($DryRun) {
        Write-Host "[DRY-RUN] winget $($args -join ' ')"
        return 0
    }

    Write-Host "Installing: $Name ($Id)"
    & winget @args
    return $LASTEXITCODE
}

function Test-CommandAvailable {
    param([Parameter(Mandatory = $true)][string]$Command)
    return [bool](Get-Command $Command -ErrorAction SilentlyContinue)
}

function Invoke-ExternalCommand {
    param(
        [Parameter(Mandatory = $true)][string]$Title,
        [Parameter(Mandatory = $true)][string]$File,
        [Parameter(Mandatory = $true)][string[]]$Arguments
    )

    if ($DryRun) {
        Write-Host "[DRY-RUN] $File $($Arguments -join ' ')"
        return 0
    }

    Write-Host $Title
    & $File @Arguments
    return $LASTEXITCODE
}

function Install-NpmGlobal {
    param(
        [Parameter(Mandatory = $true)][string]$Package,
        [string]$Version
    )

    if (-not (Test-CommandAvailable -Command "npm")) {
        Write-Warning "npm not found, skip global npm package: $Package"
        return 1
    }

    $pkgText = if ([string]::IsNullOrWhiteSpace($Version)) { $Package } else { "$Package@$Version" }
    $args = @("install", "-g", $pkgText)
    return Invoke-ExternalCommand -Title "Installing npm global package: $pkgText" -File "npm" -Arguments $args
}

function Install-ComposerGlobal {
    param(
        [Parameter(Mandatory = $true)][string]$Package,
        [string]$Version
    )

    if (-not (Test-CommandAvailable -Command "composer")) {
        Write-Warning "composer not found, skip composer package: $Package"
        return 1
    }

    $pkgText = if ([string]::IsNullOrWhiteSpace($Version)) { $Package } else { "$Package:$Version" }
    $args = @("global", "require", $pkgText, "--no-interaction")
    return Invoke-ExternalCommand -Title "Installing composer global package: $pkgText" -File "composer" -Arguments $args
}

function Ensure-StarterProject {
    param(
        [Parameter(Mandatory = $true)][string]$Name,
        [Parameter(Mandatory = $true)][string]$Path,
        [Parameter(Mandatory = $true)][string]$Runner,
        [Parameter(Mandatory = $true)][string[]]$Args
    )

    if (Test-Path -Path $Path) {
        Write-Host "Skip starter (already exists): $Name"
        return 0
    }

    return Invoke-ExternalCommand -Title "Creating starter project: $Name" -File $Runner -Arguments $Args
}

if (-not (Test-WingetAvailable)) {
    throw "winget not found. Install App Installer from Microsoft Store first."
}

$corePackages = @(
    @{ Name = "Google Chrome"; Id = "Google.Chrome"; Detect = @("Google Chrome") },
    @{ Name = "Visual Studio Code"; Id = "Microsoft.VisualStudioCode"; Detect = @("Visual Studio Code") },
    @{ Name = "Node.js LTS"; Id = "OpenJS.NodeJS.LTS"; Detect = @("Node.js") },
    @{ Name = "PHP"; Id = "PHP.PHP"; Detect = @("PHP") },
    @{ Name = "Composer"; Id = "Composer.Composer"; Detect = @("Composer") },
    @{ Name = "Postman"; Id = "Postman.Postman"; Detect = @("Postman") }
)

$extendedPackages = @(
    @{ Name = "FileZilla Client"; Id = "FileZilla.FileZilla"; Detect = @("FileZilla") },
    @{ Name = "Firefox Developer Edition"; Id = "Mozilla.Firefox.DeveloperEdition"; Detect = @("Firefox Developer Edition") },
    @{ Name = "WinSCP"; Id = "WinSCP.WinSCP"; Detect = @("WinSCP") },
    @{ Name = "PuTTY"; Id = "PuTTY.PuTTY"; Detect = @("PuTTY") },
    @{ Name = "Sublime Text"; Id = "SublimeHQ.SublimeText.4"; Detect = @("Sublime Text") },
    @{ Name = "JetBrains PhpStorm"; Id = "JetBrains.PhpStorm"; Detect = @("PhpStorm") },
    @{ Name = "MySQL Workbench"; Id = "Oracle.MySQLWorkbench"; Detect = @("MySQL Workbench") },
    @{ Name = "GIMP"; Id = "GIMP.GIMP"; Detect = @("GIMP") },
    @{ Name = "Notepad++"; Id = "Notepad++.Notepad++"; Detect = @("Notepad\+\+") },
    @{ Name = "7-Zip"; Id = "7zip.7zip"; Detect = @("7-Zip") },
    @{ Name = "Zeal"; Id = "Zealdocs.Zeal"; Detect = @("Zeal") },
    @{ Name = "Eclipse Installer"; Id = "EclipseFoundation.EclipseInstaller"; Detect = @("Eclipse") }
)

$selected = @($corePackages)
if ($Profile -eq "Extended" -or $Profile -eq "Full") {
    $selected += $extendedPackages
}

$summary = New-Object System.Collections.Generic.List[object]

foreach ($pkg in $selected) {
    $alreadyInstalled = Test-ProgramInstalled -Patterns $pkg.Detect

    if ($alreadyInstalled -and -not $UpgradeIfInstalled) {
        Write-Host "Skip (already installed): $($pkg.Name)"
        $summary.Add([PSCustomObject]@{ Name = $pkg.Name; Id = $pkg.Id; Action = "skipped"; ExitCode = 0 })
        continue
    }

    $exitCode = Install-WithWinget -Id $pkg.Id -Name $pkg.Name
    $action = if ($exitCode -eq 0) { "installed" } else { "failed" }

    $summary.Add([PSCustomObject]@{ Name = $pkg.Name; Id = $pkg.Id; Action = $action; ExitCode = $exitCode })

    if ($exitCode -ne 0) {
        Write-Warning "Install failed: $($pkg.Name) (exit code $exitCode)"
    }
}

Write-Host ""
Write-Host "=== Install Summary ==="
$summary | Format-Table -AutoSize

if ($Profile -eq "Full") {
    Write-Host ""
    Write-Host "=== Toolchain Provision (Full) ==="

    $toolchain = New-Object System.Collections.Generic.List[object]

    $npmGlobals = @(
        @{ Name = "@angular/cli"; Version = "17.2.1" },
        @{ Name = "@vue/cli"; Version = "5.0.8" },
        @{ Name = "angular"; Version = "1.8.3" },
        @{ Name = "create-react-app"; Version = "5.0.1" },
        @{ Name = "cypress"; Version = "13.6.4" },
        @{ Name = "testcafe"; Version = "3.5.0" }
    )

    foreach ($pkg in $npmGlobals) {
        $exitCode = Install-NpmGlobal -Package $pkg.Name -Version $pkg.Version
        $toolchain.Add([PSCustomObject]@{
            Type = "npm-global"
            Name = if ([string]::IsNullOrWhiteSpace($pkg.Version)) { $pkg.Name } else { "$($pkg.Name)@$($pkg.Version)" }
            ExitCode = $exitCode
            Action = if ($exitCode -eq 0) { "ok" } else { "failed" }
        })
    }

    $composerGlobals = @(
        @{ Name = "laravel/installer"; Version = "^5" },
        @{ Name = "yiisoft/yii2-composer"; Version = "^2" }
    )

    foreach ($pkg in $composerGlobals) {
        $exitCode = Install-ComposerGlobal -Package $pkg.Name -Version $pkg.Version
        $toolchain.Add([PSCustomObject]@{
            Type = "composer-global"
            Name = "$($pkg.Name):$($pkg.Version)"
            ExitCode = $exitCode
            Action = if ($exitCode -eq 0) { "ok" } else { "failed" }
        })
    }

    if ($PrepareStarterProjects) {
        if (-not (Test-Path -Path $StartersDir)) {
            if ($DryRun) {
                Write-Host "[DRY-RUN] New-Item -ItemType Directory -Path $StartersDir"
            }
            else {
                New-Item -ItemType Directory -Path $StartersDir | Out-Null
            }
        }

        $laravelTarget = Join-Path $StartersDir "laravel-10"
        $yiiTarget = Join-Path $StartersDir "yii2-basic"
        $ciTarget = Join-Path $StartersDir "codeigniter4"
        $reactTarget = Join-Path $StartersDir "react-blank"

        $starterSteps = @(
            @{ Name = "Laravel 10 starter"; Path = $laravelTarget; Runner = "composer"; Args = @("create-project", "laravel/laravel", $laravelTarget, "10.3.*", "--no-interaction") },
            @{ Name = "Yii 2 starter"; Path = $yiiTarget; Runner = "composer"; Args = @("create-project", "yiisoft/yii2-app-basic", $yiiTarget, "2.0.49", "--no-interaction") },
            @{ Name = "CodeIgniter 4 starter"; Path = $ciTarget; Runner = "composer"; Args = @("create-project", "codeigniter4/appstarter", $ciTarget, "4.4.5", "--no-interaction") },
            @{ Name = "React blank starter"; Path = $reactTarget; Runner = "npx"; Args = @("-y", "create-react-app@5.0.1", $reactTarget) }
        )

        foreach ($step in $starterSteps) {
            $exitCode = Ensure-StarterProject -Name $step.Name -Path $step.Path -Runner $step.Runner -Args $step.Args
            $toolchain.Add([PSCustomObject]@{
                Type = "starter"
                Name = $step.Name
                ExitCode = $exitCode
                Action = if ($exitCode -eq 0) { "ok" } else { "failed" }
            })
        }

        $frontendLibsTarget = Join-Path $StartersDir "frontend-libs"
        if (-not (Test-Path -Path $frontendLibsTarget)) {
            if ($DryRun) {
                Write-Host "[DRY-RUN] New-Item -ItemType Directory -Path $frontendLibsTarget"
            }
            else {
                New-Item -ItemType Directory -Path $frontendLibsTarget | Out-Null
            }
        }

        if (Test-CommandAvailable -Command "npm") {
            $exitInit = Invoke-ExternalCommand -Title "Initializing frontend lib workspace" -File "npm" -Arguments @("init", "-y", "--prefix", $frontendLibsTarget)
            $toolchain.Add([PSCustomObject]@{ Type = "starter"; Name = "frontend-libs init"; ExitCode = $exitInit; Action = if ($exitInit -eq 0) { "ok" } else { "failed" } })

            $exitLib = Invoke-ExternalCommand -Title "Installing Bootstrap/Foundation libraries" -File "npm" -Arguments @("install", "--prefix", $frontendLibsTarget, "bootstrap@5.3.2", "foundation-sites@6.9.5")
            $toolchain.Add([PSCustomObject]@{ Type = "starter"; Name = "bootstrap+foundation"; ExitCode = $exitLib; Action = if ($exitLib -eq 0) { "ok" } else { "failed" } })
        }
    }

    $toolchain | Format-Table -AutoSize

    foreach ($r in $toolchain) {
        if ($r.Action -eq "failed") {
            $summary.Add([PSCustomObject]@{ Name = $r.Name; Id = $r.Type; Action = "failed"; ExitCode = $r.ExitCode })
        }
    }
}

Write-Host ""
Write-Host "Manual follow-up items (not reliably one-click via winget in all rooms):"
Write-Host "- Eclipse Installer only installs launcher; choose 'Eclipse for PHP Developers' package in installer"
Write-Host "- Zeal docs sets (PHP/JavaScript/CSS/HTML/Vue/React/Angular/Laravel/Yii/MySQL/jQuery/Bootstrap 5) need import in Zeal app"
Write-Host "- Office 2019 and Boshiamy input method usually require room image/manual installer"

$failed = ($summary | Where-Object { $_.Action -eq "failed" }).Count
if ($failed -gt 0) {
    exit 1
}
