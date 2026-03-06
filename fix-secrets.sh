#!/bin/bash

# Quick Fix Script for Leaked Secrets
# This script helps you resolve the GitHub push protection issue

set -e

echo "🔒 GitHub Secret Leak Fix Script"
echo "================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "src/Authentication/src/ConfigProvider.php" ]; then
    echo -e "${RED}Error: Must be run from the neutrino root directory${NC}"
    echo "Current directory: $(pwd)"
    exit 1
fi

echo -e "${YELLOW}Step 1: Checking current configuration...${NC}"
if grep -q "GOOGLE_OAUTH_CLIENT_ID" src/Authentication/src/ConfigProvider.php; then
    echo -e "${GREEN}✓ ConfigProvider.php is using environment variables${NC}"
else
    echo -e "${RED}✗ ConfigProvider.php might have hardcoded secrets${NC}"
fi

echo ""
echo -e "${YELLOW}Step 2: Choose a fix option:${NC}"
echo ""
echo "Option 1: Allow the secrets (if they're already revoked/invalid)"
echo "Option 2: Rewrite git history (removes secrets from all commits)"
echo "Option 3: Just show me the problematic commit"
echo ""
read -p "Enter your choice (1/2/3): " choice

case $choice in
    1)
        echo ""
        echo -e "${YELLOW}Opening GitHub URLs to allow secrets...${NC}"
        echo ""
        echo "Click 'Allow this secret' on each page:"
        echo ""
        echo "1. Google Client ID:"
        echo "   https://github.com/vasildakov/neutrino/security/secret-scanning/unblock-secret/3AYt41iL6vuxPZfKDw8af9J4x1V"
        echo ""
        echo "2. Google Client Secret:"
        echo "   https://github.com/vasildakov/neutrino/security/secret-scanning/unblock-secret/3AYt40Ev2s0I77UAztT3whdWW69"
        echo ""
        echo -e "${YELLOW}After allowing both secrets, try pushing again:${NC}"
        echo "git push origin main"
        ;;

    2)
        echo ""
        echo -e "${RED}⚠️  WARNING: This will rewrite git history!${NC}"
        echo "This should only be done if:"
        echo "  - You haven't pushed these commits to production"
        echo "  - You've revoked the leaked credentials"
        echo "  - Other team members can re-clone the repo"
        echo ""
        read -p "Are you sure? (yes/no): " confirm

        if [ "$confirm" = "yes" ]; then
            echo ""
            echo -e "${YELLOW}Attempting to fix commit cef261f488309bbd6a3548de18c9fd6367006171...${NC}"
            echo ""

            # Check if BFG is installed
            if ! command -v bfg &> /dev/null; then
                echo -e "${YELLOW}BFG Repo-Cleaner not found. Install it first:${NC}"
                echo ""
                echo "macOS:   brew install bfg"
                echo "Other:   Download from https://rtyley.github.io/bfg-repo-cleaner/"
                echo ""
                exit 1
            fi

            echo -e "${RED}Please create a file called 'secrets.txt' with your actual secrets (one per line)${NC}"
            echo "Then run:"
            echo ""
            echo "  cd .."
            echo "  git clone --mirror git@github.com:vasildakov/neutrino.git neutrino-clean.git"
            echo "  cd neutrino-clean.git"
            echo "  bfg --replace-text ../secrets.txt"
            echo "  git reflog expire --expire=now --all && git gc --prune=now --aggressive"
            echo "  git push --force"
            echo ""
        else
            echo "Aborted."
        fi
        ;;

    3)
        echo ""
        echo -e "${YELLOW}Showing problematic commit...${NC}"
        echo ""
        git show cef261f488309bbd6a3548de18c9fd6367006171:src/Authentication/src/ConfigProvider.php | head -n 160 | tail -n 20
        ;;

    *)
        echo -e "${RED}Invalid choice${NC}"
        exit 1
        ;;
esac

echo ""
echo -e "${GREEN}For more detailed instructions, see:${NC}"
echo "  docs/fix-leaked-secrets.md"
echo ""

