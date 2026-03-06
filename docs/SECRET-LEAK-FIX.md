# 🚨 IMMEDIATE ACTION REQUIRED: GitHub Secret Leak

## Problem
GitHub blocked your push because commit `cef261f` contains Google OAuth secrets in `src/Authentication/src/ConfigProvider.php`.

## ✅ Current Status
- Your current file IS CORRECT (uses environment variables)
- Problem is in GIT HISTORY (commit cef261f488309bbd6a3548de18c9fd6367006171)
- `.env` is properly in `.gitignore` ✓
- `.env.example` template created ✓

---

## 🎯 CHOOSE YOUR FIX (Pick ONE)

### ⚡ Option 1: Allow Secrets (FASTEST - 2 minutes)

**Use if**: Credentials are test/dev only OR already revoked

**Steps**:
1. Click: https://github.com/vasildakov/neutrino/security/secret-scanning/unblock-secret/3AYt41iL6vuxPZfKDw8af9J4x1V
2. Click "Allow this secret"
3. Click: https://github.com/vasildakov/neutrino/security/secret-scanning/unblock-secret/3AYt40Ev2s0I77UAztT3whdWW69
4. Click "Allow this secret"
5. Push again: `git push origin main`

**Pros**: Instant fix
**Cons**: Secrets remain in git history (only do if secrets are invalid)

---

### 🔧 Option 2: Amend Last Commit (RECOMMENDED - 5 minutes)

**Use if**: The problematic commit is your most recent one (which it is!)

**Steps**:
```bash
cd /Users/vasdakov/Dev/vasildakov/neutrino/apps/neutrino

# Run the fix script
./amend-commit.sh

# Or manually:
git add src/Authentication/src/ConfigProvider.php
git commit --amend --no-edit
git push origin main --force
```

**Pros**: Removes secrets from history completely
**Cons**: Requires force push (only issue if others already pulled)

---

### 🔨 Option 3: Rewrite Full History (THOROUGH - 15 minutes)

**Use if**: Secrets exist in multiple commits OR Option 2 doesn't work

**Steps**:
```bash
# Install BFG (if needed)
brew install bfg  # macOS

# Create a file with your actual secrets
cat > /tmp/secrets.txt << EOF
your-actual-google-client-id-here
your-actual-google-client-secret-here
EOF

# Clone mirror and clean
cd /Users/vasdakov/Dev/vasildakov
git clone --mirror git@github.com:vasildakov/neutrino.git neutrino-clean.git
cd neutrino-clean.git

# Remove secrets
bfg --replace-text /tmp/secrets.txt

# Cleanup and push
git reflog expire --expire=now --all
git gc --prune=now --aggressive
git push --force

# Update your local repo
cd /Users/vasdakov/Dev/vasildakov/neutrino/apps/neutrino
git fetch origin
git reset --hard origin/main

# Cleanup
rm /tmp/secrets.txt
```

**Pros**: Most thorough cleanup
**Cons**: More complex, requires BFG tool

---

## 🛡️ SECURITY BEST PRACTICE

### If secrets might be compromised:

1. **Revoke credentials immediately**:
   - Go to: https://console.cloud.google.com/apis/credentials
   - Delete the leaked OAuth Client ID
   - Create new credentials

2. **Update your .env**:
   ```bash
   # Edit your .env file with NEW credentials
   nano .env
   ```

3. **Then use Option 2 or 3** to clean git history

---

## 📋 MY RECOMMENDATION

Based on your situation, here's what I recommend:

### If credentials are DEV/TEST only:
✅ **Use Option 1** (allow secrets) - fastest

### If credentials might be used in production:
1. ⚠️  **Revoke them IMMEDIATELY** in Google Cloud Console
2. ✅ **Use Option 2** (amend commit) - easiest
3. 🔄 Create new credentials and update `.env`

---

## 📝 Files Created to Help You

- `fix-secrets.sh` - Interactive fix script
- `amend-commit.sh` - Quick amend script
- `.env.example` - Template for environment variables
- `docs/fix-leaked-secrets.md` - Detailed instructions

## 🚀 QUICK START

**Most users should run**:
```bash
# Check what was leaked
./fix-secrets.sh

# Choose option 3 to see the commit
# Then decide which fix to use
```

**OR if you're sure credentials are invalid**:
Just click the GitHub URLs above to allow them and push again.

---

## ❓ Need Help?

Run the interactive script:
```bash
./fix-secrets.sh
```

Or ask me which option you'd like to pursue!

