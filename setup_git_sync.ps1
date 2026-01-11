# setup_git_sync.ps1
# 依據 docs/git同步備份branch.md 建立的同步腳本

$BackupUrl = "git@github.com:edwin45168899/PHP_Laravel.git"
$MainUrl = "git@github.com-chiisen:chiisen/PHP_Laravel.git"

Write-Output "正在設定 Git 遠端同步..."
Write-Output "備份遠端: $BackupUrl"
Write-Output "主要遠端: $MainUrl"   

# 1. 加入 backup repo 到 origin 的 push list
git remote set-url --add --push origin $BackupUrl

# 2. 加入主要 repo 到 origin 的 push list
git remote set-url --add --push origin $MainUrl

Write-Output "設定完成。目前的 remote 設定如下："
git remote -v
