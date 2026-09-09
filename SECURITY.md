# Security

## Reporting a vulnerability

Please do not open a public issue for a security problem.

Email **security@dicecodes.com** with the details, or report it privately through
[GitHub's advisory form](https://github.com/IamRamgarhia/dicebar/security/advisories/new).

Tell us what version you tested, what an attacker can do, and the smallest steps
that reproduce it. We will confirm receipt, agree a disclosure date with you, and
credit you in the release notes unless you would rather we did not.

## Supported versions

The current release receives security fixes. Because this plugin is distributed
through the WordPress plugin directory, the fastest way for everyone to receive a
fix is a new release, so that is what we ship rather than backports.

## What the plugin does and does not do

Knowing the surface helps when assessing a report.

- It makes **no external network requests** of any kind, at any time.
- It **stores two options** and nothing else. It writes no files and creates no
  database tables.
- It **collects no data** about visitors and sends nothing anywhere.
- Every setting is rebuilt from defaults on save, so a crafted request cannot
  introduce keys the plugin does not recognise.
- Item addresses are restricted to five schemes: `http`, `https`, `tel`,
  `mailto` and `sms`. Anything else is rejected on save, including for
  administrators.
- The custom CSS field strips tags and removes the constructs that have
  historically executed script.
- Uploading SVG icons is deliberately not supported, because a crafted SVG is a
  script execution vector.

If you find a way around any of those, we want to hear about it.
