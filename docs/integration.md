# Direct integration

Install the independently verified exact pre-1.0 version with Composer. Use [the runnable example](../examples/direct-construction.php) for direct construction of exact values and both conversion rules. Host factories may construct MoneyConversionPipeline from MoneyConverter and MoneyRateProviderCatalog, or UnitConversionPipeline from QuantityConverter and UnitConversionProviderCatalog. Hosts implement catalog interfaces from their authorized provider set; this package does not install any catalog implementation.

The public [API reference](public-api.md), [App agreement](app-agreement.md), [architecture](architecture.md), and [handoff](../MIGRATION-HANDOFF.md) specify the complete ownership and adoption boundary. Production consumers must retain converted result provenance; rates/tables, authorization, trusted activation, storage and presentation remain host-owned. Catalog concurrency and lifetime are host responsibilities.

Package verification is `composer check`; independent no-dev archive verification is `php tools/verify-clean-consumer.php`. The offline consumer uses `COMPOSER_DISABLE_NETWORK=1 php tools/verify-clean-consumer.php` after tool dependencies are provisioned. The released source includes its handoff, charter, public docs, examples and three public manifests, but no package tests, test oracle, vendor tree or development tooling.
