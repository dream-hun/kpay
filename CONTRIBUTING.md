# Contributing

Thanks for contributing!

## Commit message convention (Conventional Commits)

We use **Conventional Commits** so changes are easy to read and can be used for changelogs/releases.

### Format

```
<type>(<scope>)!: <subject>

<body>

<footer>
```

- **type**: required (see list below)
- **scope**: optional (e.g. `http`, `config`, `docs`, `tests`)
- **!**: optional, indicates a breaking change
- **subject**: required, short description in imperative mood (no trailing period)
- **body**: optional, what/why
- **footer**: optional, references and/or breaking-change details

### Allowed types

- **feat**: new feature
- **fix**: bug fix
- **docs**: documentation-only changes
- **refactor**: code change that neither fixes a bug nor adds a feature
- **perf**: performance improvement
- **test**: adding or fixing tests
- **build**: build system or dependency changes
- **ci**: CI configuration changes
- **chore**: maintenance tasks (no production code change)
- **style**: formatting-only (no logic change)
- **revert**: revert a previous commit

### Examples

```
feat(http): add checkStatus endpoint
```

```
fix(client): return PendingRequest from client builder
```

```
refactor(config)!: rename kpay defaults keys

BREAKING CHANGE: configuration keys under kpay.defaults were renamed; update your published config.
```

### Tips

- Keep commits small and focused.
- If you need to fix your commit message, use `git commit --amend` (or interactive rebase for multiple commits).
