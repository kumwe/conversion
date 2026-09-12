# Conversion architecture and construction

The four runtime layers and their import ceiling are specified in [Engineering standard](engineering-standard.md). Decimal owns canonical exact values/arithmetic; Value owns typed amounts and conversion evidence; Contract owns conversion requests/rules; Provider owns only ports and the two pipelines. Production depends only on PHP.

The pipelines are final readonly objects. They receive their converter and provider catalog through constructors, keep no mutable operation state, and read each request's explicit as-at instant and rounding policy. The catalog is a host-owned collaborator: readonly pipeline properties do not make an external catalog immutable or thread-safe. Provider calls may perform host-defined I/O; arithmetic and value types themselves have no I/O, clocks, ambient globals or discovery.

There is intentionally no ConfigProvider or factory default. The package cannot choose an authorized provider catalog, tenant scope or safe host lifetime. A host can register its own explicit factory which constructs a pipeline from the host's catalog and the directly constructed pure converter. No shared service may capture a per-request catalog across authority scopes. The [service map](../resources/service-map/v1.json) records this construction mode.

The standard API projection is `resources/public-api/v1.json`; provider-profile consumers use
`resources/public-api/legacy-v1.json`. All 23 public types and the decimal corpus have package-owned
compatibility checks. Core qualifies exact release pins and retains its own acceptance tests.
Native acceleration belongs to the separately governed Computation/Engine boundary.
