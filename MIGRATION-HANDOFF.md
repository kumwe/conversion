---
{
  "schema": "kumwe-migration-handoff/v2",
  "artifact_kind": "framework_php",
  "migration_id": "KUMWE-MIG-2026-031",
  "change_set": "KUMWE-CS-2026-031",
  "state": "draft_pr_open",
  "source": {
    "app": {
      "repository": "https://github.com/kumwe/app",
      "baseline_commit": "24ecf956423c18933e824b43cea1bfb9127a79a9",
      "examined_paths": [
        "composer.json",
        "tests/Unit/BusinessRecord/Domain/ExactDecimalTest.php",
        "docs/architecture/capability-index.md"
      ],
      "old_namespace_roots": [
        "Kumwe\\App\\BusinessRecord\\"
      ],
      "capability_index_sha256": "8fb2a8680bed6ac1456183bc9e48fe040194923b6d1b3331f04cea28bd5a9b2f"
    },
    "semantic_inputs": [],
    "examined_dependencies": [
      "PHP-only runtime; no Kumwe or third-party runtime dependency.",
      "Existing kumwe/conversion v0.1.3 at e95d5633722929e77b73005f8c44cfe4d99ac8c3 supplies unchanged public API and decimal corpus baseline."
    ],
    "active_related_pull_requests": [
      "https://github.com/kumwe/conversion/pull/11",
      "https://github.com/kumwe/extension-sdk/pull/15"
    ]
  },
  "target": {
    "repository": "https://github.com/kumwe/conversion",
    "artifact_identity": "kumwe/conversion",
    "canonical_namespace_or_abi": "Kumwe\\Conversion\\",
    "branch": "codex/v2-release-handoff-20260908",
    "pull_request": "https://github.com/kumwe/conversion/pull/11"
  },
  "ownership": {
    "responsibility": "Exact decimal/value/conversion behavior and provider ports with explicit caller-supplied evidence.",
    "non_responsibilities": [
      "Provider discovery, authorization and active catalogs",
      "Rate/unit tables and rounding policy selection",
      "Persistence, presentation, App composition",
      "Native runtime provisioning and Computation cutover"
    ],
    "allowed_dependency_ceiling": [
      "PHP"
    ],
    "implementation_owner": "kumwe/conversion",
    "next_consumer": "Independent release verification, then compatible package consumers; App integration remains a later task.",
    "public_manifests": [
      {
        "path": "resources/public-api/v1.json",
        "sha256": "9e90d5057e24441ab82c88b979a9b0a5a2fccb16e1d28410d8402ee0269af716"
      },
      {
        "path": "resources/public-api/legacy-v1.json",
        "sha256": "aa8302264a28005c0ff67148a2c9e61c11c956be85f3fa020d6f21688e98ba0d"
      },
      {
        "path": "resources/capabilities/v1.json",
        "sha256": "dcf2a3522936c181f54f5b00bdd11feee07f8f6386789361edb2d995c0b932d3"
      },
      {
        "path": "resources/service-map/v1.json",
        "sha256": "6c0a9fbf45da4a7667fd82fbee4717d555c617ba448687c85ba32a7b4c4c8131"
      },
      {
        "path": "resources/conformance/decimal-v1.tsv",
        "sha256": "635db251898707828e24f12b1abb672273552f5f633186a725cc9f50ac08140c"
      }
    ],
    "intentionally_excluded": [
      "All App source changes and duplicate-test deletion are deferred.",
      "The runtime API and decimal corpus are unchanged from 0.1.3; its profile manifest is preserved byte-identically at resources/public-api/legacy-v1.json.",
      "Host-owned catalogs and SPI activation remain outside this package."
    ]
  },
  "framework_php": {
    "composer_package": "kumwe/conversion",
    "canonical_namespace": "Kumwe\\Conversion\\",
    "public_api_manifest": "resources/public-api/v1.json",
    "capability_manifest": "resources/capabilities/v1.json",
    "service_map": "resources/service-map/v1.json",
    "extracted_symbols": [
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\ExactDecimal",
        "new_fqcn": "Kumwe\\Conversion\\Decimal\\ExactDecimal",
        "source_path": "src/BusinessRecord/Domain/ExactDecimal.php",
        "target_path": "src/Decimal/ExactDecimal.php",
        "kind": "class",
        "public_methods": [
          "__toString",
          "compare",
          "fromInt",
          "fromString",
          "value"
        ],
        "public_properties": [
          "precision",
          "scale"
        ],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\ExactDecimalArithmetic",
        "new_fqcn": "Kumwe\\Conversion\\Decimal\\ExactDecimalArithmetic",
        "source_path": "src/BusinessRecord/Domain/ExactDecimalArithmetic.php",
        "target_path": "src/Decimal/ExactDecimalArithmetic.php",
        "kind": "class",
        "public_methods": [
          "fromLiteral",
          "multiply",
          "round"
        ],
        "public_properties": [],
        "public_constants": [
          "MAXIMUM_PRECISION"
        ],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\ExactRoundingRule",
        "new_fqcn": "Kumwe\\Conversion\\Decimal\\ExactRoundingRule",
        "source_path": "src/BusinessRecord/Domain/ExactRoundingRule.php",
        "target_path": "src/Decimal/ExactRoundingRule.php",
        "kind": "interface",
        "public_methods": [
          "increments"
        ],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\MoneyValue",
        "new_fqcn": "Kumwe\\Conversion\\Value\\MoneyValue",
        "source_path": "src/BusinessRecord/Domain/MoneyValue.php",
        "target_path": "src/Value/MoneyValue.php",
        "kind": "class",
        "public_methods": [
          "__construct",
          "toArray"
        ],
        "public_properties": [
          "amount",
          "currency"
        ],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\QuantityValue",
        "new_fqcn": "Kumwe\\Conversion\\Value\\QuantityValue",
        "source_path": "src/BusinessRecord/Domain/QuantityValue.php",
        "target_path": "src/Value/QuantityValue.php",
        "kind": "class",
        "public_methods": [
          "__construct",
          "toArray"
        ],
        "public_properties": [
          "amount",
          "unit"
        ],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\MoneyRoundingMode",
        "new_fqcn": "Kumwe\\Conversion\\Value\\MoneyRoundingMode",
        "source_path": "src/BusinessRecord/Domain/MoneyRoundingMode.php",
        "target_path": "src/Value/MoneyRoundingMode.php",
        "kind": "enum",
        "public_methods": [
          "increments"
        ],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\QuantityRoundingMode",
        "new_fqcn": "Kumwe\\Conversion\\Value\\QuantityRoundingMode",
        "source_path": "src/BusinessRecord/Domain/QuantityRoundingMode.php",
        "target_path": "src/Value/QuantityRoundingMode.php",
        "kind": "enum",
        "public_methods": [
          "increments"
        ],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\MoneyExchangeRate",
        "new_fqcn": "Kumwe\\Conversion\\Value\\MoneyExchangeRate",
        "source_path": "src/BusinessRecord/Domain/MoneyExchangeRate.php",
        "target_path": "src/Value/MoneyExchangeRate.php",
        "kind": "class",
        "public_methods": [
          "__construct",
          "fromArray",
          "instant",
          "toArray"
        ],
        "public_properties": [
          "asAt",
          "baseCurrency",
          "provider",
          "quoteCurrency",
          "rate"
        ],
        "public_constants": [
          "INSTANT_FORMAT"
        ],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\UnitConversionFactor",
        "new_fqcn": "Kumwe\\Conversion\\Value\\UnitConversionFactor",
        "source_path": "src/BusinessRecord/Domain/UnitConversionFactor.php",
        "target_path": "src/Value/UnitConversionFactor.php",
        "kind": "class",
        "public_methods": [
          "__construct",
          "fromArray",
          "instant",
          "toArray"
        ],
        "public_properties": [
          "asAt",
          "factor",
          "provider",
          "sourceUnit",
          "targetUnit"
        ],
        "public_constants": [
          "INSTANT_FORMAT",
          "UNIT_PATTERN"
        ],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\ConvertedMoneyValue",
        "new_fqcn": "Kumwe\\Conversion\\Value\\ConvertedMoneyValue",
        "source_path": "src/BusinessRecord/Domain/ConvertedMoneyValue.php",
        "target_path": "src/Value/ConvertedMoneyValue.php",
        "kind": "class",
        "public_methods": [
          "__construct",
          "detect",
          "fromArray",
          "fromPortableString",
          "isPortableString",
          "toArray",
          "toPortableString"
        ],
        "public_properties": [
          "converted",
          "rate",
          "rounding",
          "source",
          "unrounded"
        ],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\ConvertedQuantityValue",
        "new_fqcn": "Kumwe\\Conversion\\Value\\ConvertedQuantityValue",
        "source_path": "src/BusinessRecord/Domain/ConvertedQuantityValue.php",
        "target_path": "src/Value/ConvertedQuantityValue.php",
        "kind": "class",
        "public_methods": [
          "__construct",
          "fromArray",
          "fromPortableString",
          "isPortableString",
          "toArray",
          "toPortableString"
        ],
        "public_properties": [
          "converted",
          "factor",
          "rounding",
          "source",
          "unrounded"
        ],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\MoneyConversionRequest",
        "new_fqcn": "Kumwe\\Conversion\\Contract\\MoneyConversionRequest",
        "source_path": "src/BusinessRecord/Domain/MoneyConversionRequest.php",
        "target_path": "src/Contract/MoneyConversionRequest.php",
        "kind": "class",
        "public_methods": [
          "__construct",
          "answeredBy"
        ],
        "public_properties": [
          "amount",
          "asAt",
          "precision",
          "rounding",
          "scale",
          "targetCurrency"
        ],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\UnitConversionRequest",
        "new_fqcn": "Kumwe\\Conversion\\Contract\\UnitConversionRequest",
        "source_path": "src/BusinessRecord/Domain/UnitConversionRequest.php",
        "target_path": "src/Contract/UnitConversionRequest.php",
        "kind": "class",
        "public_methods": [
          "__construct",
          "answeredBy"
        ],
        "public_properties": [
          "asAt",
          "precision",
          "quantity",
          "rounding",
          "scale",
          "targetUnit"
        ],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\MoneyConverter",
        "new_fqcn": "Kumwe\\Conversion\\Contract\\MoneyConverter",
        "source_path": "src/BusinessRecord/Domain/MoneyConverter.php",
        "target_path": "src/Contract/MoneyConverter.php",
        "kind": "class",
        "public_methods": [
          "convert"
        ],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Domain\\QuantityConverter",
        "new_fqcn": "Kumwe\\Conversion\\Contract\\QuantityConverter",
        "source_path": "src/BusinessRecord/Domain/QuantityConverter.php",
        "target_path": "src/Contract/QuantityConverter.php",
        "kind": "class",
        "public_methods": [
          "convert"
        ],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Application\\MoneyRateProvider",
        "new_fqcn": "Kumwe\\Conversion\\Provider\\MoneyRateProvider",
        "source_path": "src/BusinessRecord/Application/MoneyRateProvider.php",
        "target_path": "src/Provider/MoneyRateProvider.php",
        "kind": "interface",
        "public_methods": [
          "identifier",
          "rateFor",
          "supports"
        ],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Application\\UnitConversionProvider",
        "new_fqcn": "Kumwe\\Conversion\\Provider\\UnitConversionProvider",
        "source_path": "src/BusinessRecord/Application/UnitConversionProvider.php",
        "target_path": "src/Provider/UnitConversionProvider.php",
        "kind": "interface",
        "public_methods": [
          "factorFor",
          "identifier",
          "supports"
        ],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Application\\MoneyRateProviderCatalog",
        "new_fqcn": "Kumwe\\Conversion\\Provider\\MoneyRateProviderCatalog",
        "source_path": "src/BusinessRecord/Application/MoneyRateProviderCatalog.php",
        "target_path": "src/Provider/MoneyRateProviderCatalog.php",
        "kind": "interface",
        "public_methods": [
          "providersFor"
        ],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Application\\UnitConversionProviderCatalog",
        "new_fqcn": "Kumwe\\Conversion\\Provider\\UnitConversionProviderCatalog",
        "source_path": "src/BusinessRecord/Application/UnitConversionProviderCatalog.php",
        "target_path": "src/Provider/UnitConversionProviderCatalog.php",
        "kind": "interface",
        "public_methods": [
          "providersFor"
        ],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Application\\MoneyRateUnavailable",
        "new_fqcn": "Kumwe\\Conversion\\Provider\\MoneyRateUnavailable",
        "source_path": "src/BusinessRecord/Application/MoneyRateUnavailable.php",
        "target_path": "src/Provider/MoneyRateUnavailable.php",
        "kind": "class",
        "public_methods": [],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Application\\UnitConversionUnavailable",
        "new_fqcn": "Kumwe\\Conversion\\Provider\\UnitConversionUnavailable",
        "source_path": "src/BusinessRecord/Application/UnitConversionUnavailable.php",
        "target_path": "src/Provider/UnitConversionUnavailable.php",
        "kind": "class",
        "public_methods": [],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Application\\MoneyConversionPipeline",
        "new_fqcn": "Kumwe\\Conversion\\Provider\\MoneyConversionPipeline",
        "source_path": "src/BusinessRecord/Application/MoneyConversionPipeline.php",
        "target_path": "src/Provider/MoneyConversionPipeline.php",
        "kind": "class",
        "public_methods": [
          "__construct",
          "convert"
        ],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      },
      {
        "old_fqcn": "Kumwe\\App\\BusinessRecord\\Application\\UnitConversionPipeline",
        "new_fqcn": "Kumwe\\Conversion\\Provider\\UnitConversionPipeline",
        "source_path": "src/BusinessRecord/Application/UnitConversionPipeline.php",
        "target_path": "src/Provider/UnitConversionPipeline.php",
        "kind": "class",
        "public_methods": [
          "__construct",
          "convert"
        ],
        "public_properties": [],
        "public_constants": [],
        "exceptions": [
          "Declared typed refusals and invalid-input conditions are documented per public method in docs/public-api.md."
        ],
        "serialization_contract": "Existing public arrays and canonical decimal bytes remain unchanged; see source-generated API reference and decimal corpus.",
        "compatibility": "Historical rename already adopted by App; this governance successor preserves the 23 public type/member shapes unchanged."
      }
    ],
    "consumers": {
      "app_code": [
        "Historical BusinessRecord Domain/Application consumers in docs/app-agreement.md already use canonical package names."
      ],
      "configuration_and_di": [
        "Host constructors/factories supply MoneyRateProviderCatalog and UnitConversionProviderCatalog explicitly."
      ],
      "reflection_and_string_references": [
        "App extension-provider-v1 consumer records must retain the existing manifest profile and digest."
      ],
      "fixtures_and_examples": [
        "examples/direct-construction.php",
        "resources/conformance/decimal-v1.tsv",
        "App tests/Unit/BusinessRecord/Domain/ExactDecimalTest.php is a proven duplicate candidate."
      ],
      "external": [
        "kumwe/business-definition",
        "kumwe/record-values",
        "kumwe/conversion-extension",
        "kumwe/extension-sdk",
        "kumwe/engine semantic corpus"
      ]
    },
    "dependency_injection": {
      "mode": "direct",
      "provider": null,
      "factories": [],
      "aliases": [],
      "service_lifetimes": [
        "Caller-owned pipelines; host catalog scope and thread/process safety are not inferred from readonly pipeline properties."
      ],
      "configuration_keys": [],
      "provider_absence_reason": "Final readonly pipelines contain no mutable request state and accept explicit converter/catalog arguments. Hosts own authorized catalog selection and lifetime; no safe universal catalog or container default exists."
    }
  },
  "native_cpp": null,
  "php_extension": null,
  "tests": {
    "moved_or_added": [
      "Existing tests/Case/*.php and tests/ownership.json remain the portable behavior owner.",
      "tools/verify-v2-metadata.php rejects canonical/legacy manifest and handoff drift; tools/verify-archive.php rejects missing, extra or changed exported bytes.",
      "tools/render-public-api-docs.php rejects incomplete source/API documentation."
    ],
    "remain_in_app_or_consumer": [
      "Money Conversion, Unit Conversion and Converted Money Surface architecture tests",
      "Host persistence, authority, trusted activation, HTTP/CLI and integration tests"
    ],
    "split_tests": [
      "Only implementation assertions proven duplicated by this package may be removed later."
    ],
    "prohibited_duplicates": [
      "tests/Unit/BusinessRecord/Domain/ExactDecimalTest.php in App after the later approved adoption review."
    ],
    "corpora": [
      "resources/conformance/decimal-v1.tsv"
    ]
  },
  "documentation": {
    "charter": "CHARTER.md",
    "readme": "README.md",
    "public_api": "docs/public-api.md",
    "architecture": "docs/architecture.md",
    "integration_or_consumer": "docs/integration.md",
    "examples": [
      "examples/direct-construction.php"
    ],
    "changelog_record": "CHANGELOG.md 0.1.4"
  },
  "release_expectations": {
    "version_policy": "Exact pre-1.0 pins; 0.1.4 preserves runtime/API/corpus and adds v2 governance evidence.",
    "expected_artifact_types": [
      "GitHub source ZIP",
      "Composer archive"
    ],
    "required_checks": [
      "composer check",
      "Exact source/public API/corpus identity",
      "Offline no-dev authoritative archive consumer",
      "Release-on-record and registry source/dist equality",
      "Independent external release attestation"
    ],
    "required_registry_or_installer": "Packagist kumwe/conversion",
    "required_external_attestation": true
  },
  "next_task": {
    "phase_name": "Independent release verification and compatible package dependency train",
    "permitted_only_when": [
      "0.1.4 is actually published and exact source/tag/archive/registry identities pass independent verification.",
      "The external RELEASE-ATTESTATION.yaml accompanies this released handoff."
    ],
    "consumer_repository": "https://github.com/kumwe/extension-sdk",
    "dependency_or_native_change": "Reconcile the complete compatible PHP package graph to exact Conversion 0.1.4 only after release verification; do not modify App in this train.",
    "namespace_or_api_replacements": [
      "None in this release; the existing canonical 23-type namespace is unchanged."
    ],
    "files_to_update": [
      "composer.json",
      "composer.lock",
      "Package-owned release dependency/readiness metadata and changelog records"
    ],
    "files_to_remove": [],
    "tests_to_remove": [],
    "tests_to_retain_or_add": [
      "Every consumer-owned behavior/boundary/conformance suite",
      "Combined no-dev package graph install",
      "Native decimal corpus parity using unchanged corpus bytes"
    ],
    "di_or_provisioning_changes": [
      "None; direct constructor integration remains explicit."
    ],
    "capability_index_changes": [
      "Record the new capability/service-map and handoff coordinates in consumer evidence; no App index edit in this task."
    ],
    "changelog_and_evidence_changes": [
      "Record independent verification and dependency repin as separate observed states."
    ],
    "verification_commands": [
      "composer validate --strict",
      "composer check",
      "COMPOSER_DISABLE_NETWORK=1 php tools/verify-clean-consumer.php"
    ]
  },
  "concurrency": {
    "likely_conflict_files": [
      "composer.json",
      "CHANGELOG.md",
      "MIGRATION-HANDOFF.md",
      "resources/capabilities/v1.json",
      "resources/service-map/v1.json"
    ],
    "related_migrations": [],
    "ownership_conflicts": [],
    "integration_train": null,
    "resolution_rule": "semantic-preservation"
  },
  "governance": {
    "roadmap_source_sha256": "a202155ef1a65f5ab293d4f8397ebf4ac430db7f1e877c776bbe7851e6fe18d8",
    "roadmap_refs": [],
    "non_roadmap_refs": [],
    "completion_claim": false
  },
  "decisions": [
    "This is governance completion for an existing package, not a repeated source extraction.",
    "The JSON front matter is a valid YAML 1.2 mapping and is checked without introducing a runtime YAML dependency.",
    "No self commit/archive digest or future publication result is embedded.",
    "Stateless package pipelines do not certify collaborator concurrency or authority scopes."
  ],
  "blockers": [
    "Publication and independent external verification are required future events.",
    "Dependent repins and all App integration remain separate work."
  ]
}
---

## Migration/implementation summary

Version 0.1.4 adds the v2 handoff, capability/construction manifests and complete public API documentation. The 23 runtime types and decimal corpus remain byte-identical to 0.1.3. The canonical resources/public-api/v1.json now uses kumwe-package-public-api/v1; the previous manifest and extension-provider-v1 profile bytes are preserved at resources/public-api/legacy-v1.json. Future profile readers must use that explicit compatibility path. The historical rename inventory above is an existing adoption record, not a request to extract those App classes again.

## Public API and responsibility

[Public API](docs/public-api.md) contains every source-owned public type/member contract. [Architecture](docs/architecture.md) describes dependency direction and the intentional direct construction mode. Converters are pure rules; pipeline collaborators remain host-owned.

## Capability reuse/semantic input review

The package is PHP-only. It owns its decimal corpus and has no external semantic owner dependency. The existing public API profile and decimal corpus are verified against 0.1.3; no new rounding behavior, rates or tables are introduced.

## Consumer inventory

[App agreement](docs/app-agreement.md) lists all 23 historical renames and retained host SPI/catalog ownership. The independently read App snapshot already pins Conversion 0.1.2. Its ExactDecimalTest is a duplicate candidate; other consumer tests require an explicit assertion-by-assertion review before removal. This PR edits no App files.

## Test ownership

The existing package suite owns decimal/value/converter/provider behavior, refusal boundaries and the language-neutral corpus. [Test ownership](docs/test-ownership.md) records the mapped tests. Consumer host authority, persistence, lifecycle, HTTP/CLI and composition tests remain in their owners. Metadata and source-generated docs gain drift gates.

## Next-task execution notes

First verify the actual published 0.1.4 source/tag/archive/Packagist tuple, handoff/manifest/corpus digests, security and no-dev archive consumer. Store external evidence outside this package. Then release the compatible dependent package graph. Later App adoption refreshes its source baseline, exact-repins through Composer, and removes only proven duplicate unit tests; native cutover remains Computation-owned.

## Concurrency and conflict record

Coordinate Composer pins and release metadata across current package PRs. Preserve existing runtime API, compatibility profile and decimal corpus bytes. Recompute every public-manifest handoff digest after a metadata edit and rerun the final-head gates. No future tag/commit is assumed.

## Validation and remaining gates

Run composer check, the exported archive validator and the independent offline consumer. The host release workflow re-runs the same quality gate. This embedded handoff records expectations; publication, external verification and consumer adoption remain separate observed events.
