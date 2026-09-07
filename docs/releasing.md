# Releasing the conversion library

The library versions independently under semantic versioning; alignment with Kumwe App travels
through the exact pin in [`app-agreement.md`](app-agreement.md), never through matching version
numbers.

Releasing is merging. Every Kumwe PHP library delivers the same way:

1. Land the work on `main` with its `CHANGELOG.md` section for the next version — the heading
   `## X.Y.Z - date` is the release record.
2. The `Release on record` workflow runs on every push to `main`: it installs the validated
   development toolchain, re-proves the complete check lane, reads the newest recorded version, and
   identifies the release represented by the pushed commit. When the version has no tag yet it
   creates `vX.Y.Z` at that commit through the repository API and publishes the GitHub release.
   When the tag already exists, it must resolve directly to a commit in the pushed `main` history
   whose newest changelog record is the same version. Nobody pushes or moves a tag by hand; a push
   that records no new version is a verification-only run.
3. Packagist follows tags through its GitHub integration — submit `kumwe/conversion` once at
   packagist.org and every later release appears without a credential in this repository.

Version policy:

- **Patch** — behaviour fixes that keep every exported shape, grammar, and refusal identical.
- **Minor** — new capability that no existing consumer must act on.
- **Major** — a change a consumer must act on; the App agreement's rename record and pinned
  behaviour make any observable difference a major by definition.
- While the extraction is settling, the library stays `0.x` and the App pins exactly.

## Release integrity prerequisites

Before merging the recorded patch release, a maintainer must protect `main` and enable immutable releases in the repository or applicable organization policy. The release job refuses an unprotected ref before tag/publication mutations and requires the exact stable version to be published with `immutable: true`. A pre-existing mutable release fails verification. Changing settings now does not make past mutable releases independently verified.

The shared release-heading parser is tested against malformed records and is used for both the pushed changelog and an existing tag. Source/tag checks, all package tests, true archive checks and dependency audit remain required. A fresh independent release verifier and exact artifact evidence are still required before dependent publication or App adoption. Agents open reviewable PRs; maintainers merge and publication follows the recorded version.
