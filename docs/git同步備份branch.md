# git同步備份branch

## 添加【自訂遠端】
```bash
# 語法：git remote add <自訂名稱> <專案的路徑或URL>
git remote add edwin45168899 git@github.com:edwin45168899/PHP_Laravel.git
```
`git@github.com:edwin45168899/PHP_Laravel.git` 查一下 C:\Users\chiis\.ssh\config 裡面的設定

## 查看新增的【自訂遠端】
```bash
git remote
```

## 刪除【自訂遠端】
```bash
git remote remove edwin45168899
```

## 建立 GitHub 專案
專案名稱同本地專案名稱
README.md、.gitignore、LICENSE 都不要設定  
因為 commit 後可能會衝突  

## 推送到遠端的 GitHub
```bash 
git push edwin45168899 main

# 本地的分支
git push origin main

# 本地的分支並追蹤
git push -u origin main
```

## 一次同步多個分支
設定【自訂遠端】
```bash
# 注意 origin 的 push 會多一個 push
git remote set-url --add --push origin git@github.com:edwin45168899/PHP_Laravel.git
# 預設沒設定 origin，可能還要設定一次
git remote set-url --add --push origin git@github.com-chiisen:chiisen/PHP_Laravel.git

# 刪除 git remote set-url 設定
## push
git remote set-url --delete --push origin git@github.com:edwin45168899/PHP_Laravel.git
## fetch
git remote set-url edwin45168899 git@github.com:edwin45168899/PHP_Laravel.git

# 查看 git remote set-url 設定
git remote -v
```
推送 origin 分支，但是 origin 有多一個 origin  git@github.com:edwin45168899/PHP_Laravel.git (push)
```bash
git push
```
