# Package test ownership

The package's `tests/ownership.json` maps every published type to the actual package runner's discovered behavior and boundary tests, and records the package-owned conformance corpus. The quality gate validates the complete API inventory, test names and evidence paths. Nine negative fixtures prove that stale, missing or unowned evidence fails. New exports cannot land without an ownership entry. The runner refuses empty suites and empty cases in both execution and discovery modes.

Evidence references identify responsibility; they are not a claim of 100% line, branch or input coverage. Ports with no runtime implementation own their signatures and vocabulary here; concrete host implementations retain their execution tests. Package tests use neutral fixtures and adapters, and never bootstrap Kumwe App.

Core retains host composition, authority, persistence, delivery, lifecycle and recovery tests.
The canonical decimal corpus and pure package behavior belong here; consumers remove duplicate
unit tests only after verifying their assertions are covered by this package. A dependency update
does not justify deleting host acceptance tests.
