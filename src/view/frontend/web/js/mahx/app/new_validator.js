import JustValidate from 'just-validate';
import match from 'match-operator';

/**
 * Convert dot-notated name (e.g., "street.0" or "additionalData.po_number")
 * into HTML field name format (e.g., "street[0]" or "additionalData[po_number]").
 */
function getElementByDotName(form, path) {
  const parts = path.split('.');
  let name = parts[0];

  for (let i = 1; i < parts.length; i++) {
    name += `[${parts[i]}]`;
  }

  return form.elements.namedItem(name);
}

/**
 * Convert input name like "street[0][line]" to dot-notated name like "street.0.line".
 *
 * @param {HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement} input
 * @returns {string}
 */
function getDotNameFromElement(input) {
  return input.name.replace(/\[(\w+)\]/g, '.$1');
}

/**
 * @typedef {Object} ValidatorOptions
 * @property {HTMLFormElement} form - The form element to validate.
 * @property {Object.<string, string>} [rules={}] - Validation rules mapped by input name.
 * @property {Object.<string, string>} [messages={}] - Custom messages for validation rules.
 * @property {Object.<string, string>} [aliases={}] - Aliases for form field labels.
 * @property {Object} [globalConfig={}] - Global configuration for JustValidate.
 */

/**
 * Initialize and configure form validation.
 *
 * @param {ValidatorOptions} options - Configuration options for the validator.
 * @returns {Object} - Validator instance with `validate` method.
 */
export default function Validator({
  form,
  rules = {},
  messages = {},
  aliases = {},
  globalConfig = {},
}) {
  const defaultConfig = { errorFieldCssClass: ['input-error'] };
  const jv = new JustValidate(form, { ...defaultConfig, ...globalConfig });

  const getElementValue = (name) => form.elements[name]?.value;

  const isAll = (fields, predicate) => fields.every((field) => predicate(getElementValue(field)));
  const isAny = (fields, predicate) => fields.some((field) => predicate(getElementValue(field)));


  Object.entries(rules).forEach(([name, ruleString]) => {
    const input = getElementByDotName(form, name);
    if (!input) return;

    const ruleParts = ruleString.split('|');
    const ruleMap = ruleParts.reduce((acc, r) => {
      const [rule, paramString = ''] = r.split(':');
      acc[rule] = paramString.split(',').map((s) => s.trim()).filter(Boolean);
      return acc;
    }, {});

    const jRules = [];

    Object.entries(ruleMap).forEach(([ruleName, params]) => {
      match(ruleName, {
        required: () => jRules.push({ rule: 'required' }),

        required_if: () => {
          const [field, ...values] = params;
          if (values.includes(getElementValue(field))) {
            jRules.push({ rule: 'required' });
          }
        },

        required_unless: () => {
          const [field, ...values] = params;
          if (!values.includes(getElementValue(field))) {
            jRules.push({ rule: 'required' });
          }
        },

        required_with: () => {
          if (isAny(params, Boolean)) {
            jRules.push({ rule: 'required' });
          }
        },

        required_without: () => {
          if (isAny(params, (val) => !val)) {
            jRules.push({ rule: 'required' });
          }
        },

        required_with_all: () => {
          if (isAll(params, Boolean)) {
            jRules.push({ rule: 'required' });
          }
        },

        required_without_all: () => {
          if (isAll(params, (val) => !val)) {
            jRules.push({ rule: 'required' });
          }
        },

        email: () => jRules.push({ rule: 'email' }),

        uppercase: () => {
          jRules.push({ validator: (value) => value === (value || '').toUpperCase() });
        },

        lowercase: () => {
          jRules.push({ validator: (value) => value === (value || '').toLowerCase() });
        },

        json: () => {
          jRules.push({
            validator: (value) => {
              try {
                return typeof JSON.parse(value) === 'object';
              } catch {
                return false;
              }
            },
          });
        },

        alpha: () => jRules.push({ rule: 'customRegexp', value: /^[A-Za-z]+$/ }),

        numeric: () => jRules.push({ rule: 'number' }),

        alpha_num: () => jRules.push({ rule: 'customRegexp', value: /^[A-Za-z0-9]+$/ }),

        alpha_dash: () => jRules.push({ rule: 'customRegexp', value: /^[A-Za-z0-9_-]+$/ }),

        alpha_spaces: () => {
          jRules.push({
            validator: (value) => /^[A-Za-z]+(?: [A-Za-z]+)*$/.test((value || '').trim()),
            errorMessage: messages?.[`${name}.alpha_spaces`] ?? 'Only letters and spaces are allowed',
          });
        },

        in: () => {
          jRules.push({ validator: (value) => params.includes(value) });
        },

        not_in: () => {
          jRules.push({ validator: (value) => !params.includes(value) });
        },

       min: () => {
          const min = Number(params[0]);
          jRules.push({
            validator: (value) => {
              if (Array.isArray(value)) {
                return value.length >= min;
              }
              if (input.type === 'number') {
                return parseFloat(value) >= min;
              }
              return (value || '').length >= min;
            },
            errorMessage: messages?.[`${name}.min`] ?? `Minimum allowed is ${min}`,
          });
        },

        max: () => {
          const max = Number(params[0]);
          jRules.push({
            validator: (value) => {
              if (Array.isArray(value)) {
                return value.length <= max;
              }
              if (input.type === 'number') {
                return parseFloat(value) <= max;
              }
              return (value || '').length <= max;
            },
            errorMessage: messages?.[`${name}.max`] ?? `Maximum allowed is ${max}`,
          });
        },

        between: () => {
          const [min, max] = params.map(Number);
          jRules.push({
            validator: (value) => {
              if (Array.isArray(value)) {
                return value.length >= min && value.length <= max;
              }

              if (input.type === 'number') {
                const numeric = parseFloat(value);
                console.log({ numeric, min, max });
                return numeric >= min && numeric <= max;
              }

              return (value || '').length >= min && (value || '').length <= max;
            },
            errorMessage: messages?.[`${name}.between`] ?? `Must be between ${min} and ${max}`,
          });
        },

        digits: () => {
          const digitCount = Number(params[0]);
          jRules.push({
            validator: (value) => /^\d+$/.test(value) && value.length === digitCount,
          });
        },

        digits_between: () => {
          const [min, max] = params.map(Number);
          jRules.push({
            validator: (value) =>
              /^\d+$/.test(value) && value.length >= min && value.length <= max,
          });
        },

        url: () => {
          jRules.push({ rule: 'customRegexp', value: /^(https?|ftp):\/\/[^"]+$/ });
        },

        integer: () => jRules.push({ rule: 'integer' }),

        boolean: () => {
          jRules.push({
            validator: (value) => {
              const val = typeof value === 'string' ? value.toLowerCase() : value;
              return ['true', 'false', '1', '0', true, false].includes(val);
            },
          });
        },

        array: () => {
          jRules.push({ validator: (value) => Array.isArray(value) });
        },

        same: () => {
          jRules.push({ validator: (value) => value === getElementValue(params[0]) });
        },

        regex: () => {
          jRules.push({ rule: 'customRegexp', value: new RegExp(params[0]) });
        },

        date: () => {
          jRules.push({ validator: (value) => !Number.isNaN(Date.parse(value)) });
        },

        accepted: () => {
          jRules.push({
            validator: (value) => ['yes', 'on', '1', 'true', true].includes(value),
          });
        },

        present: () => {
          jRules.push({ validator: () => name in form.elements });
        },

        different: () => {
          jRules.push({ validator: (value) => value !== getElementValue(params[0]) });
        },

        after: () => {
          jRules.push({
            validator: (value) => new Date(value) > new Date(getElementValue(params[0])),
          });
        },

        before: () => {
          jRules.push({
            validator: (value) => new Date(value) < new Date(getElementValue(params[0])),
          });
        },

        nullable: () => {
          // Handled upstream (i.e. skip validation when value is null/empty)
        },
      }, () => {});
    });

    if (jRules.length) {
      jv.addField(input, jRules);
    }
  });

  return Object.assign(jv, {
    rules,
    messages,
    aliases,
    hasRuleExistForInput(input) {
      return !!this.rules[getDotNameFromElement(input)]
    },
  });
}
