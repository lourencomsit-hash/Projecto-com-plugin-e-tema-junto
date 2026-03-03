$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $PSScriptRoot
$target = Join-Path $root "assets\\parks"

$files = @(
  @{rel="serengeti\\serengeti-4.jpg"; url="https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-4-2000x1333.jpg"},
  @{rel="serengeti\\serengeti-3.jpg"; url="https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-3-2000x1250.jpg"},
  @{rel="serengeti\\serengeti-5.jpg"; url="https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-5-2000x1196.jpg"},
  @{rel="serengeti\\serengeti-6.jpg"; url="https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-6-2000x1201.jpg"},
  @{rel="serengeti\\serengeti-classic.jpg"; url="https://breezesafaris.com/wp-content/uploads/2018/01/serengeti-scaled-2048x1536.jpg"},

  @{rel="ngorongoro\\ngorongoro-main.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-scaled-2048x1366.jpg"},
  @{rel="ngorongoro\\ngorongoro-2000.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-2000x1333.jpg"},
  @{rel="ngorongoro\\ngorongoro-1536.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-scaled-1536x1024.jpg"},
  @{rel="ngorongoro\\ngorongoro-1024.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-1024x683.jpg"},
  @{rel="ngorongoro\\ngorongoro-pan.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-950x320.jpg"},

  @{rel="tarangire\\tarangire-main.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-scaled.jpg"},
  @{rel="tarangire\\tarangire-2000.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-2000x1723.jpg"},
  @{rel="tarangire\\tarangire-1200.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-1200x800.jpg"},
  @{rel="tarangire\\tarangire-1024.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-1024x882.jpg"},
  @{rel="tarangire\\tarangire-pan.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-950x320.jpg"},

  @{rel="manyara\\manyara-main.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-scaled-2048x1366.jpg"},
  @{rel="manyara\\manyara-2000.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-2000x1334.jpg"},
  @{rel="manyara\\manyara-1536.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-scaled-1536x1024.jpg"},
  @{rel="manyara\\manyara-1024.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-1024x683.jpg"},
  @{rel="manyara\\manyara-pan.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-950x320.jpg"},

  @{rel="arusha\\arusha-main.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-scaled-2048x1366.jpg"},
  @{rel="arusha\\arusha-2000.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-2000x1333.jpg"},
  @{rel="arusha\\arusha-1536.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-scaled-1536x1024.jpg"},
  @{rel="arusha\\arusha-1024.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-1024x683.jpg"},
  @{rel="arusha\\arusha-pan.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-950x320.jpg"},

  @{rel="ndutu\\ndutu-main.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-scaled-2048x1366.jpg"},
  @{rel="ndutu\\ndutu-2000.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-2000x1334.jpg"},
  @{rel="ndutu\\ndutu-1536.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-scaled-1536x1024.jpg"},
  @{rel="ndutu\\ndutu-1024.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-1024x683.jpg"},
  @{rel="ndutu\\ndutu-pan.jpg"; url="https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-950x320.jpg"},

  @{rel="zanzibar\\zanzibar-beach.jpg"; url="https://breezesafaris.com/wp-content/uploads/2025/11/pexels-followalice-667205-2000x1379.jpg"},
  @{rel="zanzibar\\zanzibar-villa.png"; url="https://breezesafaris.com/wp-content/uploads/2025/11/IMG_7208-1-2000x1333.png"},
  @{rel="zanzibar\\zanzibar-resort.webp"; url="https://breezesafaris.com/wp-content/uploads/2025/11/MTC-tented-suite2.webp"},
  @{rel="zanzibar\\zanzibar-lifestyle.jpg"; url="https://breezesafaris.com/wp-content/uploads/2025/11/IMG_7903.jpg"},
  @{rel="zanzibar\\zanzibar-couple.jpg"; url="https://breezesafaris.com/wp-content/uploads/2026/02/image-2-edited.jpg"}
)

foreach ($item in $files) {
  $out = Join-Path $target $item.rel
  $dir = Split-Path -Parent $out
  New-Item -ItemType Directory -Path $dir -Force | Out-Null
  Invoke-WebRequest -Uri $item.url -OutFile $out -UseBasicParsing
  Write-Host "Downloaded $($item.rel)"
}

Write-Host ""
Write-Host "Done. Files saved to: $target"
