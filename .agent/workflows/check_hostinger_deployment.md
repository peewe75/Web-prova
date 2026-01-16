---
description: Steps to verify Hostinger deployment when changes are not visible live
---

# Hostinger Deployment Check

Use this workflow when you have pushed changes (git push) but they are not reflected on the live site (`studiodigitale.eu`).

1. **Verify Git Push**
   - Ensure `git push origin main` completed successfully.

2. **Check Hostinger Deployment Logs**
   - **ACTION REQUIRED:** Ask the user to check the "Deployment" section in their Hostinger Dashboard.
   - Look for errors such as:
     - `Aborting: The following untracked working tree files would be overwritten by merge` (File conflict)
     - Permission errors
     - Connection timeouts

3. **Common Fixes**
   - **Untracked File Conflict:**
     - If a file exists on the server but is not tracked by git, and you try to push it, the deploy will fail.
     - *Solution:* Rename the local file, or delete it from the server, or use `git rm --cached` if strictly necessary.
   - **Cache:**
     - Check `api/blog.php` or other endpoints directly to see if the **server** has the new code.
     - If the server has new code but browser shows old, it's Browser Cache.
     - If the server sends old code, it's Deployment Failure (or Server Cache).

4. **Force Update (Emergency)**
   - If history is messy, `git push --force` might be needed (use with caution).
