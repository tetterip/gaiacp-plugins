# GaiaCP plugins

The plugin catalogue of [GaiaCP](https://gaiacp.digital), the open source hosting billing and provisioning panel.
Browse it at **https://plugins.gaiacp.digital**; a GaiaCP admin installs and updates these plugins from the panel
under Settings → Plugins → Catalogue.

## How it works

- Each plugin is a folder in [`plugins/`](plugins), with its source code. Nothing is listed without being read first.
- Authors send a new plugin, or a new version, as a pull request. GitHub runs `php bin/catalogue check`; a maintainer
  reviews the code and merges.
- After a merge, [`publish.yml`](.github/workflows/publish.yml) builds the zips, `catalogue.json`, its Ed25519
  signature (`catalogue.json.sig`) and the web page onto the `catalogue` branch, which is what plugins.gaiacp.digital
  serves.
- GaiaCP trusts the catalogue only when the signature matches the public key in its own code, and checks each zip's
  SHA-256 from the signed file before installing it. Someone who took over the web hosting could not slip in a
  changed plugin.
- A published version is never rebuilt or replaced: a change means a new version number.

**Official** plugins are made by the GaiaCP project; **Community** plugins by others, reviewed before listing.
Reviewing is not a guarantee: a plugin runs with GaiaCP's full access, so install what you need from authors you
trust.

## Writing a plugin

Start with GaiaCP's `docs/writing-plugins.md` and `docs/plugin-tutorial.md`, then see [CONTRIBUTING.md](CONTRIBUTING.md)
for what the catalogue needs and how to send it. [`plugins/HelloWorld`](plugins/HelloWorld) is the smallest example.

## Running the builder yourself

Plain PHP 8.3 with the `zip` and `sodium` extensions:

```
php bin/catalogue check                         # the checks a pull request must pass
php bin/catalogue keygen                        # a signing key pair, for a catalogue of your own
CATALOGUE_SIGNING_KEY=... php bin/catalogue build --out=site --base-url=https://plugins.example.com
```

A catalogue of your own works too: GaiaCP reads it with `GAIACP_PLUGIN_CATALOGUE_URL` and
`GAIACP_PLUGIN_CATALOGUE_KEYS` (your public key) in its `.env`.

## Licence

The builder and this repository's files are GPL-3.0-or-later. Each plugin has its own licence, named in its
`plugin.json`.
