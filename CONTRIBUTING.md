# Contributing to DiceBar

Thanks for wanting to help. This is a small plugin with a strict quality gate,
so a pull request that passes the checks below is usually easy to merge.

## Getting set up

You need Node, Composer, PHP and Docker.

```bash
npm install && composer install
npm run env:rebuild
```

`env:rebuild` tears the WordPress container down and builds it again from
nothing. It exists because a plain `wp-env start` is unreliable: MySQL reports
its container started before the server accepts connections, and an interrupted
start leaves containers that block the next one. The script waits for the server
and clears the leftovers.

## Before you open a pull request

All four of these have to pass.

```bash
composer lint        # WordPress coding standards and PHP 7.4 compatibility
npm run test:php     # PHPUnit against a real WordPress
npm run check        # the official Plugin Check
npm run verify:zip   # unpacks the archive the way WordPress does
```

`composer lint:fix` fixes most formatting complaints automatically.

## House rules

**Prefix everything.** Functions, classes, constants, options, hooks, CSS
classes, custom properties and JavaScript globals all start with `dicebar`. An
unprefixed global in a WordPress plugin is a collision waiting to happen, and
the directory rejects it.

**Sanitise on the way in, escape on the way out.** Never assume a value is safe
because it was sanitised earlier. Escape at the point of output, every time, with
the function that matches the context.

**Five URL schemes, and no others.** Web, secure web, telephone, email and text
message. Do not widen this list. An item's address comes from an administrator,
and administrator accounts get compromised.

**Nonce and capability, in that order.** A nonce is not an authorisation check
and a capability check is not a request-forgery check. Every write needs both.

**No external requests.** The plugin contacts nothing. This is not a performance
preference, it is why the directory trusts it.

**Files under 400 lines, one responsibility per class.** Rendering never queries
and querying never echoes.

**Bump the version properly.**

```bash
npm run version:set -- 1.7.0
```

That writes it to all four places that must agree. A test asserts they match,
because a plugin header and a readme stable tag that disagree ship the wrong code
with no warning at all.

## Reporting a bug

Please say what you expected, what happened, your WordPress and PHP versions,
your theme, and whether a caching or optimising plugin is active. A screenshot of
the settings screen or the bar itself resolves most reports in one round.
