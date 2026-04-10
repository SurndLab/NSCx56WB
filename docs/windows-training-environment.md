# Windows Competition Training Environment Toolkit

This guide helps coaches or assistants standardize and audit Windows practice machines based on the equipment/software table in [infra.md](infra.md).

## Goal

Use one repeatable process to:

1. Collect machine baseline (hardware + software versions)
2. Run preflight checks before training/mock exam
3. Export PASS/FAIL reports for quick review

## Files

- `tools/windows-env/collect-env.ps1`: inventory collection (system + installed apps + command versions)
- `tools/windows-env/preflight-check.ps1`: requirement checks (Core/Extended/Full profile)
- `tools/windows-env/install-baseline.ps1`: one-command baseline installer using winget (Core/Extended/Full)

## Suggested Workflow

1. Run `collect-env.ps1` on each machine and keep JSON reports.
2. Run `preflight-check.ps1 -Profile Core` before every training session.
3. Run `preflight-check.ps1 -Profile Full` before full mock exam days.
4. Compare report changes across dates to detect version drift.

## Run Commands (PowerShell)

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass

# 0) One-command install baseline software (core)
.\tools\windows-env\install-baseline.ps1 -Profile Core

# 0b) One-command install baseline software (extended)
.\tools\windows-env\install-baseline.ps1 -Profile Extended

# 0c) Full mode: include JS/PHP toolchains
.\tools\windows-env\install-baseline.ps1 -Profile Full

# 0d) Full mode + prepare starter projects for framework drills
.\tools\windows-env\install-baseline.ps1 -Profile Full -PrepareStarterProjects -StartersDir .\starters

# 1) Collect environment baseline
.\tools\windows-env\collect-env.ps1 -OutputDir .\reports

# 2) Fast readiness check (core tools)
.\tools\windows-env\preflight-check.ps1 -Profile Core -OutputDir .\reports

# 3) Full check (core + extended + toolchain)
.\tools\windows-env\preflight-check.ps1 -Profile Full -OutputDir .\reports
```

## What Is Checked

### Core profile

- Windows version (Win10 22H2 or newer)
- CPU, RAM, disk free space
- Node.js, npm
- Chrome
- VS Code
- PHP, Composer
- Postman

### Extended profile

Includes Core plus common competition tools listed in infra:

- FileZilla, WinSCP, Firefox Developer Edition, PuTTY
- Eclipse for PHP Developers, Sublime Text, PhpStorm
- MySQL Workbench, GIMP, Notepad++, 7-Zip

### Full profile installer

`install-baseline.ps1 -Profile Full` additionally provisions:

- npm globals: Angular CLI, Vue CLI, AngularJS package, create-react-app, Cypress, TestCafe
- composer globals: laravel/installer, yiisoft/yii2-composer
- optional starter projects (`-PrepareStarterProjects`): Laravel 10, Yii2, CodeIgniter 4, React blank app, frontend libs (Bootstrap/Foundation)

## Notes

- Some constraints in `infra.md` are physical/proctor rules (for example USB port sealing). Scripts cannot verify these automatically; keep a manual checklist for those items.
- `install-baseline.ps1` uses winget; if your room is offline, prepare an offline package repository and run local installers.
- Eclipse for PHP Developers is installed through Eclipse Installer package selection (installer is automated; package selection still needs one confirmation flow).
- Zeal app can be installed automatically; Zeal docsets still need import/sync based on your room's offline policy.
- If your training room has no internet, store offline installers in a local shared folder and avoid auto-update right before mock exams.
- Freeze versions one week before competition simulation to prevent environment drift.
