$ErrorActionPreference='Stop'
Add-Type -AssemblyName System.Drawing
$path=Join-Path $PSScriptRoot 'Marco_teorico_Sistema_EDD_CENIDET.docx'
$qa=Join-Path $PSScriptRoot 'qa'
New-Item -ItemType Directory -Path $qa -Force | Out-Null
Write-Output 'Iniciando Word'
$word=New-Object -ComObject Word.Application
$word.Visible=$false
$word.DisplayAlerts=0
try {
 Write-Output 'Abriendo documento'
 $document=$word.Documents.Open($path,$false,$false)
 Write-Output 'Actualizando campos'
 
 Write-Output 'Paginando'
 
 
 $document.ExportAsFixedFormat((Join-Path $qa 'revision.pdf'),17)
 $window=$document.ActiveWindow
 $window.View.Type=3
 $pages=$window.Panes.Item(1).Pages
 Write-Output ('Pages: '+$pages.Count)
 for($i=1;$i -le $pages.Count;$i++) {
  $bits=$pages.Item($i).EnhMetaFileBits
  $stream=[System.IO.MemoryStream]::new([byte[]]$bits)
  $image=[System.Drawing.Image]::FromStream($stream)
  $bitmap=[System.Drawing.Bitmap]::new(1275,1650)
  $graphics=[System.Drawing.Graphics]::FromImage($bitmap)
  $graphics.Clear([System.Drawing.Color]::White)
  $graphics.DrawImage($image,0,0,1275,1650)
  $bitmap.Save((Join-Path $qa ('page-'+$i+'.png')),[System.Drawing.Imaging.ImageFormat]::Png)
  $graphics.Dispose();$bitmap.Dispose();$image.Dispose();$stream.Dispose()
 }
 $document.Close(0)
} finally {$word.Quit()}
