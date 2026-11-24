# SightPress module template

A starting point for your own SightPress module. SightPress is a powerful CMS for everyone, designed with blind and
low-vision creators and audiences in mind, built on modern, object-oriented PHP.

A module adds features to SightPress: pages, admin screens, block types, permissions, settings, search content,
sitemap entries and more. The template is an empty module that already passes every check: it installs, shows on the
**Features** screen, and can be turned on and off for each site. It has no example features; you add yours.

The step-by-step tutorial is in the SightPress documentation: **Create a module**
(<https://docs.sightpress.org/latest/tutorials/create-a-module/>). It builds an "Events" module from this template.

## Start

Either use this repository as a template on GitHub (**Use this template**), or:

```sh
composer create-project sightpress/module-template my-module
cd my-module
composer check
```

If you used **Use this template**, run `mv tools/module.gitattributes .gitattributes` and delete
`release-please-config.json`, `.release-please-manifest.json`, `.github/workflows/release-please.yml` and
`.github/rulesets/`, unless you want them. `composer create-project` does this for you.

`composer check` runs the code style, static analysis (PHPStan at level max), the unit tests with a 100% coverage
gate, and the integration tests, which install the module in SightPress (core is a dev dependency) and turn it off and
on for a site.

## Make it yours

1. **Package name:** in `composer.json`, set `name` (for example `acme/events`), `description`, `homepage` and
   `support`.
2. **Namespace and class:** rename `src/MyModule.php` and its namespace (`Acme\MyModule`), and update `autoload`,
   `autoload-dev` and `extra.sightpress.module` in `composer.json`, the namespace in `src/Migrations.php` and in
   `tests/`. Then run `composer dump-autoload`.
3. **Id:** in the class, set `ID`: lower-case words joined by `.`, `_` or `-`, starting with your vendor name, for
   example `acme.events`. Use the same id as the key under `extra.sightpress.features` in `composer.json`.
4. **What people read:** under `extra.sightpress.features.<id>`, write the `name`, `description` and `when-off` the
   Features screen shows to site admins, in plain words.
5. **Templates and texts:** set `NAMESPACE` (lower-case letters, digits and `_`, for example `acme_events`) and rename
   `translations/my_module.en-GB.xlf` to match. Templates are then `@acme_events/…`, and texts use
   `t('…', {}, 'acme_events')`.
6. Run `composer check` again.

## What a module may use

Only `sightpress/module-interface` (`SightPress\Module\…`). It is the contract between SightPress and its modules:
routes and screens, permissions, block types, settings, migrations, events, search, SEO, scheduled jobs, translations
and more. **Never depend on `sightpress/core` in `require`**; it is a dev dependency for the integration tests only.
If you need something the contract doesn't have, open an issue on
[module-interface](https://github.com/sightpress/module-interface/issues).

`composer.json` requires the contract at its latest minor version (`^0.5`). SightPress lists a module that needs
another version as unavailable, with the reason, and every site keeps working.

## Install it in a project

In a SightPress project, add your module as a Composer path repository while you work on it:

```sh
composer config repositories.my-module '{"type": "path", "url": "../my-module", "options": {"symlink": true}}'
composer require acme/events:@dev
bin/sightpress cache:clear
bin/sightpress site:module:list <site>
bin/sightpress site:module:enable <site> acme.events
```

Turning a module on for a site runs its database migrations. Turning it off hides everything it adds and keeps its
data; site admins can delete the data afterwards on the Features screen.

## Notes for module authors

- **Accessibility first.** Admin screens go in the admin shell through `Screens::admin()`, public pages in the site's
  theme through `Screens::public()`. Use real headings, labels for every field, error messages that say how to fix
  the problem, and nothing that only works with a mouse or only shows by colour. Test with the keyboard alone, at 320
  pixels and 400% zoom.
- **Plain words.** Texts people read are short and clear, in British English first, and go through the translations
  folder.
- **Portable migrations.** Migrations run on SQLite, MySQL, MariaDB and PostgreSQL: describe the schema with
  Doctrine's schema API, never raw engine-specific SQL, and give every migration a working `down()`.
- **Personal data.** Store as little as you can, and say in your README what you store and why.
- **Never load anything from other sites** (fonts, scripts, images) in what your module renders.

## Licence

GNU General Public License v3.0 or later. See [LICENSE](LICENSE). You may license your own module as you wish if it
contains no code from this template; most of it does, so GPL-3.0-or-later is the simplest choice.
