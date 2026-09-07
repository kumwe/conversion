# Releasing the conversion library

Follow the [Package release standard](package-release-standard.md) for the shared
quality gate, changelog parsing, publication and retry behavior. Complete the
[repository release setup](repository-release-setup.md) with an administrator
session before merging a release record:

```bash
bash tools/configure-release-repositories.sh --check kumwe/conversion
bash tools/configure-release-repositories.sh --apply kumwe/conversion
```

The required CI check is **Package gate**. Maintainers rebase reviewed PRs into the
repository's current default branch; the release workflow reruns the same quality
gate on the resulting commit and derives its release identity from that run.
A release intention in CHANGELOG.md is not evidence that publication occurred.
Keep work that is not ready for publication under `## Unreleased`.

The library versions independently under semantic versioning. Alignment with Kumwe
App travels through the exact pin in [app-agreement.md](app-agreement.md), never
through matching version numbers.

## Version policy

- **Patch:** behavior fixes that preserve exported shapes, grammar and refusal semantics.
- **Minor:** new capability that no existing consumer must act on.
- **Major:** a change a consumer must act on; the App agreement's rename record and
  pinned behavior make an observable contract difference a major change in review.
- During extraction the library stays `0.x`, and the App pins exactly.

The complete package gate, actual Composer archive and fresh authoritative no-dev
consumer checks remain required. Release workflow changes do not waive the App
agreement or package-owned behavior, boundary and conformance evidence.

## Publication evidence and recovery

The maintainer performs the initial Packagist submission. Its GitHub integration
then follows tags without a registry credential in CI. Before dependent publication
or App adoption, a fresh independent verifier must bind the exact published
source/tag, archive digest, manifests, registry coordinate, license/security and
clean-consumer results in an external RELEASE-ATTESTATION.yaml. The artifact and
handoff must not invent their own final commit, checksum or publication evidence.

Use the current release workflow on the default branch to retry after correcting
repository settings. Historical mutable releases remain unchanged: enabling
immutability affects future publications, so a mutable version requires an unused
successor. Never move or delete a published tag or replace a released artifact.
An unpublished tag can be completed only on the exact commit tested by the retry.
A green PR does not replace the default-branch release result or independent
verification. Administrator credentials do not belong in Actions.
