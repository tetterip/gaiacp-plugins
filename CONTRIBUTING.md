# Sending a plugin to the catalogue

Thank you for writing one. A plugin runs inside other people's billing panels, next to their clients' details and
their server passwords, so every plugin and every new version is read by a maintainer before it is listed.

## 1. The folder

Your plugin goes in `plugins/<Folder>/`, as GaiaCP loads it (see GaiaCP's `docs/writing-plugins.md`). Its
`plugin.json` needs, besides `version`, `requires` and `class`:

```json
{
    "version": "1.0.0",
    "requires": "0.1.0",
    "class": "Plugins\\MyPanel\\MyPanelPlugin",
    "name": "My Panel",
    "description": {"en": "Creates and suspends hosting accounts on My Panel.", "el": "…"},
    "category": "Servers",
    "author": {"name": "Your name", "url": "https://example.com"},
    "homepage": "https://example.com/gaiacp-my-panel",
    "licence": "GPL-3.0-or-later",
    "php": "8.3"
}
```

- `category`: Servers, Registrars, Monitoring, Payments, Integrations or Themes.
- `description` may be one English text, or one per language (`en` is required).
- `licence`: an SPDX name. GaiaCP is GPL-3.0-or-later; a plugin should use a GPL-compatible licence.
- `homepage` and `php` (the oldest PHP it needs) are optional.
- `CHANGELOG.md` has a `## [1.0.0] - 2026-10-25` section for every version, newest first.
- No dot files, symbolic links, `vendor/` you did not write (copy only what you need, with its licence), or built
  files you can't show the source of.

Run `php bin/catalogue check MyPanel` before you open the pull request.

## 2. The pull request

One plugin per pull request. A new version changes only that plugin's folder: bump `version`, add the CHANGELOG
section. A version that was published can never be changed, only followed by a newer one.

## 3. What the review looks at

A plugin is listed when:

- It does what its description says, and nothing else.
- It sends data only to the service the admin set it up for, and only what that service needs. No tracking, no
  "phone home", no ads.
- It loads no code from outside its folder (no downloading and running scripts, no `eval`).
- Passwords and API keys are stored encrypted (`encryptedSettingKeys()`), never logged in plain text, and never sent
  back to the browser.
- API calls go through `ModuleLogger::middleware()`, so the admin can see them in the Module log.
- Texts go through `__()`, so the panel can be translated.
- Disabling it keeps its data; it doesn't change GaiaCP's own tables except through GaiaCP's classes.
- The code is readable (not minified or obfuscated).

The maintainer may ask for changes, and may remove a plugin that stops working, is abandoned, or turns out to break
these rules. Security problems in a listed plugin: see [SECURITY.md](SECURITY.md).

## 4. After the merge

The catalogue is rebuilt and signed by GitHub Actions within a few minutes. GaiaCP installs see the new version the
next time they read the catalogue (daily, or with Refresh under Settings → Plugins → Catalogue).
