# GIT WORKFLOW

This document describes the Git workflow and branching rules for FormaLMS development.

## BRANCHING RULES
FormaLMS follows these branching rules for version control:

## Main Development Branch
- `dev` - Represents the current development version (e.g., formalms 4.2 DEV)

### Git Commands for Working with the Dev Branch
```bash
# Switch to the dev branch
git checkout dev

# Pull the latest changes
git pull origin dev

# Create a feature branch from dev
git checkout -b feature/new-feature dev

# Make your changes and commit them
git add .
git commit -m "Add new feature"

# Push your feature branch to the remote repository
git push origin feature/new-feature

# When the feature is ready, merge it back to dev
git checkout dev
git merge feature/new-feature

# Push the updated dev branch to the remote repository
git push origin dev
```

## Maintenance Branches
- `dev-4.1.x` - Maintains older versions (e.g., dev-4.0.x and so on)

### Git Commands for Working with Maintenance Branches
```bash
# Switch to a maintenance branch
git checkout dev-4.1.x

# Pull the latest changes
git pull origin dev-4.1.x

# Create a bugfix branch from the maintenance branch
git checkout -b bugfix/issue-description dev-4.1.x

# Make your changes and commit them
git add .
git commit -m "Fix issue in 4.1.x"

# Push your bugfix branch to the remote repository
git push origin bugfix/issue-description

# When the bugfix is ready, merge it back to the maintenance branch
git checkout dev-4.1.x
git merge bugfix/issue-description

# Push the updated maintenance branch to the remote repository
git push origin dev-4.1.x

# If the bugfix should also be in the dev branch, cherry-pick the commit
git checkout dev
git cherry-pick <commit-hash>
git push origin dev
```

## Official Releases
- Official releases are tagged with names like `VER-4.1.10`

### Git Commands for Creating Official Releases
```bash
# Ensure you're on the correct branch (maintenance or dev)
git checkout dev-4.1.x

# Pull the latest changes
git pull origin dev-4.1.x

# Create a tag for the release
git tag -a VER-4.1.10 -m "Version 4.1.10 release"

# Push the tag to the remote repository
git push origin VER-4.1.10

# To list all release tags
git tag -l "VER-*"

# To check out a specific release
git checkout VER-4.1.10
```

## Hotfixes
- Hotfixes are named like `hotfix/4.1.10-nameofthehotfix`

### Git Commands for Creating and Managing Hotfixes
```bash
# Create a hotfix branch from the release tag
git checkout -b hotfix/4.1.10-security-fix VER-4.1.10

# Make your changes and commit them
git add .
git commit -m "Fix security issue in 4.1.10"

# Push your hotfix branch to the remote repository
git push origin hotfix/4.1.10-security-fix

# When the hotfix is ready, create a new release tag
git tag -a VER-4.1.11 -m "Version 4.1.11 hotfix release"
git push origin VER-4.1.11

# Merge the hotfix to the maintenance branch if needed
git checkout dev-4.1.x
git merge hotfix/4.1.10-security-fix
git push origin dev-4.1.x

# If the hotfix should also be in the dev branch, cherry-pick the commit
git checkout dev
git cherry-pick <commit-hash>
git push origin dev
```