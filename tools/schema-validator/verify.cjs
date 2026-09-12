'use strict';

// Development-only Draft 2020-12 validation of complete authoritative package contracts.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const Ajv2020 = require('ajv/dist/2020').default;
const root = path.resolve(__dirname, '../..');
if (Number(process.versions.node.split('.')[0]) < 20) {
  throw new Error('The schema gate requires Node.js 20 or newer.');
}
const ajv = new Ajv2020({ allErrors: true, strict: false });
const read = relative => fs.readFileSync(path.join(root, relative), 'utf8');
const parseHandoff = text => {
  if (!text.startsWith('---\n')) throw new Error('Handoff opening front-matter boundary is missing.');
  const boundary = text.indexOf('\n---\n', 4);
  if (boundary < 0) throw new Error('Handoff closing front-matter boundary is missing.');
  // This package intentionally writes JSON-compatible YAML 1.2, keeping its PHP verifier dependency-free.
  return JSON.parse(text.slice(4, boundary));
};
const documents = new Map();
for (const kind of ['public-api', 'capabilities', 'service-map', 'handoff']) {
  const schemaPath = 'tools/schemas/' + (kind === 'handoff'
    ? 'package-release-record.v1.schema.json' : 'package-' + kind + '.v1.schema.json');
  const schema = JSON.parse(read(schemaPath));
  const validate = ajv.compile(schema);
  const value = kind === 'handoff' ? parseHandoff(read('docs/release-record.md'))
    : JSON.parse(read('resources/' + kind + '/v1.json'));
  if (!validate(value)) throw new Error(kind + ': ' + ajv.errorsText(validate.errors, { separator: '\n' }));
  documents.set(kind, { value, validate });
}
const reject = (kind, mutate) => {
  const { value, validate } = documents.get(kind);
  const candidate = structuredClone(value);
  mutate(candidate);
  assert.equal(validate(candidate), false, kind + ': malformed fixture was accepted');
};
reject('capabilities', value => { value.native_requirements = []; });
reject('capabilities', value => { delete value.responsibility; });
reject('capabilities', value => { value.capabilities[0].symbols = 'not-an-array'; });
reject('public-api', value => { delete value.symbols[Object.keys(value.symbols)[0]].kind; });
reject('public-api', value => { value.release = 'dev-main'; });
reject('service-map', value => { value.factories = {}; });
reject('service-map', value => { value.undeclared_field = true; });
reject('handoff', value => { delete value.target; });
reject('handoff', value => { value.source.app.baseline_commit = 'not-a-commit'; });
reject('handoff', value => { value.artifact_kind = 'unrecognized'; });
assert.throws(() => parseHandoff('---\n{"blockers": ["unterminated]}\n---\n'), SyntaxError);
assert.throws(() => parseHandoff('---\n{}\n'), /closing front-matter boundary/);
console.log('All 3 canonical manifests and full release record satisfy authoritative Draft 2020-12 schemas.');
console.log('Schema gate passed 12 malformed, missing-field, nullability and front-matter refusal fixtures.');
