#!/bin/bash

# Quick fix: Amend the most recent commit to remove secrets
# This works because the problematic commit (cef261f) is the HEAD commit

set -e

echo "🔧 Amending commit cef261f to remove secrets from history..."
echo ""

# Verify the current state
echo "Current ConfigProvider.php uses environment variables:"
grep "GOOGLE_OAUTH" src/Authentication/src/ConfigProvider.php | head -n 3
echo ""

# Amend the commit
echo "Amending the commit..."
git add src/Authentication/src/ConfigProvider.php
git commit --amend --no-edit

echo ""
echo "✅ Commit amended! Now force push:"
echo ""
echo "  git push origin main --force"
echo ""
echo "⚠️  WARNING: This rewrites history. Only do this if others haven't pulled this commit yet!"

