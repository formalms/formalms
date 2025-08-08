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
A hotfix is a minimal, urgent change applied to an already released version to resolve a critical issue that cannot wait for the normal release cycle.

Use a hotfix when:
- Security vulnerabilities, service outages, data loss/corruption, or blocking bugs affect production users.
- The fix is small and low‑risk, with no schema changes or new features.

Rules:
- Base: branch from the latest release tag (e.g., VER-4.1.10) or from the maintenance branch tracking that line (e.g., dev-4.1.x) if no tag exists yet.
- Naming: `hotfix/<version>-<short-description>` (e.g., `hotfix/4.1.10-security-fix`).
- Scope: smallest possible change; avoid refactors and new features.
- Tests: add/adjust unit/functional tests proving the regression is fixed.
- Versioning: results in a patch bump (e.g., 4.1.10 → 4.1.11).
- Backport/forward-port: merge back to the relevant maintenance branch and cherry-pick to `dev` to keep lines in sync.

### Git Commands for Creating and Managing Hotfixes
```bash
# Create a hotfix branch from the release tag
git checkout VER-4.1.10
git checkout -b hotfix/4.1.10-security-fix

# Make your changes and commit them
git add .
git commit -m "fix(security): patch vulnerability in 4.1.10"

# Push your hotfix branch to the remote repository
git push origin hotfix/4.1.10-security-fix

# When the hotfix is ready, create a new patch release tag
git tag -a VER-4.1.11 -m "Version 4.1.11 hotfix release"
git push origin VER-4.1.11

# Merge the hotfix into the maintenance branch for that line
git checkout dev-4.1.x
git merge --no-ff hotfix/4.1.10-security-fix
git push origin dev-4.1.x

# Also forward-port to the dev branch (if applicable)
git checkout dev
git cherry-pick <commit-hash>
git push origin dev
```

## Features
A feature introduces new functionality or significant improvements that are not urgent and should follow the normal release cycle.

Use a feature when:
- You add a new capability, UI/UX flow, or integration.
- You make non-breaking improvements or refactors tied to new behavior.

Rules:
- Base: branch from `dev`.
- Naming: `feature/<short-description>` (e.g., `feature/new-messages-flow`).
- Scope: keep the branch focused on one coherent deliverable; split large items into smaller PRs.
- Tests: include unit/functional tests and documentation updates.
- Commit messages: follow Conventional Commits (e.g., `feat(messages): add threaded replies`) — see https://www.conventionalcommits.org/.
- Versioning: features normally contribute to a minor version bump (e.g., 4.1.x → 4.2.0).
- Merging: merge back into `dev` (prefer `--squash` to keep history clean). Do not merge features into maintenance branches; exceptions require explicit approval.

### Git Commands for Creating and Managing Features
```bash
# Create a feature branch from dev
git checkout dev
git pull origin dev
git checkout -b feature/nameofthefeature

# Make your changes and commit them
git add .
git commit -m "feat(messages): create new flow for messages"

# Push your feature branch to the remote repository
git push origin feature/nameofthefeature

# Merge the feature into the dev branch (squash recommended)
git checkout dev
git pull origin dev
git merge --squash feature/nameofthefeature
git commit -m "feat(context): feature description"
git push origin dev

# Release happens separately when preparing a new version tag from dev
# (example)
# git tag -a VER-4.2.0 -m "Version 4.2.0"
# git push origin VER-4.2.0
```