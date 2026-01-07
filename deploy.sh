#!/bin/bash

# Build and Deploy Script with Auto-incrementing Tags
# Usage: ./deploy.sh "Your commit message"
#    or: ./deploy.sh (will prompt for message)

set -e  # Exit on any error

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}=====================================${NC}"
echo -e "${BLUE}   Build & Deploy Script${NC}"
echo -e "${BLUE}=====================================${NC}\n"

# 1. Check for uncommitted changes
if [[ -n $(git status -s) ]]; then
    echo -e "${YELLOW}Warning: You have uncommitted changes${NC}\n"
fi

# 2. Run npm build
echo -e "${BLUE}Step 1: Running npm build...${NC}"
if npm run build; then
    echo -e "${GREEN}✓ Build successful${NC}\n"
else
    echo -e "${RED}✗ Build failed. Aborting.${NC}"
    exit 1
fi

# 3. Run tests (optional)
echo -e "${BLUE}Step 2: Running tests (optional)...${NC}"
if npm test --if-present 2>/dev/null; then
    echo -e "${GREEN}✓ Tests passed${NC}\n"
else
    echo -e "${YELLOW}⚠ No tests found or tests skipped${NC}\n"
fi

# 4. Git add all changes
echo -e "${BLUE}Step 3: Staging changes...${NC}"
git add .
echo -e "${GREEN}✓ Changes staged${NC}\n"

# 5. Show what will be committed
echo -e "${BLUE}Files to be committed:${NC}"
git status --short
echo ""

# 6. Get commit message from argument or prompt
if [[ -n "$1" ]]; then
    commit_message="$1"
    echo -e "${BLUE}Step 4: Using commit message:${NC} ${GREEN}\"$commit_message\"${NC}\n"
else
    echo -e "${BLUE}Step 4: Enter commit message:${NC}"
    read -p "Commit message: " commit_message

    if [[ -z "$commit_message" ]]; then
        echo -e "${RED}✗ Commit message cannot be empty. Aborting.${NC}"
        exit 1
    fi
fi

# 7. Commit changes
echo -e "\n${BLUE}Committing changes...${NC}"
git commit -m "$commit_message"
echo -e "${GREEN}✓ Changes committed${NC}\n"

# 8. Get the latest tag and increment
echo -e "${BLUE}Step 5: Creating new tag...${NC}"
latest_tag=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
echo -e "Latest tag: ${YELLOW}$latest_tag${NC}"

# Extract version numbers
version=${latest_tag#v}
IFS='.' read -r major minor patch <<< "$version"

# Increment patch version (you can modify this logic)
patch=$((patch + 1))
new_tag="v$major.$minor.$patch"

echo -e "New tag: ${GREEN}$new_tag${NC}"
read -p "Press Enter to continue or Ctrl+C to abort..."

# 9. Create the tag
git tag -a "$new_tag" -m "Release $new_tag: $commit_message"
echo -e "${GREEN}✓ Tag $new_tag created${NC}\n"

# 10. Push to main branch
echo -e "${BLUE}Step 6: Pushing to main...${NC}"
if git push origin main; then
    echo -e "${GREEN}✓ Pushed to main${NC}\n"
else
    echo -e "${RED}✗ Push to main failed${NC}"
    exit 1
fi

# 11. Push tags
echo -e "${BLUE}Step 7: Pushing tags...${NC}"
if git push origin "$new_tag"; then
    echo -e "${GREEN}✓ Tag pushed${NC}\n"
else
    echo -e "${RED}✗ Tag push failed${NC}"
    exit 1
fi

# 12. Summary
echo -e "${GREEN}=====================================${NC}"
echo -e "${GREEN}   Deployment Complete! 🚀${NC}"
echo -e "${GREEN}=====================================${NC}"
echo -e "Commit: ${YELLOW}$commit_message${NC}"
echo -e "Tag: ${YELLOW}$new_tag${NC}"
echo -e "Branch: ${YELLOW}main${NC}\n"
