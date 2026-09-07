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

Before merging the recorded patch release, a maintainer must protect `main` and enable immutable
releases in the repository or applicable organization policy. The release job reads the current
`main` branch metadata and refuses an unprotected branch before tag/publication mutations. It also
requires the exact stable version to be published with `immutable: true`. A pre-existing mutable
release of that exact version fails verification. Changing settings now does not make past mutable
releases independently verified.

### Configure the repository before retrying

Run [34128686388](https://github.com/kumwe/conversion/actions/runs/34128686388) passed the package,
archive and fresh-consumer checks, then refused publication because `main` was unprotected. The
pending release record is `0.1.3`; Packagist linkage cannot enable GitHub branch protection or
release immutability.

1. In [Settings > Rules > Rulesets](https://github.com/kumwe/conversion/settings/rules), import
   [`.github/rulesets/main.json`](../.github/rulesets/main.json). Confirm enforcement is **Active**.
   The ruleset requires pull requests and the existing `PHP 8.5` check, prevents deletion and force
   pushes, and has no bypass actors. It does not require a second maintainer's approval. The file
   alone does not activate protection: it must be imported by a repository administrator. If an
   applicable rule already exists, update that rule instead of adding a duplicate.
2. In [Settings > General](https://github.com/kumwe/conversion/settings), under **Releases**, enable
   **Enable release immutability**, or confirm an organization policy enforces it for this repo.
   [GitHub applies this setting only to future releases](https://docs.github.com/en/code-security/how-tos/secure-your-supply-chain/establish-provenance-and-integrity/prevent-release-changes).
   Enable it before retrying: verification occurs after publication, so a disabled setting would
   publish a mutable version and then fail. Old mutable `0.1.0`–`0.1.2` releases do not prevent a new
   immutable `0.1.3`; do not recreate or move old tags.
3. Verify both settings with an administrator-authenticated GitHub CLI:

   ```bash
   gh api repos/kumwe/conversion/branches/main \
     --jq '.name == "main" and .protected == true'
   gh api repos/kumwe/conversion/immutable-releases --jq '.enabled == true'
   ```

   Both must print `true`. A permission error is not proof that a setting is enabled or disabled.
   The [immutability settings API requires Administration:read](https://docs.github.com/en/rest/repos/repos#check-if-immutable-releases-are-enabled-for-a-repository),
   which the normal workflow token does not have. Do not add administrator credentials to CI.

   Alternatively, an administrator can import the ruleset and enable immutability from this
   checkout using these commands (import the ruleset once):

   ```bash
   gh api --method POST repos/kumwe/conversion/rulesets \
     --input .github/rulesets/main.json
   gh api --method PUT repos/kumwe/conversion/immutable-releases
   ```

4. Merge the fix after CI passes. The push to `main` runs the complete package gate and publishes
   the pending version. After this workflow is on `main`, it also supports **Run workflow**, using
   branch **main**, or:

   ```bash
   gh workflow run release-on-record.yml --repo kumwe/conversion --ref main
   ```

   Dispatches on other branches cannot publish. A retry still runs the complete package gate and
   retains tag ancestry, exact-version and immutable-publication checks. Before this workflow
   change is merged, a new push is the reliable way to obtain a fresh protection context.

If the exact pending version has already been published mutable, stop and prepare a new patch
record; enabling the setting does not retrofit that release. If an unpublished tag exists at a
different commit, the workflow refuses it rather than moving it to unverified content.

The shared release-heading parser is tested against malformed records and is used for both the pushed changelog and an existing tag. Source/tag checks, all package tests, true archive checks and dependency audit remain required. A fresh independent release verifier and exact artifact evidence are still required before dependent publication or App adoption. Agents open reviewable PRs; maintainers merge and publication follows the recorded version.
