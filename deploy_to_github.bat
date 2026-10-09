@echo off
echo =======================================================
echo   DEPLOYING BRIAN JOSHUA TANAEL PORTFOLIO TO GITHUB
echo =======================================================
echo.
echo Make sure you have created the repository on GitHub:
echo Option 1 (Recommended): https://github.com/new -> Name it: Brian187012703.github.io
echo Option 2: https://github.com/new -> Name it: portfolio
echo.
set /p REPO_NAME="Enter your repo name (default: Brian187012703.github.io): "
if "%REPO_NAME%"=="" set REPO_NAME=Brian187012703.github.io

echo.
echo.
if not exist .git (
  echo Initializing local git repository...
  git init
)

echo Staging all updated files...
git add .
git commit -m "Fix button functionality, resolve git conflicts, and implement full multi-device responsiveness" 2>nul

echo.
echo Setting remote origin to: https://github.com/Brian187012703/%REPO_NAME%.git
git remote remove origin 2>nul
git remote add origin https://github.com/Brian187012703/%REPO_NAME%.git
git branch -M main

echo.
echo Pushing website to GitHub...
git push -u origin main

echo.
echo =======================================================
echo Done! 
echo If your repo was named Brian187012703.github.io:
echo Your site will be live in 1-2 minutes at:
echo https://brian187012703.github.io/
echo.
echo If your repo was named portfolio:
echo Go to your repo Settings -> Pages -> Source: 'Deploy from a branch' -> 'main' / root -> Save!
echo Your site will be live at:
echo https://brian187012703.github.io/portfolio/
echo =======================================================
pause
