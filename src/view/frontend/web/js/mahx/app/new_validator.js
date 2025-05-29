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

const getDotNameFromElement = (input) =>
  input.name.replace(/\[(\w+)\]/g, '.$1');

export default function Validator({
  form,
  rules = {},
  messages = {},
  aliases = {},
  globalConfig = {},
}) {
  const defaultConfig = { errorFieldCssClass: ['input-error'] };
  const jv = new JustValidate(form, { ...defaultConfig, ...globalConfig });

  const getElementValue = (name) => form.elements[name].value;
  const isAll = (fields, predicate) =>
    fields.every((field) => predicate(getElementValue(field)));
  const isAny = (fields, predicate) =>
    fields.some((field) => predicate(getElementValue(field)));

  Object.entries(rules).forEach(([name, ruleString]) => {
    const input = getElementByDotName(form, name);
    if (!input) {
      return;
    }

    const dotName = getDotNameFromElement(input) || 'field';
    const inputLabel = aliases[dotName] || dotName;
    const ruleParts = ruleString.split('|');
    const ruleMap = ruleParts.reduce((acc, rule) => {
      const [ruleName, paramString = ''] = rule.split(':');
      acc[ruleName] = paramString
        .split(',')
        .map((s) => s.trim())
        .filter(Boolean);
      return acc;
    }, {});

    const jRules = [];

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
            jRules.push(requiredRule);
          },
          required_if: () => {
            const [field, ...values] = params;
            if (values.includes(getElementValue(field))) {
              jRules.push(requiredRule);
            }
          },
          required_unless: () => {
            const [field, ...values] = params;
            if (!values.includes(getElementValue(field))) {
              jRules.push(requiredRule);
            }
          },
          required_with: () => {
            if (isAny(params, Boolean)) {
              jRules.push(requiredRule);
            }
          },
          required_without: () => {
            if (isAny(params, (val) => !val)) {
              jRules.push(requiredRule);
            }
          },
          required_with_all: () => {
            if (isAll(params, Boolean)) {
              jRules.push(requiredRule);
            }
          },
          required_without_all: () => {
            if (isAll(params, (val) => !val)) {
              jRules.push(requiredRule);
            }
          },
          email: () => {
            jRules.push({
              rule: 'email',
              errorMessage: errorMessage('is not valid email'),
            });
          },
          uppercase: () => {
            jRules.push({
              validator: (value) => value === (value || '').toUpperCase(),
              errorMessage: errorMessage('must be uppercase'),
            });
          },
          lowercase: () => {
            jRules.push({
              validator: (value) => value === (value || '').toLowerCase(),
              errorMessage: errorMessage('must be lowercase'),
            });
          },
          json: () => {
            jRules.push({
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
            jRules.push({
              rule: 'customRegexp',
              value: /^[A-Za-z]+$/,
              errorMessage: errorMessage('only allows alphabet characters'),
            });
          },
          numeric: () => {
            jRules.push({
              rule: 'number',
              errorMessage: errorMessage('must be numeric'),
            });
          },
          alpha_num: () => {
            jRules.push({
              rule: 'customRegexp',
              value: /^[A-Za-z0-9]+$/,
              errorMessage: errorMessage(
                'must only contain letters and numbers'
              ),
            });
          },
          alpha_dash: () => {
            jRules.push({
              rule: 'customRegexp',
              value: /^[A-Za-z0-9_-]+$/,
              errorMessage: errorMessage(
                'must only contain letters, numbers, dashes and underscores'
              ),
            });
          },
          alpha_spaces: () => {
            jRules.push({
              validator: (value) =>
                /^[A-Za-z]+(?: [A-Za-z]+)*$/.test((value || '').trim()),
              errorMessage: errorMessage(
                'must only contain letters and spaces'
              ),
            });
          },
          in: () => {
            jRules.push({
              validator: (value) => params.includes(value),
              errorMessage: errorMessage(`only allows ${params.join(',')}`),
            });
          },
          not_in: () => {
            jRules.push({
              validator: (value) => !params.includes(value),
              errorMessage: errorMessage(`is not allowing ${params.join(',')}`),
            });
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
              errorMessage: errorMessage(`minimum is ${min}`),
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
              errorMessage: errorMessage(`maximum is ${max}`),
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
            jRules.push({
              validator: (value) =>
                /^\d+$/.test(value) && value.length === digitCount,
              errorMessage: errorMessage(
                `must be numeric and have an exact length of ${digitCount}`
              ),
            });
          },
          digits_between: () => {
            const [min, max] = params.map(Number);
            jRules.push({
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
            jRules.push({
              rule: 'customRegexp',
              value: /^(https?|ftp):\/\/[^"]+$/,
              errorMessage: errorMessage('is not valid url'),
            });
          },
          integer: () => {
            jRules.push({
              rule: 'integer',
              errorMessage: errorMessage('must be an integer'),
            });
          },
          boolean: () => {
            jRules.push({
              validator: (value) =>
                ['true', 'false', '1', '0', true, false].includes(
                  typeof value === 'string' ? value.toLowerCase() : value
                ),
              errorMessage: errorMessage('must be a boolean'),
            });
          },
          array: () => {
            jRules.push({
              validator: Array.isArray,
              errorMessage: errorMessage('must be an array'),
            });
          },
          same: () => {
            jRules.push({
              validator: (value) => value === getElementValue(params[0]),
              errorMessage: errorMessage(`must be same as ${params[0]}`),
            });
          },
          regex: () => {
            jRules.push({
              rule: 'customRegexp',
              value: new RegExp(params[0]),
              errorMessage: errorMessage('is not valid format'),
            });
          },
          date: () => {
            jRules.push({
              validator: (value) => !Number.isNaN(Date.parse(value)),
              errorMessage: errorMessage('is not a valid date'),
            });
          },
          accepted: () => {
            jRules.push({
              validator: (value) =>
                ['yes', 'on', '1', 'true', true].includes(value),
              errorMessage: errorMessage('must be accepted'),
            });
          },
          present: () => {
            jRules.push({
              validator: () => name in form.elements,
              errorMessage: errorMessage('must be present'),
            });
          },
          different: () => {
            jRules.push({
              validator: (value) => value !== getElementValue(params[0]),
              errorMessage: errorMessage(`must be different from ${params[0]}`),
            });
          },
          after: () => {
            jRules.push({
              validator: (value) =>
                new Date(value) > new Date(getElementValue(params[0])),
              errorMessage: errorMessage(`must be a date after ${params[0]}`),
            });
          },
          before: () => {
            jRules.push({
              validator: (value) =>
                new Date(value) < new Date(getElementValue(params[0])),
              errorMessage: errorMessage(`must be a date before ${params[0]}`),
            });
          },
          nullable: () => {},
        },
        () => {}
      );
    });

    if (jRules.length > 0) {
      jv.addField(input, jRules);
    }
  });

  return Object.assign(jv, {
    rules,
    messages,
    aliases,
    hasRuleExistForInput(input) {
      return Boolean(this.rules[getDotNameFromElement(input)]);
    },
  });
}
