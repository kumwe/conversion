---
{
  "schema": "kumwe-package-release-record/v1",
  "artifact_kind": "framework_php",
  "migration_id": "KUMWE-MIG-2026-031",
  "change_set": "KUMWE-CS-2026-031",
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
    ]
  },
  "target": {
    "repository": "https://github.com/kumwe/conversion",
    "artifact_identity": "kumwe/conversion",
    "canonical_namespace_or_abi": "Kumwe\\Conversion\\"
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
    "next_consumer": "Extension SDK, conversion providers and host implementations including Kumwe Core, each qualified against an exact package release.",
    "public_manifests": [
      {
        "path": "resources/public-api/v1.json",
        "sha256": "e2e3c0e3fb5127d38b382cb4779ff7d0343ab0ee0c3ea48264472e75b30298e3"
      },
      {
        "path": "resources/public-api/legacy-v1.json",
        "sha256": "aa8302264a28005c0ff67148a2c9e61c11c956be85f3fa020d6f21688e98ba0d"
      },
      {
        "path": "resources/capabilities/v1.json",
        "sha256": "fd7946e41e724ee071dc619105cd06ac6c29f90e159e40b5ad5267d3d6c4237b"
      },
      {
        "path": "resources/service-map/v1.json",
        "sha256": "bc9a36835e305643cc24af4e698551726750425c1869fc525411f561c73fc4d7"
      },
      {
        "path": "resources/conformance/decimal-v1.tsv",
        "sha256": "635db251898707828e24f12b1abb672273552f5f633186a725cc9f50ac08140c"
      }
    ],
    "intentionally_excluded": [
      "Host application source, provider authorization, persistence and presentation remain consumer-owned.",
      "The canonical runtime API, compatibility profile and decimal corpus are governed by their recorded manifests.",
      "Host catalogs and SPI activation remain outside this package."
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
      "tools/render-public-api-docs.php rejects incomplete source/API documentation.",
      "tools/schema-validator/verify.cjs validates all canonical manifests and the full handoff, and rejects malformed/nullability/incomplete-contract fixtures."
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
    "changelog_record": "CHANGELOG.md 0.1.5"
  },
  "release_expectations": {
    "version_policy": "Exact pre-1.0 pins with explicit compatibility qualification; public API and decimal corpus changes require reviewed versioned contracts.",
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
  "governance": {
    "completion_claim": false
  },
  "decisions": [
    "JSON-compatible YAML permits dependency-free package metadata checks.",
    "Release identity and archive digests are established externally from published bytes.",
    "Stateless pipelines do not certify collaborator concurrency or authority scopes."
  ],
  "blockers": [],
  "consumer_contract": {
    "permitted_only_when": [
      "The exact published source/tag/archive/registry identities pass independent verification.",
      "External RELEASE-ATTESTATION.yaml binds the release record, manifests and corpus."
    ],
    "consumer_repository": "https://github.com/kumwe/extension-sdk",
    "dependency_or_native_change": "Resolve the consumer graph to a qualified exact Conversion release; provider catalogs and native provisioning remain host-owned.",
    "namespace_or_api_replacements": [
      "None in this release; the existing canonical 23-type namespace is unchanged."
    ],
    "files_to_update": [
      "Consumer composer.json and composer.lock",
      "Consumer package ownership and release evidence"
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
      "Record the installed capability, service-map and release-record coordinates in consumer evidence."
    ],
    "changelog_and_evidence_changes": [
      "Record independent verification and dependency repin as separate observed states."
    ],
    "verification_commands": [
      "composer validate --strict",
      "composer check",
      "COMPOSER_DISABLE_NETWORK=1 php tools/verify-clean-consumer.php"
    ]
  }
}
---

# Conversion release contract record

## Package contract

The package owns exact decimals, typed amounts, conversion requests, pipelines and provider ports.
It supplies no rates, unit tables, provider discovery or host authority.

## Public API and responsibility

The [API reference](public-api.md) and [architecture](architecture.md) describe all 23 runtime types.
The provider profile remains at `resources/public-api/legacy-v1.json`; the standard API projection
is `resources/public-api/v1.json`. Both are digest-verified compatibility contracts.

## Dependencies and semantic inputs

Production requires PHP only. Conversion owns its language-neutral decimal corpus and arithmetic
semantics. Provider implementations supply rate or factor evidence and caller-selected rounding.

## Consumer contract

The [Core/App agreement](app-agreement.md) retains the exact pin protocol, canonical symbol map,
provider profile and consumer authority boundaries. Historical source baselines are provenance;
consumers inspect current sources before replacing duplicate implementations or tests.

## Test ownership

Package tests own decimal/value/converter/pipeline behavior and refusal boundaries. Hosts retain
authorization, persistence, lifecycle, presentation, HTTP/CLI and composition acceptance tests.
See [test ownership](test-ownership.md).

## Consumer verification

Verify actual published source, tag, archive, registry and manifest/corpus identities. Install the
original ZIP without development requirements, run its examples, and replay the same lock offline.
Store independent evidence separately from the package.

## Compatibility and drift

Maintain the complete canonical API, provider profile and decimal corpus. Manifest changes require
explicit digest updates and compatible consumer pins. Native acceleration belongs to Computation
and Engine and does not change this PHP package ownership.

## Validation

Run `composer check`, `composer clean-consumer` and the release automation regressions.
The source record states requirements; external attestations establish release qualification.
