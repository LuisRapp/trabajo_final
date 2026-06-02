$ErrorActionPreference = 'Stop'

# Vuelve al modo Docker completo (app + db)

$repoRoot = Resolve-Path (Join-Path $PSScriptRoot '..')

Push-Location $repoRoot
try {
    docker compose up -d db app
}
finally {
    Pop-Location
}
