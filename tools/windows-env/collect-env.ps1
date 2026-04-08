[CmdletBinding()]
param(
    [string]$OutputDir = ".\reports"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Get-SafeCommandVersion {
    param(
        [Parameter(Mandatory = $true)][string]$Name,
        [Parameter(Mandatory = $true)][scriptblock]$VersionScript
    )

    $exists = Get-Command $Name -ErrorAction SilentlyContinue
    if (-not $exists) {
        return [PSCustomObject]@{
            command = $Name
            found = $false
            version = $null
            raw = $null
        }
    }

    try {
        $raw = & $VersionScript 2>&1 | Out-String
        $version = [regex]::Match($raw, '(\d+\.\d+(?:\.\d+)*)').Value
        if ([string]::IsNullOrWhiteSpace($version)) {
            $version = $null
        }

        return [PSCustomObject]@{
            command = $Name
            found = $true
            version = $version
            raw = $raw.Trim()
        }
    }
    catch {
        return [PSCustomObject]@{
            command = $Name
            found = $true
            version = $null
            raw = $_.Exception.Message
        }
    }
}

function Get-InstalledPrograms {
    $paths = @(
        "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*",
        "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*",
        "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*"
    )

    $all = foreach ($path in $paths) {
        Get-ItemProperty -Path $path -ErrorAction SilentlyContinue |
            Where-Object { $_.DisplayName } |
            Select-Object DisplayName, DisplayVersion, Publisher, InstallDate
    }

    return $all |
        Sort-Object DisplayName -Unique
}

if (-not (Test-Path -Path $OutputDir)) {
    New-Item -Path $OutputDir -ItemType Directory | Out-Null
}

$now = Get-Date
$stamp = $now.ToString("yyyyMMdd-HHmmss")

$os = Get-CimInstance Win32_OperatingSystem
$cpu = Get-CimInstance Win32_Processor | Select-Object -First 1
$cs = Get-CimInstance Win32_ComputerSystem
$diskC = Get-CimInstance Win32_LogicalDisk -Filter "DeviceID='C:'"
$gpus = Get-CimInstance Win32_VideoController | Select-Object Name, AdapterRAM, DriverVersion

$monitorCount = $null
try {
    $monitorCount = (Get-CimInstance -Namespace root\wmi -ClassName WmiMonitorID -ErrorAction Stop).Count
}
catch {
    $monitorCount = $null
}

$commandVersions = @(
    Get-SafeCommandVersion -Name "node" -VersionScript { node --version },
    Get-SafeCommandVersion -Name "npm" -VersionScript { npm --version },
    Get-SafeCommandVersion -Name "php" -VersionScript { php --version },
    Get-SafeCommandVersion -Name "composer" -VersionScript { composer --version },
    Get-SafeCommandVersion -Name "code" -VersionScript { code --version }
)

$installed = Get-InstalledPrograms

$result = [PSCustomObject]@{
    generatedAt = $now.ToString("o")
    computerName = $env:COMPUTERNAME
    userName = $env:USERNAME
    os = [PSCustomObject]@{
        caption = $os.Caption
        version = $os.Version
        buildNumber = $os.BuildNumber
        architecture = $os.OSArchitecture
        lastBootUpTime = $os.LastBootUpTime
    }
    hardware = [PSCustomObject]@{
        cpuName = $cpu.Name
        cpuCores = $cpu.NumberOfCores
        cpuLogicalProcessors = $cpu.NumberOfLogicalProcessors
        totalMemoryGB = [Math]::Round($cs.TotalPhysicalMemory / 1GB, 2)
        diskCSizeGB = if ($diskC) { [Math]::Round($diskC.Size / 1GB, 2) } else { $null }
        diskCFreeGB = if ($diskC) { [Math]::Round($diskC.FreeSpace / 1GB, 2) } else { $null }
        monitorCount = $monitorCount
        gpus = $gpus
    }
    commandVersions = $commandVersions
    installedPrograms = $installed
}

$jsonPath = Join-Path $OutputDir ("env-inventory-{0}.json" -f $stamp)
$result | ConvertTo-Json -Depth 8 | Out-File -FilePath $jsonPath -Encoding UTF8

Write-Host "Inventory saved: $jsonPath"
Write-Host "Installed programs count: $($installed.Count)"
