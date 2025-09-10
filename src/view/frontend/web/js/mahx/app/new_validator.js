import JustValidate from 'just-validate';
import match from 'match-operator';

const getElementByDotName = (form, path) => {
  const parts = path.split('.');
  return form.elements.namedItem(
    parts.reduce(
      (acc, part, index) => (index === 0 ? part : `${acc}[${part}]`),
      ''
    )
  );
};

/**
 *
 * Converts a form field's bracketed name (PHP/Magento style) into a dot-path.
 * Examples
 *  * - "customer[address][postcode]"  -> "customer.address.postcode"
 *  * - "shipping[street][0]"          -> "shipping.street.0"
 */
const getDotNameFromElement = (input) => input.name.replace(/\[(\w+)\]/g, '.$1');

export default function Validator({
  form,
  rules = {},
  customRules = {},
  messages = {},
  aliases = {},
  globalConfig = {},
}) {
  const defaultConfig = { errorFieldCssClass: ['input-error', 'select-error'] };
  const jv = new JustValidate(form, { ...defaultConfig, ...globalConfig });
  const getElementValue = (name) => form.elements[name].value;
  const isAll = (fields, predicate) =>
    fields.every((field) => predicate(getElementValue(field)));
  const isAny = (fields, predicate) =>
    fields.some((field) => predicate(getElementValue(field)));

    /**
     * When an input contains hx-validate="true", then we want to trigger htmx request only after validating the input.
     * Here we are applying that logic to the input.
     */
  function applyHtmxValidation(field) {
      if (!field.hasAttribute('hx-validate')) {
          return;
      }

      field.addEventListener('htmx:confirm', function(event) {
          if (event.detail.elt !== field) {
              return;
          }

          // Stop sending htmx request immediately.
          event.preventDefault();

          jv.revalidateField(field).then((isValid) => {
              if (isValid) {
                  // Proceed with htmx request.
                  event.detail.issueRequest();
              }
          });
      });
  }

  function applyRules(mahxRules, mahxCustomRules) {
      Object.entries(mahxRules).forEach(([name, ruleString]) => {
          const input = getElementByDotName(form, name);

          if (!input) {
              return;
          }

          const dotName = getDotNameFromElement(input) || 'field';
          const inputLabel = aliases[dotName] || dotName;
          const ruleParts = ruleString.split('|');
          let ruleMap = ruleParts.reduce((acc, rule) => {
              const [ruleName, paramString = ''] = rule.split(':');
              acc[ruleName] = paramString
                  .split(',')
                  .map((s) => s.trim())
                  .filter(Boolean);
              return acc;
          }, {});

          const jRules = [];
          const addRule = (rule) => jRules.push(rule);

          Object.entries(ruleMap).forEach(([ruleName, params]) => {
              const customMessage = messages[`${dotName}:${ruleName}`];
              const errorMessage = (defaultMsg) =>
                  customMessage || `The ${inputLabel} ${defaultMsg}`;
              const requiredRule = {
                  rule: 'required',
                  errorMessage: errorMessage('is required'),
              };

              match(
                  ruleName,
                  {
                      required: () => {
                          addRule(requiredRule);
                      },
                      required_if: () => {
                          const [field, ...values] = params;
                          if (values.includes(getElementValue(field))) {
                              addRule(requiredRule);
                          }
                      },
                      required_unless: () => {
                          const [field, ...values] = params;
                          if (!values.includes(getElementValue(field))) {
                              addRule(requiredRule);
                          }
                      },
                      required_with: () => {
                          if (isAny(params, Boolean)) {
                              addRule(requiredRule);
                          }
                      },
                      required_without: () => {
                          if (isAny(params, (val) => !val)) {
                              addRule(requiredRule);
                          }
                      },
                      required_with_all: () => {
                          if (isAll(params, Boolean)) {
                              addRule(requiredRule);
                          }
                      },
                      required_without_all: () => {
                          if (isAll(params, (val) => !val)) {
                              addRule(requiredRule);
                          }
                      },
                      email: () => {
                          addRule({
                              rule: 'email',
                              errorMessage: errorMessage('is not valid email'),
                          });
                      },
                      uppercase: () => {
                          addRule({
                              validator: (value) => value === (value || '').toUpperCase(),
                              errorMessage: errorMessage('must be uppercase'),
                          });
                      },
                      lowercase: () => {
                          addRule({
                              validator: (value) => value === (value || '').toLowerCase(),
                              errorMessage: errorMessage('must be lowercase'),
                          });
                      },
                      json: () => {
                          addRule({
                              validator: (value) => {
                                  try {
                                      return typeof JSON.parse(value) === 'object';
                                  } catch (e) {
                                      return false;
                                  }
                              },
                              errorMessage: errorMessage('must be a valid JSON string'),
                          });
                      },
                      alpha: () => {
                          addRule({
                              rule: 'customRegexp',
                              value: /^[A-Za-z]+$/,
                              errorMessage: errorMessage('only allows alphabet characters'),
                          });
                      },
                      numeric: () => {
                          addRule({
                              rule: 'number',
                              errorMessage: errorMessage('must be numeric'),
                          });
                      },
                      alpha_num: () => {
                          addRule({
                              rule: 'customRegexp',
                              value: /^[A-Za-z0-9]+$/,
                              errorMessage: errorMessage(
                                  'must only contain letters and numbers'
                              ),
                          });
                      },
                      alpha_dash: () => {
                          addRule({
                              rule: 'customRegexp',
                              value: /^[A-Za-z0-9_-]+$/,
                              errorMessage: errorMessage(
                                  'must only contain letters, numbers, dashes and underscores'
                              ),
                          });
                      },
                      alpha_spaces: () => {
                          addRule({
                              validator: (value) =>
                                  /^[A-Za-z]+(?: [A-Za-z]+)*$/.test((value || '').trim()),
                              errorMessage: errorMessage(
                                  'must only contain letters and spaces'
                              ),
                          });
                      },
                      in: () => {
                          addRule({
                              validator: (value) => params.includes(value),
                              errorMessage: errorMessage(`only allows ${params.join(',')}`),
                          });
                      },
                      not_in: () => {
                          addRule({
                              validator: (value) => !params.includes(value),
                              errorMessage: errorMessage(`is not allowing ${params.join(',')}`),
                          });
                      },
                      min: () => {
                          const min = Number(params[0]);
                          addRule({
                              validator: (value) => {
                                  if (Array.isArray(value)) {
                                      return value.length >= min;
                                  }
                                  if (input.type === 'number') {
                                      return parseFloat(value) >= min;
                                  }
                                  return (value || '').length >= min;
                              },
                              errorMessage: errorMessage(`minimum is ${min}`),
                          });
                      },
                      max: () => {
                          const max = Number(params[0]);
                          addRule({
                              validator: (value) => {
                                  if (Array.isArray(value)) {
                                      return value.length <= max;
                                  }
                                  if (input.type === 'number') {
                                      return parseFloat(value) <= max;
                                  }
                                  return (value || '').length <= max;
                              },
                              errorMessage: errorMessage(`maximum is ${max}`),
                          });
                      },
                      between: () => {
                          const [min, max] = params.map(Number);
                          addRule({
                              validator: (value) => {
                                  if (Array.isArray(value)) {
                                      return value.length >= min && value.length <= max;
                                  }
                                  if (input.type === 'number') {
                                      const numeric = parseFloat(value);
                                      return numeric >= min && numeric <= max;
                                  }
                                  const { length } = value || '';
                                  return length >= min && length <= max;
                              },
                              errorMessage: errorMessage(`must be between ${min} and ${max}`),
                          });
                      },
                      digits: () => {
                          const digitCount = Number(params[0]);
                          addRule({
                              validator: (value) =>
                                  /^\d+$/.test(value) && value.length === digitCount,
                              errorMessage: errorMessage(
                                  `must be numeric and have an exact length of ${digitCount}`
                              ),
                          });
                      },
                      digits_between: () => {
                          const [min, max] = params.map(Number);
                          addRule({
                              validator: (value) =>
                                  /^\d+$/.test(value) &&
                                  value.length >= min &&
                                  value.length <= max,
                              errorMessage: errorMessage(
                                  `must have a length between ${min} and ${max}`
                              ),
                          });
                      },
                      url: () => {
                          addRule({
                              rule: 'customRegexp',
                              value: /^(https?|ftp):\/\/[^"]+$/,
                              errorMessage: errorMessage('is not valid url'),
                          });
                      },
                      integer: () => {
                          addRule({
                              rule: 'integer',
                              errorMessage: errorMessage('must be an integer'),
                          });
                      },
                      boolean: () => {
                          addRule({
                              validator: (value) =>
                                  ['true', 'false', '1', '0', true, false].includes(
                                      typeof value === 'string' ? value.toLowerCase() : value
                                  ),
                              errorMessage: errorMessage('must be a boolean'),
                          });
                      },
                      array: () => {
                          addRule({
                              validator: Array.isArray,
                              errorMessage: errorMessage('must be an array'),
                          });
                      },
                      same: () => {
                          addRule({
                              validator: (value) => value === getElementValue(params[0]),
                              errorMessage: errorMessage(`must be same as ${params[0]}`),
                          });
                      },
                      regex: () => {
                          addRule({
                              rule: 'customRegexp',
                              value: new RegExp(params[0]),
                              errorMessage: errorMessage('is not valid format'),
                          });
                      },
                      date: () => {
                          addRule({
                              validator: (value) => !Number.isNaN(Date.parse(value)),
                              errorMessage: errorMessage('is not a valid date'),
                          });
                      },
                      accepted: () => {
                          addRule({
                              validator: (value) =>
                                  ['yes', 'on', '1', 'true', true].includes(value),
                              errorMessage: errorMessage('must be accepted'),
                          });
                      },
                      present: () => {
                          addRule({
                              validator: () => name in form.elements,
                              errorMessage: errorMessage('must be present'),
                          });
                      },
                      different: () => {
                          addRule({
                              validator: (value) => value !== getElementValue(params[0]),
                              errorMessage: errorMessage(`must be different from ${params[0]}`),
                          });
                      },
                      after: () => {
                          addRule({
                              validator: (value) =>
                                  new Date(value) > new Date(getElementValue(params[0])),
                              errorMessage: errorMessage(`must be a date after ${params[0]}`),
                          });
                      },
                      before: () => {
                          addRule({
                              validator: (value) =>
                                  new Date(value) < new Date(getElementValue(params[0])),
                              errorMessage: errorMessage(`must be a date before ${params[0]}`),
                          });
                      },
                      nullable: () => {},
                      // Custom rules specific to checkout
                      region_required: () => {
                          addRule({
                              validator: (value) => {
                                  return !!(input.dataset.isRequired !== 'true' || String(value).trim().length > 0);
                              },
                              errorMessage: errorMessage('is required'),
                          })
                      }
                  },
                  () => {
                      // If a custom rule handler exists, let it define rules
                      const customRuleCb = mahxCustomRules[ruleName];
                      if (customRuleCb) {
                          const jvRule = customRuleCb({ addRule, params, input, dotName, inputLabel, errorMessage });
                          if (jvRule) {
                              addRule(jvRule);
                          }
                      }
                  }
              );
          });

          if (jRules.length > 0) {
              jv.addField(input, jRules);
              applyHtmxValidation(input);
          }
      });
  }

  applyRules(rules, customRules);

  return Object.assign(jv, {
    rules,
    messages,
    aliases,
    applyRules,
    fieldHasRules(input) {
      return Boolean(this.rules[getDotNameFromElement(input)]);
    },
      async validateFieldFor(input) {
          // If no validation exists for this field, consider it as valid.
          if (!this.fieldHasRules(input)) {
              return true;
          }

          try {
              return await this.revalidateField(input);
          } catch (error) {
              return false;
          }
      },
    async revalidateWithoutFocus() {
      const origFocusInvalidField = this.globalConfig.focusInvalidField;
      this.globalConfig.focusInvalidField = false;
      const isValid = await this.revalidate();
      this.globalConfig.focusInvalidField = origFocusInvalidField;
      return isValid;
    },
  });
}
