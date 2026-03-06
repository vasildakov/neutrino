# Fix: Remove Leaked Secrets from Git History

## Problem
GitHub detected Google OAuth secrets in commit `cef261f488309bbd6a3548de18c9fd6367006171` in the file `src/Authentication/src/ConfigProvider.php`.

## Current Status
✅ The current file is correct - it uses environment variables
❌ Git history contains the actual secrets in a previous commit

## Solutions

### Option 1: Rewrite Git History (Recommended if you haven't pushed to production)

#### Step 1: Check the problematic commit
```bash
git show cef261f488309bbd6a3548de18c9fd6367006171:src/Authentication/src/ConfigProvider.php | grep -A2 "client_id\|client_secret"
```

#### Step 2: Use BFG Repo-Cleaner (Easiest)
```bash
# Install BFG (if not installed)
brew install bfg  # macOS
# or download from: https://rtyley.github.io/bfg-repo-cleaner/

# Clone a fresh bare copy
cd ..
git clone --mirror git@github.com:vasildakov/neutrino.git neutrino-clean.git
cd neutrino-clean.git

# Remove the secrets (replace with your actual secret patterns)
echo "YOUR_ACTUAL_CLIENT_ID_HERE" > ../secrets.txt
echo "YOUR_ACTUAL_CLIENT_SECRET_HERE" >> ../secrets.txt

bfg --replace-text ../secrets.txt

# Force push to rewrite history
git reflog expire --expire=now --all && git gc --prune=now --aggressive
git push --force
```

#### Step 3: Update your local repo
```bash
cd /Users/vasdakov/Dev/vasildakov/neutrino/apps/neutrino
git fetch origin
git reset --hard origin/main
```

### Option 2: Interactive Rebase (If commit is recent)

```bash
# Find where the commit is
git log --oneline | grep cef261f

# If it's within the last N commits, you can rebase
git rebase -i HEAD~N  # Replace N with number of commits back

# In the editor:
# - Change 'pick' to 'edit' for commit cef261f488309bbd6a3548de18c9fd6367006171
# - Save and close

# Edit the file
# (Edit src/Authentication/src/ConfigProvider.php to remove secrets)

# Amend the commit
git add src/Authentication/src/ConfigProvider.php
git commit --amend --no-edit

# Continue rebase
git rebase --continue

# Force push
git push origin main --force
```

### Option 3: Allow Secret via GitHub (NOT RECOMMENDED for real secrets)

If these are test/development secrets that don't matter:
1. Visit: https://github.com/vasildakov/neutrino/security/secret-scanning/unblock-secret/3AYt41iL6vuxPZfKDw8af9J4x1V
2. Click "Allow this secret"
3. Repeat for: https://github.com/vasildakov/neutrino/security/secret-scanning/unblock-secret/3AYt40Ev2s0I77UAztT3whdWW69
4. Try pushing again

⚠️ **WARNING**: Only use this if the secrets are already invalid or were test credentials!

### Option 4: Revoke and Replace Secrets (Safest)

If the secrets might have been exposed:

1. **Revoke the old credentials:**
   - Go to Google Cloud Console: https://console.cloud.google.com/
   - Navigate to: APIs & Services > Credentials
   - Delete the leaked OAuth 2.0 Client ID
   - Create a new one

2. **Update your environment variables** with new credentials

3. **Clean git history** using Option 1 or 2

4. **Push again**

## Prevention for Future

### Create .env file (if not exists)
```bash
cat > /Users/vasdakov/Dev/vasildakov/neutrino/apps/neutrino/.env << 'EOF'
GOOGLE_OAUTH_CLIENT_ID=your-new-client-id-here
GOOGLE_OAUTH_CLIENT_SECRET=your-new-client-secret-here
GOOGLE_OAUTH_REDIRECT_URI=http://localhost:8080/auth/google/callback
TWITTER_OAUTH_CLIENT_ID=your-twitter-client-id
TWITTER_OAUTH_CLIENT_SECRET=your-twitter-client-secret
TWITTER_OAUTH_REDIRECT_URI=http://localhost:8080/auth/twitter/callback
EOF
```

### Ensure .env is in .gitignore
```bash
echo ".env" >> /Users/vasdakov/Dev/vasildakov/neutrino/apps/neutrino/.gitignore
git add .gitignore
git commit -m "Add .env to gitignore"
```

### Create .env.example template
```bash
cat > /Users/vasdakov/Dev/vasildakov/neutrino/apps/neutrino/.env.example << 'EOF'
# Google OAuth Configuration
GOOGLE_OAUTH_CLIENT_ID=your-google-client-id
GOOGLE_OAUTH_CLIENT_SECRET=your-google-client-secret
GOOGLE_OAUTH_REDIRECT_URI=http://localhost:8080/auth/google/callback

# Twitter OAuth Configuration
TWITTER_OAUTH_CLIENT_ID=your-twitter-client-id
TWITTER_OAUTH_CLIENT_SECRET=your-twitter-client-secret
TWITTER_OAUTH_REDIRECT_URI=http://localhost:8080/auth/twitter/callback
EOF

git add .env.example
git commit -m "Add .env.example template"
```

## Recommended Action Plan

1. ✅ **Immediately**: Revoke the leaked credentials in Google Cloud Console
2. ✅ **Clean history**: Use BFG Repo-Cleaner (Option 1)
3. ✅ **Generate new credentials**: Create new OAuth client in Google Cloud
4. ✅ **Update .env**: Store new credentials locally
5. ✅ **Verify .gitignore**: Ensure .env is never committed
6. ✅ **Push again**: Try pushing your changes

## Quick Fix (If secrets are already invalid)

If you've already revoked the credentials or they're test credentials:

```bash
# Just allow them via GitHub's interface
# Click the URLs GitHub provided:
# - https://github.com/vasildakov/neutrino/security/secret-scanning/unblock-secret/3AYt41iL6vuxPZfKDw8af9J4x1V
# - https://github.com/vasildakov/neutrino/security/secret-scanning/unblock-secret/3AYt40Ev2s0I77UAztT3whdWW69

# Then push again
git push origin main
```

## Need Help?

If you need help with any of these steps, let me know which option you'd like to pursue!

