[CmdletBinding()]
param(
    [ValidateSet("Core", "Extended", "Full")]
    [string]$Profile = "Core",
    [string]$OutputDir = ".\reports"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Get-InstalledPrograms {
    $paths = @(
        "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*",
        "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*",
        "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*"
    )

    $all = foreach ($path in $paths) {
        Get-ItemProperty -Path $path -ErrorAction SilentlyContinue |
            Where-Object { $_.DisplayName } |
            Select-Object DisplayName, DisplayVersion
    }

    return $all
}

function Find-ProgramVersion {
    param(
        [Parameter(Mandatory = $true)][array]$Programs,
        [Parameter(Mandatory = $true)][string[]]$NamePatterns
    )

    foreach ($program in $Programs) {
        foreach ($pattern in $NamePatterns) {
            if ($program.DisplayName -match $pattern) {
                return [PSCustomObject]@{
                    found = $true
                    name = $program.DisplayName
                    version = $program.DisplayVersion
                }
            }
        }
    }

    return [PSCustomObject]@{
        found = $false
        name = $null
        version = $null
    }
}

function Get-CliVersion {
    param(
        [Parameter(Mandatory = $true)][string]$Command,
        [Parameter(Mandatory = $true)][scriptblock]$Script
    )

    if (-not (Get-Command $Command -ErrorAction SilentlyContinue)) {
        return [PSCustomObject]@{
            found = $false
            version = $null
            raw = $null
        }
    }

    try {
        $raw = & $Script 2>&1 | Out-String
        $version = [regex]::Match($raw, '(\d+\.\d+(?:\.\d+)*)').Value
        if ([string]::IsNullOrWhiteSpace($version)) {
            $version = $null
        }

        return [PSCustomObject]@{
            found = $true
            version = $version
            raw = $raw.Trim()
        }
    }
    catch {
        return [PSCustomObject]@{
            found = $true
            version = $null
            raw = $_.Exception.Message
        }
    }
}

function ConvertTo-Version {
    param([string]$Text)

    if ([string]::IsNullOrWhiteSpace($Text)) {
        return $null
    }

    $m = [regex]::Match($Text, '(\d+\.\d+(?:\.\d+){0,2})')
    if (-not $m.Success) {
        return $null
    }

    try {
        return [Version]$m.Value
    }
    catch {
        return $null
    }
}

function New-CheckResult {
    param(
        [string]$Item,
        [bool]$Pass,
        [string]$Required,
        [string]$Actual,
        [string]$Note = ""
    )

    return [PSCustomObject]@{
        item = $Item
        pass = $Pass
        required = $Required
        actual = $Actual
        note = $Note
    }
}

function Test-MinVersion {
    param(
        [string]$Actual,
        [string]$Minimum
    )

    $a = ConvertTo-Version $Actual
    $m = ConvertTo-Version $Minimum

    if (-not $a -or -not $m) {
        return $false
    }

    return ($a -ge $m)
}

function Get-DisplayValue {
    param(
        [AllowNull()][string]$Value,
        [string]$Fallback = "missing"
    )

    if ([string]::IsNullOrWhiteSpace($Value)) {
        return $Fallback
    }

    return $Value
}

if (-not (Test-Path -Path $OutputDir)) {
    New-Item -Path $OutputDir -ItemType Directory | Out-Null
}

$programs = Get-InstalledPrograms
$results = New-Object System.Collections.Generic.List[object]

$os = Get-CimInstance Win32_OperatingSystem
$cs = Get-CimInstance Win32_ComputerSystem
$diskC = Get-CimInstance Win32_LogicalDisk -Filter "DeviceID='C:'"

$winBuildMin = [Version]"10.0.19045"
$winCurrent = [Version]("{0}.{1}" -f $os.Version, $os.BuildNumber)
$results.Add((New-CheckResult -Item "Windows Version" -Pass ($winCurrent -ge $winBuildMin) -Required "Win10 22H2+" -Actual ("{0} (Build {1})" -f $os.Caption, $os.BuildNumber)))

$memoryGB = [Math]::Round($cs.TotalPhysicalMemory / 1GB, 2)
$results.Add((New-CheckResult -Item "RAM" -Pass ($memoryGB -ge 32) -Required ">= 32 GB" -Actual ("{0} GB" -f $memoryGB)))

if ($diskC) {
    $diskSizeGB = [Math]::Round($diskC.Size / 1GB, 2)
    $diskFreeGB = [Math]::Round($diskC.FreeSpace / 1GB, 2)
    $results.Add((New-CheckResult -Item "Disk C Size" -Pass ($diskSizeGB -ge 500) -Required ">= 500 GB" -Actual ("{0} GB" -f $diskSizeGB)))
    $results.Add((New-CheckResult -Item "Disk C Free" -Pass ($diskFreeGB -ge 80) -Required ">= 80 GB free" -Actual ("{0} GB" -f $diskFreeGB)))
}

$node = Get-CliVersion -Command "node" -Script { node --version }
$npm = Get-CliVersion -Command "npm" -Script { npm --version }
$php = Get-CliVersion -Command "php" -Script { php --version }
$composer = Get-CliVersion -Command "composer" -Script { composer --version }

$results.Add((New-CheckResult -Item "Node.js" -Pass ($node.found -and (Test-MinVersion -Actual $node.version -Minimum "22.15.0")) -Required ">= 22.15.0" -Actual (Get-DisplayValue -Value $node.version) -Note "infra item #10"))
$results.Add((New-CheckResult -Item "npm" -Pass $npm.found -Required "installed" -Actual (Get-DisplayValue -Value $npm.version)))
$results.Add((New-CheckResult -Item "PHP" -Pass ($php.found -and (Test-MinVersion -Actual $php.version -Minimum "8.4.6")) -Required ">= 8.4.6" -Actual (Get-DisplayValue -Value $php.version) -Note "infra item #37"))
$results.Add((New-CheckResult -Item "Composer" -Pass ($composer.found -and (Test-MinVersion -Actual $composer.version -Minimum "2.8.8")) -Required ">= 2.8.8" -Actual (Get-DisplayValue -Value $composer.version) -Note "infra item #24"))

$coreApps = @(
    @{ Item = "Chrome"; Min = "135.0"; Patterns = @("Google Chrome") },
    @{ Item = "VS Code"; Min = "1.86.0"; Patterns = @("Microsoft Visual Studio Code") },
    @{ Item = "Postman"; Min = "11.42.5"; Patterns = @("Postman") }
)

$extendedApps = @(
    @{ Item = "FileZilla Client"; Min = "3.66.5"; Patterns = @("FileZilla") },
    @{ Item = "Firefox Developer Edition"; Min = "124.0"; Patterns = @("Firefox Developer Edition") },
    @{ Item = "WinSCP"; Min = "6.3.1"; Patterns = @("WinSCP") },
    @{ Item = "PuTTY"; Min = "0.80"; Patterns = @("PuTTY") },
    @{ Item = "Eclipse for PHP Developers"; Min = "2025.03"; Patterns = @("Eclipse") },
    @{ Item = "Sublime Text"; Min = "4172.0"; Patterns = @("Sublime Text") },
    @{ Item = "PhpStorm"; Min = "2023.3.3"; Patterns = @("PhpStorm") },
    @{ Item = "MySQL Workbench"; Min = "8.1.3"; Patterns = @("MySQL Workbench") },
    @{ Item = "GIMP"; Min = "3.0.2"; Patterns = @("GIMP") },
    @{ Item = "Notepad++"; Min = "8.6.4"; Patterns = @("Notepad++") },
    @{ Item = "7-Zip"; Min = "22.0"; Patterns = @("7-Zip") },
    @{ Item = "Zeal"; Min = "0.72"; Patterns = @("Zeal") }
)

$toCheck = @($coreApps)
if ($Profile -eq "Extended" -or $Profile -eq "Full") {
    $toCheck += $extendedApps
}

foreach ($app in $toCheck) {
    $match = Find-ProgramVersion -Programs $programs -NamePatterns $app.Patterns
    $pass = $false

    if ($match.found) {
        $pass = Test-MinVersion -Actual $match.version -Minimum $app.Min
    }

    $results.Add((New-CheckResult -Item $app.Item -Pass $pass -Required (">= {0}" -f $app.Min) -Actual (Get-DisplayValue -Value $match.version) -Note (Get-DisplayValue -Value $match.name -Fallback "not installed")))
}

if ($Profile -eq "Full") {
    $ng = Get-CliVersion -Command "ng" -Script { ng version }
    $vue = Get-CliVersion -Command "vue" -Script { vue --version }
    $cypress = Get-CliVersion -Command "cypress" -Script { cypress --version }
    $testcafe = Get-CliVersion -Command "testcafe" -Script { testcafe --version }

    $results.Add((New-CheckResult -Item "Angular CLI" -Pass ($ng.found -and (Test-MinVersion -Actual $ng.version -Minimum "17.2.1")) -Required ">= 17.2.1" -Actual (Get-DisplayValue -Value $ng.version)))
    $results.Add((New-CheckResult -Item "Vue CLI" -Pass ($vue.found -and (Test-MinVersion -Actual $vue.version -Minimum "5.0.8")) -Required ">= 5.0.8" -Actual (Get-DisplayValue -Value $vue.version)))
    $results.Add((New-CheckResult -Item "Cypress CLI" -Pass ($cypress.found -and (Test-MinVersion -Actual $cypress.version -Minimum "13.6.4")) -Required ">= 13.6.4" -Actual (Get-DisplayValue -Value $cypress.version)))
    $results.Add((New-CheckResult -Item "TestCafe CLI" -Pass ($testcafe.found -and (Test-MinVersion -Actual $testcafe.version -Minimum "3.5.0")) -Required ">= 3.5.0" -Actual (Get-DisplayValue -Value $testcafe.version)))
}

$passCount = ($results | Where-Object { $_.pass }).Count
$totalCount = $results.Count
$status = if ($passCount -eq $totalCount) { "PASS" } else { "FAIL" }

$now = Get-Date
$stamp = $now.ToString("yyyyMMdd-HHmmss")

$reportObject = [PSCustomObject]@{
    generatedAt = $now.ToString("o")
    profile = $Profile
    status = $status
    passCount = $passCount
    totalCount = $totalCount
    checks = $results
}

$jsonPath = Join-Path $OutputDir ("preflight-{0}-{1}.json" -f $Profile.ToLower(), $stamp)
$mdPath = Join-Path $OutputDir ("preflight-{0}-{1}.md" -f $Profile.ToLower(), $stamp)

$reportObject | ConvertTo-Json -Depth 6 | Out-File -FilePath $jsonPath -Encoding UTF8

$md = @()
$md += "# Preflight Report"
$md += ""
$md += "- Generated At: $($reportObject.generatedAt)"
$md += "- Profile: $($reportObject.profile)"
$md += "- Status: **$($reportObject.status)**"
$md += "- Score: $($reportObject.passCount) / $($reportObject.totalCount)"
$md += ""
$md += "| Item | Pass | Required | Actual | Note |"
$md += "|------|------|----------|--------|------|"

foreach ($r in $results) {
    $passText = if ($r.pass) { "YES" } else { "NO" }
    $md += "| $($r.item) | $passText | $($r.required) | $($r.actual) | $($r.note) |"
}

$md -join "`r`n" | Out-File -FilePath $mdPath -Encoding UTF8

Write-Host "Preflight status: $status ($passCount/$totalCount)"
Write-Host "JSON report: $jsonPath"
Write-Host "Markdown report: $mdPath"

if ($status -eq "FAIL") {
    exit 1
}
